<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%bank_support}}".
 *
 * @property integer $id
 * @property string $bankname
 * @property string $bankcode
 * @property integer $card_type
 * @property integer $pay_type
 * @property integer $province
 * @property string $create_time
 */
class BankSupport extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_support}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bankname', 'bankcode'], 'required'],
            [['card_type', 'pay_type', 'province', 'status', 'create_time'], 'integer'],
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
            'card_type' => '1:储蓄卡; 2:信用卡',
            'pay_type' => '1:易宝一键; 2:易宝投资通',
            'province' => '开户行省份',
            'status' => '启用', //0:未启用 1:正常
            'create_time' => '创建时间',
        ];
    }
	/**
	 * 获取支付的路由
	 */
	public function getPayRoute($bankcode, $card_type){
		if(empty($bankcode)){
			return null;
		}
		$bankcode = $this->getBankCodeAlias($bankcode);
		$condition = [
			'bankcode' => $bankcode,
			'card_type' => $card_type,
		];
		return static::find() -> where($condition) ->orderBy("weight DESC") -> one();
	}
  /**
   * 根据银行标准名称，卡类型，应用对应通道获取支付路由
   */
  public function getNewPayRoute($bankname, $card_type, $payroute){
      if(empty($bankname) || empty($card_type) || empty($payroute)){
            return null;
      }
      $condition = [
          'std_bankname' => $bankname,
          'card_type' => $card_type,
          'pay_type' => $payroute,
          'status' => 1, // 1:启用
      ];
      return static::find() -> where($condition) ->orderBy("weight DESC") -> one();
  }
	/**
	 * 获取银行编号
	 * @param $bankcode
	 * @return string
	 */
	public function getBankCodeAlias($bankcode,$bankname=''){
		//1 别名判断
		$bankcode = trim($bankcode);
		$bankname = trim($bankname);
		$map = [
			'BCM' => 'BOCO', //交通银行
			'BOB' => 'BCCB', //北京银行
			'CMB' => 'CMBCHINA', //招商银行
		];
		if(isset($map[$bankcode])){
			return $map[$bankcode];
		}
		
		//2 名称判断
		$mapname = [
			'上海银行' => 'SHB',
			'中信银行' => 'ECITIC',
			'光大银行' => 'CEB',
			'华夏银行' => 'HXB',
			'平安银行' => 'PINGAN',
			'广发银行股份有限公司' => 'GDB',
		];
		if($bankname && isset($mapname[$bankname])){
			return $mapname[$bankname];
		}
		
		//3 直接返回
		return $bankcode;
	}
}
