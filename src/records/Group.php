<?php
namespace paws\records;

use yii\db\ActiveRecord;

class Group extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%group}}';
    }
}