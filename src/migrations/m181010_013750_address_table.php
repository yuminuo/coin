<?php

use yii\db\Migration;

/**
 * Class m181010_013750_address_table
 */
class m181010_013750_address_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB COMMENT="用户钱包地址关联表"';
        }

        $this->createTable('{{%address}}', [
            'a_id' => $this->primaryKey(),
            'app_type' => $this->string(20)->notNull()->comment('apptype'),
            'address' => $this->string(64)->unique()->notNull()->comment('钱包地址'),
            'type' => $this->smallInteger(3)->notNull()->defaultValue(1)->comment('类型 1:BTC2ETH:3:USDT'),
            'created_at' => $this->integer(11)->comment('创建时间'),
            'updated_at' => $this->integer(11)->comment('修改时间'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return $this->dropTable('{{%address}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181010_013750_address_table cannot be reverted.\n";

        return false;
    }
    */
}
