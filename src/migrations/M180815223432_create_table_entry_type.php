<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180815223432_create_table_entry_type extends Migration
{
    public $tableName = 'entry_type';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(256)->notNull(),
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
        echo "M180815223432_create_table_entry_type cannot be reverted.\n";

        return false;
    }
    */
}
