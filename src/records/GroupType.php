<?php
namespace paws\records;

use yii\db\ActiveRecord;

class GroupType extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%group_type}}';
    }
}