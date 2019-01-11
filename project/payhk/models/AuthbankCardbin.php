<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "authbank_cardbin".
 *
 * @property integer $id
 * @property string $bank_name
 * @property string $bank_code
 * @property string $bank_abbr
 * @property string $card_name
 * @property integer $card_length
 * @property integer $prefix_length
 * @property string $prefix_value
 * @property integer $card_type
 */
class AuthbankCardbin extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'authbank_cardbin';
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
            'id' => 'ID',
            'bank_name' => 'Bank Name',
            'bank_code' => 'Bank Code',
            'bank_abbr' => 'Bank Abbr',
            'card_name' => 'Card Name',
            'card_length' => 'Card Length',
            'prefix_length' => 'Prefix Length',
            'prefix_value' => 'Prefix Value',
            'card_type' => 'Card Type',
        ];
    }

    /**
     * 检查卡bind
     * @param  string $card_num 银行卡号
     * @return cardbin
     */
    public function getCardBin($card_num)
    {
        if(!$card_num){
            return null;
        }
        $length = strlen($card_num);
        $condition = "card_length = '{$length}' AND prefix_value=left('{$card_num}',prefix_length)";
        return static::find()->where($condition) -> orderBy('prefix_length DESC') -> one();
    }

    /**
     * 保存到数据库中
     */
    public function saveData($postData) {
        if (empty($postData)) {
            return false;
        }
        $data = [
            'bank_name' => $postData['bank_name'],
            'bank_code' => $postData['bank_code'],
            'bank_abbr' => $postData['bank_abbr'],
            'card_name' => $postData['card_name'],
            'card_length' => intval($postData['card_length']),
            'prefix_value' => $postData['prefix_value'],
            'prefix_length' => intval($postData['prefix_length']),
            'card_type' => intval($postData['card_type']),
        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(false, implode("|", $error));
        }
        return $this->save();
    }

}
