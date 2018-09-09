<?php
namespace paws\records;

use yii\db\ActiveRecord;
use Paws;
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
            [['collection_type_id'], function ($attribute) {
                $query = self::find()
                    ->joinWith('collectionType ct')
                    ->andWhere(['ct.mode' => CollectionType::MODE_SINGLE])
                    ->andWhere(['collection_type_id' => $this->{$attribute}]);
                if ($query->exists() && $this->isNewRecord)
                {
                    $modes = CollectionType::getModes();
                    $this->addError($attribute, Paws::t('app', 'Collection Type "{mode}" only can create once.', ['mode' => $modes[CollectionType::MODE_SINGLE]]));
                }
            }],
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