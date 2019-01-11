<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "{{%yi_user_loan_flows}}".
 *
 * @property integer $id
 * @property integer $loan_id
 * @property integer $admin_id
 * @property integer $loan_status
 * @property string $relative
 * @property string $reason
 * @property string $create_time
 * @property string $admin_name
 * @property integer $type
 */
class UserLoanFlows extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_loan_flows';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'admin_id'], 'required'],
            [['loan_id', 'admin_id', 'loan_status', 'type'], 'integer'],
            [['create_time'], 'safe'],
            [['relative', 'reason'], 'string', 'max' => 1024],
            [['admin_name'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => '借款ID',
            'admin_id' => '操作人员ID  0用户 -1系统 其他为管理员',
            'loan_status' => '管理员当前操作变更的借款状态',
            'relative' => '借款相关数据',
            'reason' => '用户操作原因',
            'create_time' => 'Create Time',
            'admin_name' => '管理员姓名',
            'type' => '1默认2备注',
        ];
    }

    public function frejectNum($data)
    {
        $loan_id = ArrayHelper::getValue($data,'loan_id');
        $where = ['loan_id'=> $loan_id,'loan_status'=> 7,'admin_id' => -2];
        $freject_num = $this->find()->where($where)->count();
        return (int)$freject_num;
    }   
}
