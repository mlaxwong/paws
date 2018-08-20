<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\Entry;
use paws\records\Field;

class EntryType extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%entry_type}}';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 256],
        ];
    }

    public function getEntries()
    {
        return $this->hasMany(Entry::class, ['entry_type_id' => 'id']);
    }

    public function getFields()
    {
        return $this->hasMany(Field::class, ['id' => 'entry_type_id'])->viaTable('{{%entry_type}}', ['field_id' => 'id']);
    }
    
}