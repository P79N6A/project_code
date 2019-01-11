<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "yi_user_loan_extend".
 * 借款详情附属表
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property string $uuid
 * @property integer $outmoney
 * @property integer $payment_channel
 * @property string $userIp
 * @property integer $extend_type
 * @property integer $success_num
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $fund
 * @property string $status
 * @property integer $version
 */
class UserLoanExtend extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_loan_extend';
    }
    
    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['user_id', 'loan_id', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'outmoney', 'payment_channel', 'extend_type', 'success_num', 'fund', 'version', 'loan_total', 'loan_success'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['loan_quota'], 'number'],
            [['uuid'], 'string', 'max' => 55],
            [['userIp'], 'string', 'max' => 64],
            [['status'], 'string', 'max' => 16]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => '主键',
            'user_id' => '用户id',
            'loan_id' => '借款id',
            'uuid' => 'app设备编号',
            'outmoney' => '是否出款0:不出;1:出',
            'payment_channel' => '支付通道1:新浪;2:中信;3:玖富;6:融宝',
            'userIp' => '用户借款ip',
            'extend_type' => '附属表状态1:初始;2:审核通过;3:审核通过驳回',
            'success_num' => '借款成功次数',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
            'fund' => '资金方:1:花生米富; 2:玖富;',
            'status' => '状态',
            'version' => '乐观锁',
            'loan_total' => '总借款次数',
            'loan_success' => '成功借款次数',
            'loan_quota' => '当前借款时额度',
        ]; 
    } 

    public function getUserLoan(){
        return $this->hasOne(UserLoan::className(), ['loan_id' => 'loan_id']);
    }
    public function getLoanExtend($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->orderby('ID DESC')->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }

    public function getLoanExtendInfo($where,$select = '*')
    {
        $res =  $this->find()->select($select)->where($where)->orderby('ID DESC')->one();
        return $res;
    }
}
