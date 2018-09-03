<?php
namespace paws\db;

use yii\db\BaseActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;
use paws\db\CollectionInterface;

class Collection extends BaseActiveRecord implements CollectionInterface
{
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

    public static function typeAttribute()
    {
        return Inflector::camel2id(StringHelper::basename(get_called_class()), '_') . '_type_id';
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

    public function insert($runValidation = true, $attributes = null) {}

    public static function getDb() 
    {
        $recordClass = static::collectionRecord();
        return $recordClass::getDb();
    }
}