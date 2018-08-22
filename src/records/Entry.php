<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\EntryType;
use paws\records\EntryValue;
use paws\behaviors\TimestampBehavior;

class Entry extends ActiveRecord
{
    public function bahaviors()
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
            [['name', 'handle', 'entry_type_id'], 'required'],
            [['name', 'handle'], 'string' => 256],
            [['entry_type_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getEntryType()
    {
        return $this->hasOne(EntryType::class, ['id' => 'entry_type']);
    }

    public function getEntryValues()
    {
        return $this->hasMany(EntryValue::class, ['id' => 'entry_value_id'])->viaTable('{{%entry_value_map', ['entry_id' => 'id']);
    }
}