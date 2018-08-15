<?php
namespace paws\models;

use yii\base\ActiveRecord;

class Entry extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%entry}}';
    }
}