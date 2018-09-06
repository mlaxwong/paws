<?php
namespace paws\records;

use yii\db\ActiveRecord;
use voskobovich\linker\LinkerBehavior;
use Paws;
use paws\records\Collection;
use paws\records\CollectionField;

class CollectionType extends ActiveRecord
{
    public function behaviors()
    {
        return [
            [
                'class' => LinkerBehavior::class,
                'relations' => ['collection_field_ids' => 'fields'],
            ],
        ];
    }

    public static function tableName(): string
    {
        return '{{%collection_type}}';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 256],
            [['collection_field_ids'], 'each', 'rule' => ['integer']],
        ];
    }

    public function getCollections()
    {
        return $this->hasMany(Collection::class, ['collection_type_id' => 'id']);
    }

    public function getFields()
    {
        return $this->hasMany(CollectionField::class, ['id' => 'collection_field_id'])->viaTable('{{%collection_type_field_map}}', ['collection_type_id' => 'id']);
    }

    public function attributeLabels()
    {
        return [
            'collection_ids' => Paws::t('app', 'Collections'),
            'collection_field_ids' => Paws::t('app', 'Fields'),
        ];
    }
    
}