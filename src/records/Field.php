<?php
namespace paws\records;

use yii\db\ActiveRecord;

class Field extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%field}}';
    }
}