<?php

namespace adamyxt\coin;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%address}}".
 *
 * @property int $a_id
 * @property string $app_type apptype
 * @property string $address 钱包地址
 * @property int $type 类型 1:BTC2ETH:3:USDT
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 */
class Address extends \yii\db\ActiveRecord
{
    const TYPE_BTC = 1;
    const TYPE_ETH = 2;
    const TYPE_USDT = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%address}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_type', 'address'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['app_type'], 'string', 'max' => 20],
            [['address'], 'string', 'max' => 64],
            [['address'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'a_id' => 'A ID',
            'app_type' => 'App Type',
            'address' => 'Address',
            'type' => 'Type',
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
                'class'=>TimestampBehavior::class,
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ]
            ],
        ];
    }
}
