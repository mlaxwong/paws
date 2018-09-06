<?php
namespace paws\records;

use yii\db\ActiveRecord;
use paws\records\CollectionType;
use paws\records\CollectionValue;
use paws\behaviors\TimestampBehavior;

class Collection extends ActiveRecord
{
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public static function tableName(): string
    {
        return '{{%collection}}';
    }

    public function rules()
    {
        return [
            [['collection_type_id'], 'required'],
            [['collection_type_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function getCollectionType()
    {
        return $this->hasOne(CollectionType::class, ['id' => 'collection_type_id']);
    }

    public function getCollectionValues()
    {
        return $this->hasMany(CollectionValue::class, ['collection_id' => 'id']);
    }
}