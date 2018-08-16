<?php
namespace paws\records;

use yii\base\ActiveRecord;

class Entry extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%entry}}';
    }
}