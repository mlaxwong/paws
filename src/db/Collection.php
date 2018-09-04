<?php
namespace paws\db;

use yii\db\BaseActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use paws\db\CollectionInterface;

class Collection extends BaseActiveRecord implements CollectionInterface
{
    const OP_INSERT = 0x01;
    const OP_UPDATE = 0x02;
    const OP_DELETE = 0x04;
    const OP_ALL    = 0x07;

    public $typeId;

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

    public static function find() {}

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

    public static function getDb() 
    {
        $recordClass = static::collectionRecord();
        return $recordClass::getDb();
    }
}