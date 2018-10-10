<?php

namespace adamyxt\coin;

use Yii;

/**
 * This is the model class for table "{{%address_store}}".
 *
 * @property int $as_id
 * @property string $address 钱包地址
 * @property string $keyt 密钥
 * @property int $used 是否使用1:否2:是
 * @property int $created_at 创建时间
 */
class AddressStore extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%address_store}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['address', 'keyt'], 'required'],
            [['used', 'created_at'], 'integer'],
            [['address'], 'string', 'max' => 64],
            [['keyt'], 'string', 'max' => 255],
            [['address'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'as_id' => Yii::t('app', 'As ID'),
            'address' => Yii::t('app', 'Address'),
            'keyt' => Yii::t('app', 'Keyt'),
            'used' => Yii::t('app', 'Used'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }
}
