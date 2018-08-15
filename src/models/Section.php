<?php
namespace paws\models;

use yii\db\ActiveRecord;

class Section extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%section}}';
    }
}