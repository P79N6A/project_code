<?php

namespace app\models\news;

use app\commonapi\Logger;
use Yii;

/**
 * This is the model class for table "yi_loan_goods".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $goods_id
 * @property string $goods_order_no
 * @property string $goods_price
 * @property string $goods_name
 * @property string $goods_attribute_value
 * @property string $loan_amount
 * @property integer $loan_days
 * @property string $loan_desc
 * @property string $loan_create_time
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Loan_goods extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_goods';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'goods_id', 'goods_order_no', 'goods_price', 'goods_name', 'loan_amount', 'loan_days', 'loan_desc', 'loan_create_time', 'create_time', 'last_modify_time'], 'required'],
            [['user_id', 'loan_id', 'goods_id', 'loan_days', 'version'], 'integer'],
            [['goods_price', 'loan_amount'], 'number'],
            [['loan_create_time', 'create_time', 'last_modify_time'], 'safe'],
            [['goods_order_no'], 'string', 'max' => 32],
            [['goods_name'], 'string', 'max' => 64],
            [['goods_attribute_value', 'loan_desc'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'goods_id' => 'Goods ID',
            'goods_order_no' => 'Goods Order No',
            'goods_price' => 'Goods Price',
            'goods_name' => 'Goods Name',
            'goods_attribute_value' => 'Goods Attribute Value',
            'loan_amount' => 'Loan Amount',
            'loan_days' => 'Loan Days',
            'loan_desc' => 'Loan Desc',
            'loan_create_time' => 'Loan Create Time',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function saveLoanGoodsInfo($data)
    {
        if (empty($data) || !is_array($data)) {
            return false;
        }
        $error = $this->chkAttributes($data);
        if ($error) {
            Logger::dayLog('saveerror/loangoods',$data,$error);
            return FALSE;
        }
        return $this->save();
    }
}
