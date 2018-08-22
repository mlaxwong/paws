<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180815223423_create_table_field extends Migration
{
    public $tableName = 'field';
    
    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(256)->notNull(),
            'handle' => $this->string(256)->notNull(),
            'config' => $this->text()->defaultValue(null),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable(MigrationHelper::prefix($this->tableName));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180815223423_create_table_field cannot be reverted.\n";

        return false;
    }
    */
}
