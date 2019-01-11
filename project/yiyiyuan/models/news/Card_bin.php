<?php

namespace app\models\news;

use app\models\BaseModel;
use app\commonapi\Apihttp;
use app\common\ApiCrypt;
use app\commonapi\Logger;
use Yii;

/**
 * This is the model class for table "yi_card_bin".
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
class Card_bin extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_card_bin';
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
     *通过卡号获取银行卡的绑卡信息
     * @param int $card  卡号
     * @param string $order 排序
     * @return array
     */
    public function getCardBinByCard($card,$order=""){
        if (empty($card)) {
            return null;
        }
        $sql = "SELECT * FROM " . self::tableName() . " WHERE card_length = " . strlen($card) . " AND prefix_value=left('" . $card . "',prefix_length)";
        if(!empty($order)){
            $sql .= " order by ".$order;
        }
        $card_bin = Yii::$app->db->createCommand($sql)->queryOne();
        if(!$card_bin){
            $res = [
                "cardno" => $card
            ];
            $returnData = (new Apihttp())->gitCardBin($res);
            Logger::errorLog(print_r(array($returnData), true), 'Checkbank__return', 'Cardbin');
            //$str = '{"res_code":0,"res_data":"Kbjm02BYjZ2BBgExv95yrLEybhT5SZPRx402PM0uMNWeuVHbWZTbhsUYy1xkLFK7KwhWkzGXQ+Fmt28T4bxuHgoPMo7\/OzrU\/yLo6xK7UuJEqCsoQsbMgt3jCNKggXEsRz2fYEVwbm75y2D072o7DH57rbj6d8VMFXwYnCVitww9oBD+WVnC8lmwPEuEfVseExgNAZ6f4mMlJd9eLW1dc29stZxrsDgxe3uGzNGeXFATQF6BC5BYK2gtvWhcBuIk0RrTtI4P7QRON58+3GwcnK4jvLcl7NCetXPIEeNjLuz8CiroJGRK0E4tVDf3p1rYtHgTVNUz\/yQRuMIwJb\/\/XvGn9lZAY9ixTPFKW93htUQF4EI0p3ANag=="}';
            if($returnData['res_code'] == 0){
                $card_info = (new ApiCrypt())->parseData($returnData['res_data'],'24BEFILOPQRUVWXcdhntvwxy');
                $card_bin = $card_info['res_data'];
                $id = $this->addCardBin($card_bin);
                $card_bin['id'] = $id;
            }else{
                $card_bin = false;
            }
        }
        return $card_bin;
    }

    public function addCardBin($card_bin)
    {
        try{
            $data = [
                'bank_name'     => $card_bin['bank_name'],
                'bank_code'     => $card_bin['bank_code'],
                'bank_abbr'     => $card_bin['bank_abbr'],
                'card_name'     => $card_bin['card_name'],
                'card_length'   => $card_bin['card_length'],
                'prefix_length' => $card_bin['prefix_length'],
                'prefix_value'  => $card_bin['prefix_value'],
                'card_type'     => $card_bin['card_type'],
            ];
            $error = $this->chkAttributes($data);
            if ($error) {
                return false;
            }
            $this->save();
            return $this->id;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }
}
