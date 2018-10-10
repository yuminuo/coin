<?php

use yii\db\Migration;

/**
 * Class m181010_022718_eth_finish_transactions_table
 */
class m181010_022718_eth_finish_transactions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB COMMENT="ETH交易订单本地存储表"';
        }

        $this->createTable('{{%eth_finish_transaction}}', [
            'id' => $this->primaryKey(),
            'blockHash' => $this->string(255)->comment('交易所在区块hash值'),
            'blockNumber' => $this->integer(11)->comment('交易所在区块高度'),
            'from_a' => $this->string(42)->comment('转出地址'),
            'gas' => $this->string(64)->comment('交易所用GAS'),
            'gasPrice' => $this->string(64)->comment('GAS单价'),
            'hash' => $this->string(255)->unique()->notNull()->comment('交易hash值'),
            'nonce' => $this->string(64)->comment('NONCE值'),
            'to_a' => $this->string(42)->comment('转入地址'),
            'transactionIndex' => $this->string(64)->comment('交易订单索引'),
            'value' => $this->string(64)->comment('交易值'),
            'type' => $this->smallInteger(3)->defaultValue(1)->comment('类型 1:充值2:提现3:系统归币'),
            'status' => $this->smallInteger(3)->notNull()->defaultValue(1)->comment('1:成功2:失败'),
            'created_at' => $this->integer(11)->comment('创建时间'),
            'updated_at' => $this->integer(11)->comment('修改时间'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181010_022718_eth_finish_transactions_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181010_022718_eth_finish_transactions_table cannot be reverted.\n";

        return false;
    }
    */
}
