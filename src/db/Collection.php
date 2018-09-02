<?php
namespace paws\db;

use yii\db\BaseActiveRecord;
use yii\helpers\Inflector;
use yii\helpers\StringHelper;
use paws\db\CollectionInterface;

class Collection extends BaseActiveRecord implements CollectionInterface
{
    public $typeId;

    protected $_type;

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

    public function getType()
    {
        if ($this->_type === null)
        {
            $typeClass = static::collectionTypeRecord();
            $this->_type = $typeClass::findOne($this->typeId);
        }
        return $this->_type;
    }

    public static function primaryKey()
    {
        $recordClass = static::collectionRecord();
        return $recordClass::primaryKey();
    }

    public static function find() {}

    public function insert($runValidation = true, $attributes = null) 
    {

    }

    public static function getDb() 
    {
        $recordClass = static::collectionRecord();
        return $recordClass::getDb();
    }
}