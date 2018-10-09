<?php

use yii\db\Migration;

/**
 * Class m181009_063114_create_user_withdraw
 */
class m181009_063114_create_user_withdraw extends Migration
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

        $this->createTable('{{%user_withdraw}}', [
            'id' => $this->primaryKey(),
            'account_type' => $this->tinyInteger()->notNull()->comment('币类型1:BTC,2:ETH,3:USDT'),
            'app_type' => $this->string(20)->notNull()->comment('apptype'),
            'address' => $this->string(64)->notNull()->comment('提币地址'),
            'amount' => $this->decimal(19,8)->comment('提币数量'),
            'feerate_type' => $this->tinyInteger()->comment('用户选择速度：1块2中3慢'),
            'fees' => $this->decimal(19,8)->comment('手续费'),
            'order_num' => $this->string(30)->comment('第三方订单id'),
            'status' => $this->tinyInteger()->comment('状态：1提币中2提币成功'),
            'txid' => $this->string(100)->comment('txid'),
            'nonce' => $this->integer()->comment('ETH NONCE值'),
            'created_at' => $this->integer()->notNull()->comment('创建时间'),
            'updated_at' => $this->integer()->comment('修改时间'),
        ], $tableOptions);

        $this->createIndex('index_status', '{{%user_withdraw}}', ['status']);
        $this->createIndex('address', '{{%user_withdraw}}', ['app_type', 'address']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return $this->dropTable('{{%user_withdraw}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181009_063114_create_user_withdraw cannot be reverted.\n";

        return false;
    }
    */
}
