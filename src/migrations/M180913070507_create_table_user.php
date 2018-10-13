<?php
namespace paws\migrations;

use paws\db\Migration;
use paws\helpers\MigrationHelper;

class M180913070507_create_table_user extends Migration
{
    public $tableName = 'user';

    public function safeUp()
    {
        $this->createTable(MigrationHelper::prefix($this->tableName), [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->string()->notNull()->unique(),
            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'email' => $this->string()->notNull()->unique(),
            'created_at' => $this->timestamp()->defaultValue(NULL),
            'updated_at' => $this->timestamp()->defaultValue(NULL),
            'logged_at' => $this->timestamp()->defaultValue(NULL),
        ]);

        $this->batchInsert(
            MigrationHelper::prefix($this->tableName), 
            ['id', 'username', 'auth_key', 'password_hash', 'email', 'created_at', 'updated_at'],
            [1, 'developer', 'NrhetzCJL9wRQemdpHT4GL3zyvZmAuTc', '$2y$13$TDBeAM/CC8Xpf7WHvgG4bODk1y9Z0YONhI9lzI6wyA90NSy8BBnju', 'mlaxwong@gmail.com', new \yii\db\Expression('NOW()'), new \yii\db\Expression('NOW()')]
        );
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
        echo "M180913070507_create_table_user cannot be reverted.\n";

        return false;
    }
    */
}
