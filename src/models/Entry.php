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
    
    private $_oldAttributes;

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

    public static function instantiate($row)
    {
        return new static(['entry_type_id' => $row['entry_type_id']]);
    }

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

        $entryRecord = new EntryRecord($baseValues);
        $entryRecord->entry_type_id = $this->entry_type_id;
        if (!$entryRecord->save(false)) return false;
        $primaryKeys = $entryRecord::primaryKey();
        foreach ($primaryKeys as $primaryKey)
        {
            $id = $entryRecord->{$primaryKey};
            $this->setAttribute($primaryKey, $id);
            $dirtyAttribute[$primaryKey] = $id;
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

    protected function updateInternal($attributes = null)
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

        $id = $condition[EntryRecord::primaryKey()[0]];

        $baseAttribute = ['entry_type_id'];
        $baseValues = [];
        $fieldValues = $values;
        foreach ($values as $key => $value)
        {
            if (in_array($key, $baseAttribute))
            {
                $baseValues[$key] = $value;
                unset($fieldValues[$key]);
            }
        }

        if (!isset($baseAttribute['entry_type_id'])) $baseValues['entry_type_id'] = $this->getEntryType()->id;
        
        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        $rows = EntryRecord::updateAll($baseValues, $condition);

        if ($lock !== null && !$rows) {
            throw new StaleObjectException('The object being updated is outdated.');
        }
        
        foreach ($fieldValues as $key => $value)
        {
            $field = Field::find()->andWhere(['handle' => $key])->one();
            $entryValue = EntryValue::find()->andWhere(['field_id' => $field->id, 'entry_id' => $id])->one();
            $fieldConditions['id'] = $entryValue->id;
            $fieldConditions['entry_id'] = $id;
            $fieldRows = EntryValue::updateAll(['value' => $value], $fieldConditions);
        }

        if (isset($values[$lock])) {
            $this->$lock = $values[$lock];
        }

        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = isset($this->_oldAttributes[$name]) ? $this->_oldAttributes[$name] : null;
            $this->_oldAttributes[$name] = $value;
        }
        $this->afterSave(false, $changedAttributes);

        return $rows;
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