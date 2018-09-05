<?php
namespace paws\db;

use yii\db\BaseActiveRecord;
use yii\db\StaleObjectException;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use paws\db\CollectionInterface;
use paws\db\CollectionQuery;

class Collection extends BaseActiveRecord implements CollectionInterface
{
    const OP_INSERT = 0x01;
    const OP_UPDATE = 0x02;
    const OP_DELETE = 0x04;
    const OP_ALL    = 0x07;

    public $typeId;

    private $_oldAttributes;

    public static function instantiate($row)
    {
        return new static(['typeId' => $row[static::typeAttribute()]]);
    }

    public static function collectionRecord() 
    {
        return 'paws\\records\\' . Inflector::camelize(StringHelper::basename(get_called_class()));
    }

    public static function collectionTypeRecord()
    {
        return static::collectionRecord() . 'Type';
    }

    public static function collectionValueRecord()
    {
        return static::collectionRecord() . 'Value';
    }

    public static function collectionFieldRecord()
    {
        return static::collectionRecord() . 'Field';
    }

    public static function fkCollectionId()
    {
        $collectionClass = static::collectionRecord();
        return str_replace(['{{%', '}}'], '', $collectionClass::tableName()) . '_id';
    }

    public static function fkFieldId()
    {
        $fieldClass = static::collectionFieldRecord();
        return str_replace(['{{%', '}}'], '', $fieldClass::tableName()) . '_id';
    }

    public static function typeAttribute()
    {
        return Inflector::camel2id(StringHelper::basename(static::class), '_') . '_type_id';
    }

    public function attributes()
    {
        return ArrayHelper::merge($this->getBaseAttributes(), $this->getCustomAttributes());
    }

    public function getBaseAttributes()
    {
        $recordClass = $this->collectionRecord();
        return array_keys($recordClass::getTableSchema()->columns);
    }

    public function getCustomAttributes()
    {
        $type = $this->getType();
        return  $type ? ArrayHelper::getColumn($type->fields, 'handle') : [];
    }

    public function baseRules()
    {
        $recordClass = $this->collectionRecord();
        return $recordClass::rules();
    }

    public function customRules()
    {
        $rules = [];
        $type = $this->getType();
        if ($type) {
            foreach ($type->fields as $field)
            {
                $config = json_decode($field->config, true);
                if (json_last_error() == JSON_ERROR_NONE)
                {
                    foreach ($config as $rule)
                    {
                        array_unshift($rule, $field->handle);
                        $rules[] = $rule;
                    }
                }
            }
        }
        return $rules;
    }

    public function rules()
    {
        return ArrayHelper::merge($this->baseRules(), $this->customRules());
    }

    public function getType()
    {
        $typeClass = static::collectionTypeRecord();
        return $typeClass::findOne($this->typeId);
    }

    public static function primaryKey()
    {
        $recordClass = static::collectionRecord();
        return $recordClass::primaryKey();
    }

    public static function find() 
    {
        return new CollectionQuery(static::class);
    }

    public function insert($runValidation = true, $attributes = null) 
    {
        if (!$this->beforeSave(true)) return false;

        $baseAttribute = $this->getBaseAttributes();
        $dirtyAttribute = $this->getDirtyAttributes($attributes);
        
        $baseValues = [];
        $values = $dirtyAttribute;
        foreach ($values as $key => $value)
        {
            if (in_array($key, $baseAttribute))
            {
                $baseValues[$key] = $value;
                unset($values[$key]);
            }
        }
        $collectionClass = static::collectionRecord();
        $collectionRecord = new $collectionClass($baseValues);
        $collectionRecord->{static::typeAttribute()} = $this->typeId;
        if (!$collectionRecord->save(false)) return false;
        $primaryKeys = $collectionRecord::primaryKey();
        foreach ($primaryKeys as $primaryKey)
        {
            $id = $collectionRecord->{$primaryKey};
            $this->setAttribute($primaryKey, $id);
            $dirtyAttribute[$primaryKey] = $id;
        }
        
        foreach ($values as $key => $value)
        {
            $fieldClass = static::collectionFieldRecord();
            $fieldRecord = $fieldClass::find()->andWhere(['handle' => $key])->one();
            if (!$valueId = $this->insertValueRecord($collectionRecord, $fieldRecord, $value, $runValidation)) return false;
        }
        $changedAttributes = array_fill_keys(array_keys($dirtyAttribute), null);
        $this->setOldAttributes($dirtyAttribute);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    public function insertValueRecord($collection, $field, $value, $runValidation = true)
    {
        $valueClass = static::collectionValueRecord();
        $valueRecord = new $valueClass([
            static::fkCollectionId()    => $collection->id,
            static::fkFieldId()         => $field->id,
            'value'                     => $value,
        ]);
        return $valueRecord->save() ? $valueRecord->id : false;
    }

    public function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $values[$lock] = $this->$lock + 1;
            $condition[$lock] = $this->$lock;
        }

        $collectionClass = static::collectionRecord();
        $id = $condition[$collectionClass::primaryKey()[0]];

        $baseAttributes = $this->getBaseAttributes();
        $baseValues = [];
        $fieldValues = $values;
        foreach ($values as $key => $value)
        {
            if (in_array($key, $baseAttributes))
            {
                $baseValues[$key] = $value;
                unset($fieldValues[$key]);
            }
        }

        if (empty($baseValues)) $baseValues = [static::typeAttribute() => $this->typeId];

        $rows = $collectionClass::updateAll($baseValues, $condition);

        if ($lock !== null && !$rows) throw new StaleObjectException('The object being updated is outdated.');
        
        $fieldClass = static::collectionFieldRecord();
        $valueClass = static::collectionValueRecord();
        $updatedFieldrows = 0;
        foreach ($fieldValues as $key => $value)
        {
            $fieldRecord = $fieldClass::find()->andWhere(['handle' => $key])->one();
            $valueRecord = $valueClass::find()->andWhere([static::fkFieldId() => $fieldRecord->id, static::fkCollectionId() => $id])->one();
            $fieldConditions[$valueRecord::primaryKey()[0]] = $valueRecord->id;
            $fieldConditions[static::fkCollectionId()] = $id;
            $fieldRows = $valueClass::updateAll(['value' => $value], $fieldConditions);
            if ($fieldRows > 0) $updatedFieldrows = 1;
        }

        if (isset($values[$lock])) $this->$lock = $values[$lock];

        $changedAttributes = [];
        foreach ($values as $name => $value) 
        {
            $changedAttributes[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
            $this->_oldAttributes[$name] = $value;
        }
        $this->afterSave(false, $changedAttributes);
        return $rows >= $updatedFieldrows ? $rows : $updatedFieldrows;
    }

    public static function getDb() 
    {
        $recordClass = static::collectionRecord();
        return $recordClass::getDb();
    }
}