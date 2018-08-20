<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180820215938_create_table_field_map extends Migration
{
    public $tableName = 'field_map';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'entry_type_id' => $this->integer(11)->unsigned(),
            'field_id' => $this->integer(11)->unsigned(),
        ]);

        $this->addForeignKey(
            MigrationHelper::fk($this->tableName, 'entry_type_id'), 
            MigrationHelper::prefix($this->tableName), 'entry_type_id',
            MigrationHelper::prefix('entry_type'), 'id',
            'cascade', 'cascade'
        );

        $this->addForeignKey(
            MigrationHelper::fk($this->tableName, 'field_id'), 
            MigrationHelper::prefix($this->tableName), 'field_id',
            MigrationHelper::prefix('field'), 'id',
            'cascade', 'cascade'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey(MigrationHelper::fk($this->tableName, 'entry_type_id'), MigrationHelper::prefix($this->tableName));
        $this->dropForeignKey(MigrationHelper::fk($this->tableName, 'field_id'), MigrationHelper::prefix($this->tableName));
        $this->dropForeignKey(MigrationHelper::fk($this->tableName, 'entry_type_id'), MigrationHelper::prefix($this->tableName));
        $this->dropTable(MigrationHelper::prefix($this->tableName));
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M180820215938_create_table_field_map cannot be reverted.\n";

        return false;
    }
    */
}
