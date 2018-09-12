<?php
namespace paws\records;

use yii\db\ActiveRecord;

class User extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%user}}';
    }
}