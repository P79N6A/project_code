<?php

namespace app\modules\balance\models\peanut;
use app\common\Wxapi;
use app\common\Func;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%standard_order}}". 
 * 
 * @property string $id
 * @property string $version
 * @property string $order_no
 * @property string $standard_id
 * @property string $user_id
 * @property string $buy_type
 * @property integer $order_valid_period
 * @property string $order_valid_end_date
 * @property string $goods_name
 * @property string $goods_desc
 * @property string $org_number
 * @property string $buy_amount
 * @property integer $buy_share
 * @property string $buyer_fee
 * @property string $order_status
 * @property string $achieved_interest
 * @property string $achieving_interest
 * @property string $start_date
 * @property string $end_date
 * @property string $contract
 * @property string $contract_url
 * @property integer $come_from
 * @property string $yield
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $is_notice
 * @property integer $is_red
 */ 
class Standard_order extends PeanutBase {

	public $name;
	public $financed_amount;
	

	 /** 
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return 'pea_standard_order';
    } 

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['version', 'standard_id', 'user_id', 'order_valid_period', 'buy_share', 'come_from', 'coupon_id', 'is_notice', 'is_red'], 'integer'],
            [['order_no', 'standard_id', 'user_id', 'buy_type', 'goods_name', 'buy_amount', 'buy_share', 'order_status',  'achieving_interest', 'start_date', 'end_date', 'yield', 'last_modify_time', 'create_time'], 'required'],
            [['order_valid_end_date', 'start_date', 'end_date', 'last_modify_time', 'create_time'], 'safe'],
            [['goods_desc'], 'string'],
            [['buy_amount', 'buyer_fee', 'achieved_interest', 'achieving_interest', 'yield', 'coupon_interest'], 'number'],
            [['order_no', 'org_number'], 'string', 'max' => 32],
            [['buy_type', 'order_status'], 'string', 'max' => 16],
            [['goods_name'], 'string', 'max' => 64],
            [['contract'], 'string', 'max' => 20],
            [['contract_url'], 'string', 'max' => 128]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'version' => 'Version',
            'order_no' => 'Order No',
            'standard_id' => 'Standard ID',
            'user_id' => 'User ID',
            'buy_type' => 'Buy Type',
            'order_valid_period' => 'Order Valid Period',
            'order_valid_end_date' => 'Order Valid End Date',
            'goods_name' => 'Goods Name',
            'goods_desc' => 'Goods Desc',
            'org_number' => 'Org Number',
            'buy_amount' => 'Buy Amount',
            'buy_share' => 'Buy Share',
            'buyer_fee' => 'Buyer Fee',
            'order_status' => 'Order Status',
            'achieved_interest' => 'Achieved Interest',
            'achieving_interest' => 'Achieving Interest',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'contract' => 'Contract',
            'contract_url' => 'Contract Url',
            'come_from' => 'Come From',
            'yield' => 'Yield',
            'coupon_id' => 'Coupon ID',
            'coupon_interest' => 'Coupon Interest',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'is_notice' => 'Is Notice',
            'is_red' => 'Is Red',
        ]; 
    }

	/**
	 * 实付花生米富投资人本金
	 * @param $condition
	 * @return int
	 */
	public function investorPrincipal($condition)
	{
		if (empty($condition)){
			return 0;
		}
		$where_config = [
			'AND',
			['>=', 'create_time', ArrayHelper::getValue($condition, 'start_time')],
			['<=', 'create_time', ArrayHelper::getValue($condition, 'end_time')],
			['<', 'order_valid_end_date', date("Y-m-d")],
		];
		$total = self::find()->where($where_config)->sum('buy_amount');
		return empty($total) ? 0 : $total;

	}
	/**
	 * 实付花生米富投资人本金
	 * @param $condition
	 * @return int
	 */
	public function buyerFee($condition)
	{
		if (empty($condition)){
			return 0;
		}
		$where_config = [
			'AND',
			['>=', 'create_time', ArrayHelper::getValue($condition, 'start_time')],
			['<=', 'create_time', ArrayHelper::getValue($condition, 'end_time')],
			['<', 'order_valid_end_date', date("Y-m-d")],
		];
		$total = self::find()->where($where_config)->sum('buyer_fee');
		return empty($total) ? 0 : $total;

	}
}
