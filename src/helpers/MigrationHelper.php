<?php
namespace paws\helpers;

use yii\base\Component;

class MigrationHelper extends Component
{
    /**
     * Wrap with {{% }} wrapper to string
     * @param string $tableName table name
     * @return string
     */
    public static function prefix($tableName): string
    {
        return '{{%' . $tableName . '}}';
    }

    /**
     * Generate foreign key name by table name and column name
     * @param string $tableName table name
     * @param string $column column name
     * @return string
     */
    public static function fk($tableName, $column): string
    {
        return 'fk_' . $tableName . '_' . $column;
    }
}