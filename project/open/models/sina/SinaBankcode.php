<?php

namespace app\models\sina;

use Yii;
use app\models\CardBin;
/**
 * This is the model class for table "sina_bankcode".
 *
 * @property integer $id
 * @property string $bankname
 * @property string $bankcode
 * @property string $create_time
 */
class SinaBankcode extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sina_bankcode';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bankname', 'bankcode', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['bankname', 'bankcode'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bankname' => '银行名称',
            'bankcode' => '银行编号',
            'create_time' => '创建时间',
        ];
    }
    /**
     * 获取新浪的银行编码
     * @param  string $cardno 银行卡号
     * @return string
     */
    public function getBankCode($cardno){
        $cardbin = CardBin::getCardBin($cardno);
        if(!$cardbin){
            return '';
        }
        $bankcode = $cardbin->bank_abbr;
        $bankname = $cardbin->bank_name;

        $bankcode = $this->getBankCodeAlias($bankcode);
        $model = static::find() -> where(['bankcode'=>$bankcode]) -> limit(1) -> one();
        if($model){
            return $model->bankcode;
        }

        $bankname = $this->getBankNameAlias($bankname);
        $model = static::find() -> where(['bankname'=>$bankname]) -> limit(1) -> one();
        if($model){
            return $model->bankcode;
        }

        return '';
    }
    /**
     * 获取银行编号 卡bin表的映射
     * @param $bankcode
     * @return string
     */
    private function getBankCodeAlias($bankcode) {
        //1 别名判断
        $bankcode = trim($bankcode);
        //cardbin=>sina_bankcode
        $map = [
            'ABC' => 'ABC',
            'BCM' => 'COMM',
            'BOB' => 'BCCB',
            'BOC' => 'BOC',
            'CCB' => 'CCB',
            'CEB' => 'CEB',
            'CIB' => 'CIB',
            'CMB' => 'CMB',
            'CMBC' => 'CMBC',
            'ECITIC' => 'CITIC',
            'GDB' => 'GDB',
            'HXB' => 'HXB',
            'ICBC' => 'ICBC',
            'PINGAN' => 'SZPAB',
            'POST' => 'PSBC',
            'SHB' => 'BOS',
        ];
        return isset($map[$bankcode]) ? $map[$bankcode] : $bankcode;
    }
    /**
     * 名称映射关系 卡bin->sina_bank_code表
     * @param  [type] $bankname [description]
     * @return [type]           [description]
     */
    private function getBankNameAlias($bankname){
        //1 别名判断
        $bankname = trim($bankname);
        //cardbin=>sina_bankcode
        $map = [
            '广州农村商业银行' => '广州市农信社',
            '广州农村商业银行股份有限公司' => '广州市农信社',
            '广州银行股份有限公司' => '广州市商业银行',
            '杭州商业银行' => '杭州银行',
            '杭州市商业银行' => '杭州银行',
            '中国邮政储蓄' => '中国邮储银行',
            '上海农商银行' => '上海农村商业银行',
            '上海农商银行贷记卡' => '上海农村商业银行',
            '晋城银行股份有限公司' => '晋城市商业银行',
            '长沙银行股份有限公司' => '长沙银行',
            //'no' => '银联在线支付',
            '温州银行' => '温州市商业银行',
            '广发银行股份有限公司' => '广东发展银行',
        ];
        return isset($map[$bankname]) ? $map[$bankname] : $bankname;
    }
}
