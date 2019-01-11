<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "st_prome".
 *
 * @property string $id
 * @property integer $aid
 * @property string $request_id
 * @property string $loan_id
 * @property string $user_id
 * @property string $query_time
 * @property integer $addr_parents_count
 * @property integer $addr_tel_count
 * @property integer $com_r_rank
 * @property integer $score
 * @property string $consume_fund_index
 * @property string $indentity_risk_index
 * @property string $social_stability_index
 * @property integer $realadl_tot_reject_num
 * @property integer $realadl_tot_freject_num
 * @property integer $realadl_tot_sreject_num
 * @property integer $realadl_tot_dlq14_num
 * @property integer $realadl_dlq14_ratio
 * @property integer $realadl_wst_dlq_sts
 * @property string $com_day_connect_mavg
 * @property string $com_night_connect_p
 * @property integer $com_tel_people
 * @property integer $com_valid_mobile
 * @property string $com_month_call_duration
 * @property string $com_hours_call_davg
 * @property integer $com_count
 * @property integer $com_call
 * @property integer $same_phone_num
 * @property integer $report_use_time
 * @property integer $tot_phone_num
 * @property integer $last3_all
 * @property integer $last3_not_mobile_count
 * @property integer $last6_not_mobile_count
 * @property string $retain_ratio
 * @property string $last_3mth_Oth_ratio
 * @property string $last_3mth_oth_incr
 * @property string $becalled_ratio
 * @property string $com_c_user
 * @property integer $report_type
 * @property string $create_time
 * @property string $modify_time
 */
class StProme extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_prome';
    }

    /**
     * @inheritdoc
     */
    public function rules() 
    { 
        return [
            [['aid', 'request_id', 'loan_id', 'yy_req_id', 'user_id', 'com_c_user', 'report_type'], 'integer'],
            [['request_id', 'loan_id', 'user_id', 'create_time', 'modify_time'], 'required'],
            [['query_time', 'create_time', 'modify_time'], 'safe'],
            [['retain_ratio', 'last_3mth_oth_ratio', 'last_3mth_oth_incr', 'becalled_ratio'], 'number']
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
            'yy_req_id' => '运营商报告请求ID',
            'user_id' => '业务端用户ID',
            'query_time' => '催收决策请求时间',
            'retain_ratio' => 'Retain Ratio',
            'last_3mth_oth_ratio' => 'Last 3mth Oth Ratio',
            'last_3mth_oth_incr' => 'Last 3mth Oth Incr',
            'becalled_ratio' => 'Becalled Ratio',
            'com_c_user' => 'Com C User',
            'report_type' => '运营商报告数据来源',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ]; 
    } 

    public function addPromeInfo($postData)
    {
        $saveData = [
            'aid' => isset($postData['aid']) ? $postData['aid'] : 1,
            'request_id' => isset($postData['request_id']) ? $postData['request_id'] : 0,
            'loan_id' => isset($postData['loan_id']) ? $postData['loan_id'] : 0,
            'user_id' => isset($postData['user_id']) ? $postData['user_id'] : 0,
            'query_time' => isset($postData['query_time']) ? $postData['query_time'] : '',
            'yy_req_id' => isset($postData['yy_request_id']) ? $postData['yy_request_id'] : '',
            'mobile' => isset($postData['mobile']) ? $postData['mobile'] : '',
            'retain_ratio' => isset($postData['retain_ratio']) ? $postData['retain_ratio'] : null,
            'last_3mth_oth_ratio' => isset($postData['last_3mth_Oth_ratio']) ? $postData['last_3mth_Oth_ratio'] : null,
            'last_3mth_oth_incr' => isset($postData['last_3mth_oth_incr']) ? $postData['last_3mth_oth_incr'] : null,
            'becalled_ratio' => isset($postData['becalled_ratio']) ? $postData['becalled_ratio'] : null,
            'com_c_user' => isset($postData['com_c_user']) ? $postData['com_c_user'] : 0,
            'report_type' => isset($postData['report_type']) ? $postData['report_type'] : 0,
            'indentity_risk_index' => isset($postData['indentity_risk_index']) ? $postData['indentity_risk_index'] : 0,
            'social_stability_index' => isset($postData['social_stability_index']) ? $postData['social_stability_index'] : 0,
            'consume_fund_index' => isset($postData['consume_fund_index']) ? $postData['consume_fund_index'] : 0,
            'score' => isset($postData['score']) ? $postData['score'] : 0,
        ];
        //数据类型转换
        $nowtime = date('Y-m-d H:i:s');
        $saveData['create_time'] = $nowtime;
        $saveData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($saveData);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
