<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180822132240_create_table_entry_value extends Migration
{
    public $tableName = 'entry_value';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'field_id' => $this->integer(11)->unsigned(),
            'value' => $this->text()->defaultValue(null),
        ]);

        $this->addForeignKey(
            MigrationHelper::fk($this->tableName, 'field_id'),
            MigrationHelper::prefix($this->tableName), 'field_id',
            MigrationHelper::prefix('field'), 'id',
            'cascade', 'cascade'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(MigrationHelper::fk($this->tableName, 'field_id'), MigrationHelper::prefix($this->tableName));
        $this->dropTable(MigrationHelper::prefix($this->tableName));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180822132240_create_table_entry_value cannot be reverted.\n";

        return false;
    }
    */
}
