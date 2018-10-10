<?php

use yii\db\Migration;

/**
 * Class m181010_062232_address_store
 */
class m181010_062232_address_store extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB COMMENT="钱包地址预存储表"';
        }

        $this->createTable('{{%address_store}}', [
            'as_id' => $this->primaryKey(),
            'address' => $this->string(64)->unique()->notNull()->comment('钱包地址'),
            'keyt' => $this->string(255)->notNull()->comment('密钥'),
            'used' => $this->smallInteger(3)->notNull()->defaultValue(1)->comment('是否使用1:否2:是'),
            'created_at' => $this->integer(11)->comment('创建时间'),
        ], $tableOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return $this->dropTable('{{%address_store}}}');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181010_062232_address_store cannot be reverted.\n";

        return false;
    }
    */
}
