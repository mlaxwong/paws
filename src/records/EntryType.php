<?php
namespace paws\records;

use yii\db\ActiveRecord;
use voskobovich\linker\LinkerBehavior;
use Paws;
use paws\records\Entry;
use paws\records\Field;

class EntryType extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => LinkerBehavior::class,
                'relations' => ['field_ids' => 'fields'],
            ],
        ];
    }

    public static function tableName(): string
    {
        return '{{%entry_type}}';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 256],
            [['field_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    public function getEntries()
    {
        return $this->hasMany(Entry::class, ['entry_type_id' => 'id']);
    }

    public function getFields()
    {
        return $this->hasMany(Field::class, ['id' => 'field_id'])->viaTable('{{%entry_type_field_map}}', ['entry_type_id' => 'id']);
    }

    public function attributeLabels()
    {
        return [
            'entry_ids' => Paws::t('app', 'Entries'),
            'field_ids' => Paws::t('app', 'Fields'),
        ];
    }
    
}