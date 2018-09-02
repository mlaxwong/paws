<?php 
namespace paws\models;

use yii\base\Model;
use yii\db\ActiveRecordInterface;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use Paws;
use paws\records\Entry as EntryRecord;
use paws\records\EntryType;
use paws\models\query\EntryQuery;

class Entry extends Model implements ActiveRecordInterface
{
    public $type;

    public $id;

    protected $_attributes = [];

    public function __construct($type, $config = [])
    {
        if ($type instanceof EntryType) {
            $this->type = $type;
        } else if (is_integer($type)) {
            $this->type = EntryType::findOne($type);
        }
        if (!$this->type) new InvalidConfigException(Pwas::t('app', 'Invalid entry type'));
        parent::__construct($config);
    }

    public static function tableName()
    {
        return EntryRecord::tableName();
    } 

    public function rules()
    {
        $rules = [
            ['id', 'integer'],
        ];
        foreach ($this->type->fields as $field)
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
        return $rules;
    }

    /**
     * 
     */

    public function attributes()
    {
        return ArrayHelper::getColumn($this->type->fields, 'handle');
    }

    public static function getTableSchema()
    {
        $tableSchema = static::getDb()
            ->getSchema()
            ->getTableSchema(static::tableName());

        if ($tableSchema === null) {
            throw new InvalidConfigException('The table does not exist: ' . static::tableName());
        }

        return $tableSchema;
    }

    public static function primaryKey()
    {
        return static::getTableSchema()->primaryKey;
    }

    public function getAttribute($name)
    {

    }

    public function setAttribute($name, $value)
    {

    }

    public function hasAttribute($name)
    {

    }

    public function getPrimaryKey($asArray = false)
    {

    }

    public function getOldPrimaryKey($asArray = false)
    {

    }

    public static function isPrimaryKey($keys)
    {

    }

    public static function find($typeId)
    {
        return new EntryQuery($typeId, static::class);
    }

    public static function findOne($condition)
    {

    }

    public static function findAll($condition)
    {

    }

    public static function updateAll($attributes, $condition = null)
    {

    }

    public static function deleteAll($condition = null)
    {

    }

    public function save($runValidation = true, $attributeNames = null)
    {

    }

    public function insert($runValidation = true, $attributes = null)
    {

    }

    public function update($runValidation = true, $attributeNames = null)
    {

    }

    public function delete()
    {

    }

    public function getIsNewRecord()
    {

    }

    public function equals($record)
    {

    }

    public function getRelation($name, $throwException = true)
    {

    }

    public function populateRelation($name, $records)
    {

    }

    public function link($name, $model, $extraColumns = [])
    {

    }

    public function unlink($name, $model, $delete = false)
    {

    }

    public static function getDb()
    {
        return Paws::$app->db;
    }
}