<?php 
namespace paws\models;

use Exception;
use Throwable;
use yii\db\BaseActiveRecord;
use yii\db\ActiveRecordInterface;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use Paws;
use paws\records\Entry as EntryRecord;
use paws\records\EntryType;
use paws\records\EntryValue;
use paws\records\Field;
use paws\models\query\EntryQuery;

class Entry extends BaseActiveRecord implements ActiveRecordInterface
{
    const OP_INSERT = 0x01;

    const OP_UPDATE = 0x02;

    const OP_DELETE = 0x04;
    
    const OP_ALL = 0x07;

    public $entry_type_id;

    public $id;

    // public function __construct($type, $config = [])
    // {
    //     if ($type instanceof EntryType) {
    //         $this->getEntryType() = $type;
    //     } else if (is_integer($type)) {
    //         $this->getEntryType() = EntryType::findOne($type);
    //     }
    //     if (!$this->getEntryType()) new InvalidConfigException(Paws::t('app', 'Invalid entry type'));
    //     parent::__construct($config);
    // }

    public static function tableName()
    {
        return EntryRecord::tableName();
    } 

    public function rules()
    {
        $rules = [
            [['id', 'entry_type_id'], 'integer'],
            ['entry_type_id', 'required'],
        ];
        foreach ($this->getEntryType()->fields as $field)
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

    public function getEntryType()
    {
        return EntryType::findOne($this->entry_type_id);
    }

    /**
     * 
     */

    public function attributes()
    {
        return ArrayHelper::merge(['id', 'entry_type_id'], ArrayHelper::getColumn($this->getEntryType()->fields, 'handle'));
    }

    public static function primaryKey()
    {
        return EntryRecord::getTableSchema()->primaryKey;
    }

    public static function find()
    {
        return new EntryQuery(static::class);
    }

    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            Yii::info('Model not inserted due to validation error.', __METHOD__);
            return false;
        }
        
        if (!$this->isTransactional(self::OP_INSERT)) {
            return $this->insertInternal($attributes);
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $result = $this->insertInternal($attributes);
            if ($result === false) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }

            return $result;
        } catch (Exception $e) {
            $transaction->rollBack();
            throw $e;
        } catch (Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function insertInternal($attributes = null)
    {
        if (!$this->beforeSave(true)) return false;

        $baseAttribute = ['entry_type_id'];
        
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

        if (($primaryKeys = static::getDb()->schema->insert(static::tableName(), $baseValues)) === false) {
            return false;
        }
        foreach ($primaryKeys as $name => $value) 
        {
            $id = EntryRecord::getTableSchema()->columns[$name]->phpTypecast($value);
            $this->setAttribute($name, $id);
            $dirtyAttribute[$name] = $id;
        }

        foreach ($values as $key => $value)
        {
            $field = Field::find()->andWhere(['handle' => $key])->one();
            $entryValue = new EntryValue([
                'entry_id' => $id,
                'field_id' => $field->id,
                'value' => $value,
            ]);
            if (!$entryValue->save()) return false;
        }
        
        $changedAttributes = array_fill_keys(array_keys($dirtyAttribute), null);
        $this->setOldAttributes($dirtyAttribute);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    public function transactions()
    {
        return [];
    }

    public function isTransactional($operation)
    {
        $scenario = $this->getScenario();
        $transactions = $this->transactions();

        return isset($transactions[$scenario]) && ($transactions[$scenario] & $operation);
    }

    public static function getDb()
    {
        return Paws::$app->db;
    }
}