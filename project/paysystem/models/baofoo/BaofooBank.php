<?php

namespace app\models\baofoo;
/**
 *  @des宝付银行卡对应code
 */
class BaofooBank extends \app\models\BasePay {

    /**
     * @des
     */
    public static function tableName() {
        return 'pay_baofoo_bank';
    }

    /**
     * @des
     */
    public function rules() {
        return [
            [['bankname', 'baofoo_bankname','baofoo_bankcode'], 'string', 'max' => 50],
        ];
    }

    /**
     * @des
     */
    public function attributeLabels() {
        return [
            'id'          => '主键',
            'bankname'         => '银行名',
            'baofoo_bankname'  => '宝付银行名称',
            'baofoo_bankcode'     => '宝付银行编号',
            'create_time'     => '创建时间',
        ];
    }

    /**
     * @des 获取标准化银行名称
     * @param  string $alias 银行名称
     * @return str bankcode
     */
    public function getBaofooBankcode($bankname){
        if(!$bankname) return '';
        $row = static::find() -> where(['bankname'=>$bankname]) -> limit(1) -> one();
        return is_object($row) && isset($row['baofoo_bankcode']) && $row['baofoo_bankcode']  ?  $row['baofoo_bankcode']  : '';
    }

}
