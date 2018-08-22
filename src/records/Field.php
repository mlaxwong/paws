<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\EntryType;

class Field extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%field}}';
    }

    public function rules()
    {
        return [
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 256],
            [['config'], 'safe'],
        ];
    }

    public function getEntryTypes()
    {
        return $this->hasMany(EntryType::class, ['id' => 'entry_type_id'])->viaTable('{{%entry_type}}', ['field_id' => 'id']);
    }
}