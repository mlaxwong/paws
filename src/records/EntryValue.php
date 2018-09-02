<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\Field;
use paws\records\Entry;

class EntryValue extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%entry_value}}';
    }

    public function rules()
    {
        return [
            [['entry_id', 'field_id', 'value'], 'required'],
            [['id', 'entry_id', 'field_id'], 'integer'],
            [['value'], 'safe'],
        ];
    }

    public function getEntry()
    {
        return $this->hasOne(Entry::class, ['id' => 'entry_id']);
    }

    public function getField()
    {
        return $this->hasOne(Field::class, ['id' => 'field_id']);
    }

    public static function updateAll($attributes, $condition = '', $params = [])
    {
        $command = static::getDb()->createCommand();
        $command->update(static::tableName(), $attributes, $condition, $params);

        return $command->execute();
    }
}