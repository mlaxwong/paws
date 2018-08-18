<?php
namespace paws\records;

use yii\db\ActiveRecord;

class Entry extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%entry}}';
    }
}