<?php

namespace adamyxt\coin;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%eth_transaction}}".
 *
 * @property int $et_id
 * @property string $blockHash 交易所在区块hash值
 * @property int $blockNumber 交易所在区块高度
 * @property string $from_a 转出地址
 * @property string $gas 交易所用GAS
 * @property string $gasPrice GAS单价
 * @property string $hash 交易hash值
 * @property string $nonce NONCE值
 * @property string $to_a 转入地址
 * @property string $transactionIndex 交易订单索引
 * @property string $value 交易值
 * @property int $type 类型 1:充值2:提现3:系统归币
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 */
class EthTransaction extends \yii\db\ActiveRecord
{
    const TYPE_DEPOSIT = 1;
    const TYPE_WITHDRAW = 2;
    const TYPE_COLLECT = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%eth_transaction}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['blockNumber', 'type', 'created_at', 'updated_at'], 'integer'],
            [['hash'], 'required'],
            [['blockHash', 'hash'], 'string', 'max' => 255],
            [['from_a', 'to_a'], 'string', 'max' => 42],
            [['gas', 'gasPrice', 'nonce', 'transactionIndex', 'value'], 'string', 'max' => 64],
            [['hash'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'et_id' => Yii::t('app', 'Et ID'),
            'blockHash' => Yii::t('app', 'Block Hash'),
            'blockNumber' => Yii::t('app', 'Block Number'),
            'from_a' => Yii::t('app', 'From'),
            'gas' => Yii::t('app', 'Gas'),
            'gasPrice' => Yii::t('app', 'Gas Price'),
            'hash' => Yii::t('app', 'Hash'),
            'nonce' => Yii::t('app', 'Nonce'),
            'to_a' => Yii::t('app', 'To'),
            'transactionIndex' => Yii::t('app', 'Transaction Index'),
            'value' => Yii::t('app', 'Value'),
            'type' => Yii::t('app', 'Type'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
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
                ]
            ]
        ];
    }
}
