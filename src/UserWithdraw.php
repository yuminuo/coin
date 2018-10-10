<?php

namespace adamyxt\coin;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%user_withdraw}}".
 *
 * @property int $id
 * @property int $account_type 币类型1:BTC,2:ETH,3:USDT
 * @property string $app_type apptype
 * @property string $address 提币地址
 * @property string $amount 提币数量
 * @property int $feerate_type 用户选择速度：1块2中3慢
 * @property string $fees 手续费
 * @property string $order_num 第三方订单id
 * @property int $status 状态：1提币中2提币成功
 * @property string $txid txid
 * @property int $nonce ETH NONCE值
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 */
class UserWithdraw extends \yii\db\ActiveRecord
{
    const TYPE_BTC = 1;
    const TYPE_ETH = 2;
    const TYPE_USDT = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_withdraw}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['account_type', 'app_type', 'address', 'created_at'], 'required'],
            [['amount', 'fees'], 'number'],
            [['nonce', 'created_at', 'updated_at'], 'integer'],
            [['account_type', 'feerate_type', 'status'], 'string', 'max' => 3],
            [['app_type'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 64],
            [['order_num'], 'string', 'max' => 30],
            [['txid'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account_type' => 'Account Type',
            'app_type' => 'App Type',
            'address' => 'Address',
            'amount' => 'Amount',
            'feerate_type' => 'Feerate Type',
            'fees' => 'Fees',
            'order_num' => 'Order Num',
            'status' => 'Status',
            'txid' => 'Txid',
            'nonce' => 'Nonce',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ]
        ];
    }
}
