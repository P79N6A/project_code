<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "{{%sysloan}}".
 *
 * @property string $id
 * @property integer $aid
 * @property string $request_id
 * @property string $loan_id
 * @property string $user_id
 * @property string $realname
 * @property string $identity
 * @property string $mobile
 * @property string $query_time
 * @property string $loan_create_time
 * @property string $end_date
 * @property integer $obs_status
 * @property integer $repay_cnt
 * @property integer $addr_parents_count
 * @property integer $addr_phones_nodups
 * @property string $com_days_answer
 * @property string $com_day_connect_mavg
 * @property string $com_tel_people
 * @property string $com_month_num
 * @property string $last_succ_loan_create_time
 * @property integer $mth1_app_cnt
 * @property integer $mth3_dlq14_num
 * @property integer $mth6_acp_num
 * @property integer $tot_prepmt_num
 * @property integer $tot_accept_num
 * @property integer $tot_dlq14_num
 * @property integer $tot_freject_num
 * @property string $create_time
 * @property string $modify_time
 */
class Overdueloan extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%overdueloan}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'request_id', 'loan_id', 'user_id', 'obs_status', 'repay_cnt', 'addr_parents_count', 'addr_phones_nodups', 'mth1_app_cnt', 'mth3_dlq14_num', 'mth6_acp_num', 'tot_prepmt_num', 'tot_accept_num', 'tot_dlq14_num', 'tot_freject_num'], 'integer'],
            [['user_id', 'identity', 'create_time', 'modify_time'], 'required'],
            [['query_time', 'loan_create_time', 'end_date', 'last_succ_loan_create_time', 'create_time', 'modify_time'], 'safe'],
            [['com_days_answer', 'com_day_connect_mavg', 'com_tel_people', 'com_month_num'], 'number'],
            [['realname', 'identity', 'mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'aid' => '产品类型 1：一亿元，8：714',
            'request_id' => '业务端借款ID',
            'loan_id' => '业务端借款ID',
            'user_id' => '业务端用户ID',
            'realname' => '用户真实姓名',
            'identity' => '用户身份证号',
            'mobile' => '用户手机号',
            'query_time' => '催收决策请求时间',
            'loan_create_time' => '借款申请时间',
            'end_date' => '用户最后还款时间',
            'obs_status' => '本次借款的逾期天数',
            'repay_cnt' => '本次借款已有还款次数',
            'addr_parents_count' => '通讯录中多个命名为妈，爸等亲属联系方式',
            'addr_phones_nodups' => '去重后手机号数量',
            'com_days_answer' => '天数:被叫天数',
            'com_day_connect_mavg' => '天数:通话天数',
            'com_tel_people' => '固话通话联系人',
            'com_month_num' => '通话间隔,月',
            'last_succ_loan_create_time' => '(据本次借款180天以内)最近一次成功借款的申请时间',
            'mth1_app_cnt' => '(据本次借款30天内)申请借款次数',
            'mth3_dlq14_num' => '(据本次借款90天内)逾期14天以上的借款次数',
            'mth6_acp_num' => '据本次借款180天内放款次数',
            'tot_prepmt_num' => '（本次借款之前）历史提前还款次数',
            'tot_accept_num' => '本次借款之前成功放款次数',
            'tot_dlq14_num' => '本次借款之前逾期14天以上放款次数',
            'tot_freject_num' => '本次借款之前客户历史欺诈拒绝次数',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ];
    }

    public function saveDate($postData)
    {
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $postData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function getLoansys($data)
    {
        $user_id = ArrayHelper::getValue($data, 'user_id', '');
        $aid = ArrayHelper::getValue($data, 'aid', '');
        $loan_id = ArrayHelper::getValue($data, 'loan_id', '');
        $time  = date('Y-m-d 00:00:00', strtotime('-6 days'));
        $where = ['and',
            ['loan_id'=>$loan_id], 
            ['user_id' => $user_id],
            ['aid'=>$aid], 
            ['>=','create_time', $time],
        ];
        $loan_sys = $this->find()->where($where)->limit(1)->orderBy('ID DESC')->asArray()->one();
        if (empty($loan_sys)) {
            return [];
        }
        return $loan_sys;
    }
}
