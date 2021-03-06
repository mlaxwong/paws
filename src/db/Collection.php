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
        return (new $recordClass)->rules();
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
        if (!$collectionRecord->save()) 
        {
            $this->addErrors($collectionRecord->errors);
            return false;
        }
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
            if (!$this->insertValueRecord($collectionRecord, $fieldRecord, $value)) return false;
        }
        $changedAttributes = array_fill_keys(array_keys($dirtyAttribute), null);
        $this->setOldAttributes($dirtyAttribute);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    public function insertValueRecord($collection, $field, $value = true)
    {
        $valueClass = static::collectionValueRecord();
        $valueRecord = new $valueClass([
            static::fkCollectionId()    => $collection->id,
            static::fkFieldId()         => $field->id,
            'value'                     => $value,
        ]);
        return $valueRecord->save(false) ? $valueRecord->id : false;
    }

    public function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) return false;

        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) 
        {
            $this->afterSave(false, $values);
            return 0;
        }
        
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock) 
        {
            $values[$lock] = $this->$lock + 1;
            $condition[$lock] = $this->$lock;
        }

        $collectionClass = static::collectionRecord();
        $id = $condition[$collectionClass::primaryKey()[0]];
        $collectionRecord = $collectionClass::findOne($id);

        list($baseValues, $fieldValues) = $this->attributeSeparator($values);
        if (empty($baseValues)) $baseValues = [static::typeAttribute() => $this->typeId];

        $rows = $collectionClass::updateAll($baseValues, $condition);

        if ($lock !== null && !$rows) throw new StaleObjectException('The object being updated is outdated.');
        
        $valueUpdated = $this->updateValueRecords($collectionRecord, $fieldValues);

        if (isset($values[$lock])) $this->$lock = $values[$lock];

        $changedAttributes = [];
        foreach ($values as $name => $value) 
        {
            $changedAttributes[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
            $this->_oldAttributes[$name] = $value;
        }
        $this->afterSave(false, $changedAttributes);
        return $rows > 0 || $valueUpdated;
    }

    protected function attributeSeparator($values)
    {
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
        return [$baseValues, $fieldValues];
    }

    public function updateValueRecords($collectionRecord, array $values)
    {
        $updateStatus = true;
        $fieldClass = static::collectionFieldRecord();
        $valueClass = static::collectionValueRecord();
        $fieldConditions = [];

        foreach ($values as $key => $value)
        {
            $fieldRecord = $fieldClass::find()
                ->andWhere(['handle' => $key])
                ->one();

            $valueRecord = $valueClass::find()
                ->andWhere([static::fkFieldId() => $fieldRecord->id])
                ->andWhere([static::fkCollectionId() => $collectionRecord->id])
                ->one();

            if (!$valueRecord) 
            {
                $valueRecord = new $valueClass([
                    static::fkFieldId()         => $fieldRecord->id,
                    static::fkCollectionId()    => $collectionRecord->id,
                ]);
                $valueRecord->save();
            }

            $fieldConditions[$valueRecord::primaryKey()[0]] = $valueRecord->id;
            $fieldConditions[static::fkCollectionId()] = $collectionRecord->id;

            if(!$valueClass::updateAll(['value' => $value], $fieldConditions)) $updateStatus = false;
        }
        return $updateStatus;
    }

    public static function getDb() 
    {
        $recordClass = static::collectionRecord();
        return $recordClass::getDb();
    }
}