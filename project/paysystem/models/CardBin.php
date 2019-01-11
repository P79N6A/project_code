<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%card_bin}}".
 *
 * @property string $id
 * @property string $bank_name
 * @property string $bank_code
 * @property string $bank_abbr
 * @property string $card_name
 * @property string $card_length
 * @property string $prefix_length
 * @property string $prefix_value
 * @property integer $card_type
 */
class CardBin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%card_bin}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_length', 'prefix_length', 'card_type'], 'integer'],
            [['bank_name', 'card_name'], 'string', 'max' => 60],
            [['bank_code', 'bank_abbr'], 'string', 'max' => 8],
            [['prefix_value'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增主键',
            'bank_name' => '银行名称',
            'bank_code' => '银行机构代码',
            'bank_abbr' => '银行简称',
            'card_name' => '卡名称',
            'card_length' => '卡长度',
            'prefix_length' => '比较卡号前面的位数',
            'prefix_value' => '卡前面的数值',
            'card_type' => '卡类型:0为借记卡，1为贷记卡,2预付费卡，3准贷记卡',
        ];
    }
    /**
     * 检查卡bind
     * @param  string $card_num 银行卡号
     * @return cardbin
     */
    public static function getCardBin($card_num)
    {
        if(!$card_num){
            return null;
        }
        $length = strlen($card_num);
        $condition = "card_length = '{$length}' AND prefix_value=left('{$card_num}',prefix_length)";
        return static::find()->where($condition) -> orderBy('prefix_length DESC') -> one();
    }
}
