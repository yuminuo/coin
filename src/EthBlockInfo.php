<?php

namespace adamyxt\coin;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%eth_block_info}}".
 *
 * @property int $ebi_id
 * @property string $hash 区块hash值
 * @property string $parentHash 父区块hash值
 * @property int $number 区块高度
 * @property int $timestamp 区块创建时间
 * @property int $created_at 创建时间
 * @property int $updated_at 修改时间
 */
class EthBlockInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%eth_block_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['hash', 'parentHash', 'number', 'timestamp'], 'required'],
            [['number', 'timestamp', 'created_at', 'updated_at'], 'integer'],
            [['hash', 'parentHash'], 'string', 'max' => 255],
            [['hash'], 'unique'],
            [['number'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ebi_id' => Yii::t('app', 'Ebi ID'),
            'hash' => Yii::t('app', 'Hash'),
            'parentHash' => Yii::t('app', 'Parent Hash'),
            'number' => Yii::t('app', 'Number'),
            'timestamp' => Yii::t('app', 'Timestamp'),
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
