<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\EntryType;
use paws\records\EntryValue;
use paws\behaviors\TimestampBehavior;

class Entry extends ActiveRecord
{
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function tableName(): string
    {
        return '{{%entry}}';
    }

    public function rules()
    {
        return [
            [['entry_type_id'], 'required'],
            [['entry_type_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getEntryType()
    {
        return $this->hasOne(EntryType::class, ['id' => 'entry_type_id']);
    }

    public function getEntryValues()
    {
        return $this->hasMany(EntryValue::class, ['entry_id' => 'id']);
    }
}