<?php
namespace paws\records;

use yii\db\ActiveRecord;

class Section extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%section}}';
    }
}