<?php
namespace paws\helpers;

use yii\base\Component;

class MigrationHelper extends Component
{
    public static function prefix($tableName): string
    {
        return '{{%' . $tableName . '}}';
    }

    public static function fk($tableName, $attribute): string
    {
        return 'fk_' . $tableName . '_' . $attribute;
    }
}