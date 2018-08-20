<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\EntryType;

class Entry extends ActiveRecord
{
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
        ];
    }

    public function getEntryType()
    {
        return $this->hasOne(EntryType::class, ['id' => 'entry_type']);
    }
}