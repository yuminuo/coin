<?php

namespace adamyxt\coin;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%address_num}}".
 *
 * @property int $an_id
 * @property string $address 地址
 * @property int $type 类型 1:BTC2:ETH3:USDT
 * @property string $num 数量
 * @property string $ice_num 冻结数量
 * @property int $status 账户状态 1:正常2:无效
 * @property string $tags 多字段联合唯一键
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 */
class AddressNum extends \yii\db\ActiveRecord
{

    const TYPE_BTC = 1;
    const TYPE_ETH = 2;
    const TYPE_USDT = 3;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%address_num}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address', 'num', 'ice_num', 'tags'], 'required'],
            [['type', 'status', 'created_at', 'updated_at'], 'integer'],
            [['num', 'ice_num'], 'number'],
            [['address'], 'string', 'max' => 42],
            [['tags'], 'string', 'max' => 32],
            [['tags'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'an_id' => Yii::t('app', 'An ID'),
            'address' => Yii::t('app', 'Address'),
            'type' => Yii::t('app', 'Type'),
            'num' => Yii::t('app', 'Num'),
            'ice_num' => Yii::t('app', 'Ice Num'),
            'status' => Yii::t('app', 'Status'),
            'tags' => Yii::t('app', 'Tags'),
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
                'class'=>TimestampBehavior::class,
                'attributes'=>[
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ]
            ],
            [
                'class' => AttributeBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => 'tags',
                ],
                'value' => function ($event) {
                    return md5($this->address.$this->type);
                },
            ],
        ];
    }
}
