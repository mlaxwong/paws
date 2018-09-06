<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\CollectionType;

class CollectionField extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%collection_field}}';
    }

    public function rules()
    {
        return [
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 256],
            [['config'], 'safe'],
        ];  
    }

    public function getCollectionTypes()
    {
        return $this->hasMany(CollectionType::class, ['id' => 'collection_type_id'])->viaTable('{{%collection_type_field_map}}', ['collection_field_id' => 'id']);
    }
}