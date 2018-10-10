<?php

use yii\db\Migration;

/**
 * Class m181010_015653_address_num_table
 */
class m181010_015653_address_num_table extends Migration
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

        $this->createTable('{{%address_num}}', [
            'an_id' => $this->primaryKey(),
            'address' => $this->string(42)->notNull()->comment('地址'),
            'type' => $this->smallInteger(3)->notNull()->defaultValue(1)->comment('类型 1:BTC2:ETH3:USDT'),
            'num' => $this->decimal(11, 8)->unsigned()->notNull()->comment('数量'),
            'ice_num' => $this->decimal(11, 8)->unsigned()->notNull()->comment('冻结数量'),
            'status' => $this->smallInteger(3)->notNull()->defaultValue(1)->comment('账户状态 1:正常2:无效'),
            'tags' => $this->string(32)->unique()->notNull()->comment('多字段联合唯一键'),
            'created_at' => $this->integer(11)->comment('创建时间'),
            'updated_at' => $this->integer(11)->comment('修改时间'),
        ], $tableOptions);

        $this->createIndex('address','{{%address_num}}','address');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return $this->dropTable('{{%address_num}}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181010_015653_address_num_table cannot be reverted.\n";

        return false;
    }
    */
}
