<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M181207035652_create_table_token extends Migration
{
    public $tableName = 'token';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'model_class' => $this->text()->defaultValue(NULL),
            'model_primary_key' => $this->text()->defaultValue(NULL),
            'type' => $this->string(64)->defaultValue(NULL),
            'secret' => $this->text()->defaultValue(NULL),
            'token_key' => $this->text()->defaultValue(NULL),
            'data' => $this->text()->defaultValue(NULL),
            'expire_at' => $this->timestamp()->defaultValue(NULL),
            'created_at' => $this->timestamp()->defaultValue(NULL),
            'updated_at' => $this->timestamp()->defaultValue(NULL),
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
        echo "M181207035652_create_token cannot be reverted.\n";

        return false;
    }
    */
}
