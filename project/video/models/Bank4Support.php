<?php
/**
 * 银行四要素支持情况
 * 此类继承自易宝银行卡支持
 */
namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%bank4_support}}".
 *
 * @property integer $id
 * @property string $bankname
 * @property string $bankcode
 * @property integer $card_type
 * @property string $create_time
 */
class Bank4Support extends BankSupport
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank4_support}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bankname', 'bankcode'], 'required'],
            [['card_type', 'create_time'], 'integer'],
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
            'create_time' => '创建时间',
        ];
    }
	/**
	 * 检测支持情况
	 * @param str $card_type 银行卡类型
	 * @param str $bankcode 银行编码
	 * @param str $bankname 银行名称
	 * 
	 */
	public function support($card_type, $bankcode, $bankname){
		//1 转换银行编码为有效的形式
		$bankcode = $this->getBankCodeAlias($bankcode, $bankname);
		
		//2 查询结果
		$condition = [
			'bankcode' => $bankcode, 
			'card_type' => $card_type,
		];
		$res = static::find() ->where($condition) -> one();
		return !empty($res);
	}
}
