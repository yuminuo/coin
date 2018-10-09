<?php

use yii\db\Migration;

/**
 * Class m181009_064156_create_eth_block_info
 */
class m181009_064156_create_eth_block_info extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB COMMENT="ETH区块本地存储表"';
        }

        $this->createTable('{{%eth_block_info}}', [
            'ebi_id' => $this->primaryKey(),
            'hash' => $this->string(255)->unique()->notNull()->comment('区块hash值'),
            'parentHash' => $this->string(255)->notNull()->comment('父区块hash值'),
            'number' => $this->integer(11)->unique()->notNull()->comment('区块高度'),
            'timestamp' => $this->integer(11)->notNull()->comment('区块创建时间'),
            'created_at' => $this->integer(11)->comment('创建时间'),
            'updated_at' => $this->integer(11)->comment('修改时间'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return $this->dropTable('{{%eth_block_info}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_064156_create_eth_block_info cannot be reverted.\n";

        return false;
    }
    */
}
