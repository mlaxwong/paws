<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\Field;

class EntryValue extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%entry_value}}';
    }

    public function rules()
    {
        return [
            [['field_id', 'value'], 'required'],
            [['field_id'], 'integer'],
            [['value'], 'safe'],
        ];
    }

    public function getField()
    {
        return $this->hasOne(Field::class, ['id' => 'field_id']);
    }
}