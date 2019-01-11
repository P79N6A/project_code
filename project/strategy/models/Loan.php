<?php

namespace app\models;

use Yii; 

/** 
 * This is the model class for table "st_loan". 
 * 
 * @property string $id
 * @property string $request_id_one
 * @property string $loan_id
 * @property string $loan_no
 * @property string $user_id
 * @property string $realname
 * @property string $identity
 * @property integer $is_black
 * @property integer $type
 * @property string $source
 * @property string $amount
 * @property string $mobile
 * @property string $telephone
 * @property integer $business_type
 * @property integer $come_from
 * @property integer $days
 * @property integer $bph_fm_sx
 * @property integer $bph_y
 * @property integer $bph_other
 * @property integer $bph_fm_small
 * @property integer $bph_fm_fack
 * @property integer $bph_br
 * @property integer $bid_fm_sx
 * @property integer $bid_fm_court_sx
 * @property integer $bid_fm_court_enforce
 * @property integer $bid_fm_lost
 * @property integer $bid_y
 * @property integer $bid_other
 * @property integer $bid_br
 * @property integer $mph_y
 * @property integer $mph_fm
 * @property integer $mph_other
 * @property integer $mph_br
 * @property integer $mid_y
 * @property integer $mid_fm
 * @property integer $mid_other
 * @property integer $mid_br
 * @property integer $addr_contacts_count
 * @property integer $addr_relative_count
 * @property integer $com_r_total_mavg
 * @property integer $com_c_total_mavg
 * @property integer $com_r_rank
 * @property integer $com_c_total
 * @property integer $com_r_total
 * @property integer $addr_count
 * @property integer $report_use_time
 * @property integer $report_loan_connect
 * @property integer $report_110
 * @property integer $report_120
 * @property integer $report_lawyer
 * @property integer $report_aomen
 * @property integer $report_court
 * @property integer $report_fcblack
 * @property integer $report_shutdown
 * @property integer $com_hours_connect
 * @property integer $com_valid_all
 * @property integer $com_valid_mobile
 * @property integer $vs_phone_match
 * @property integer $vs_valid_match
 * @property integer $addr_has_black
 * @property integer $is_amount_up
 * @property integer $is_white_true
 * @property integer $is_bank_edit
 * @property integer $is_info_edit
 * @property integer $is_report_edit
 * @property string $report_night_percent
 * @property integer $addr_collection_count
 * @property string $query_time
 * @property string $loan_create_time
 * @property integer $one_more_loan_value
 * @property integer $seven_more_loan_value
 * @property integer $last_step
 * @property integer $success_num
 * @property string $create_time
 * @property string $modify_time
 * @property string $request_id_two
 * @property integer $one_number_account_value
 * @property integer $prd_type
 */ 
class Loan extends BaseModel
{ 
    /** 
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return 'st_loan'; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['request_id_one', 'loan_id', 'user_id', 'is_black', 'type', 'business_type', 'come_from', 'days', 'bph_fm_sx', 'bph_y', 'bph_other', 'bph_fm_small', 'bph_fm_fack', 'bph_br', 'bid_fm_sx', 'bid_fm_court_sx', 'bid_fm_court_enforce', 'bid_fm_lost', 'bid_y', 'bid_other', 'bid_br', 'mph_y', 'mph_fm', 'mph_other', 'mph_br', 'mid_y', 'mid_fm', 'mid_other', 'mid_br', 'addr_contacts_count', 'addr_relative_count', 'com_r_total_mavg', 'com_c_total_mavg', 'com_r_rank', 'com_c_total', 'com_r_total', 'addr_count', 'report_use_time', 'report_loan_connect', 'report_110', 'report_120', 'report_lawyer', 'report_aomen', 'report_court', 'report_fcblack', 'report_shutdown', 'com_hours_connect', 'com_valid_all', 'com_valid_mobile', 'vs_phone_match', 'vs_valid_match', 'addr_has_black', 'is_amount_up', 'is_white_true', 'is_bank_edit', 'is_info_edit', 'is_report_edit', 'addr_collection_count', 'one_more_loan_value', 'seven_more_loan_value', 'last_step', 'success_num', 'request_id_two', 'one_number_account_value', 'prd_type'], 'integer'],
            [['loan_no', 'user_id', 'identity', 'create_time', 'modify_time'], 'required'],
            [['amount', 'report_night_percent'], 'number'],
            [['query_time', 'loan_create_time', 'create_time', 'modify_time'], 'safe'],
            [['loan_no'], 'string', 'max' => 64],
            [['realname', 'identity', 'source', 'mobile', 'telephone'], 'string', 'max' => 20]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => '主键',
            'request_id_one' => '借款决策1请求ID',
            'loan_id' => '业务端借款ID',
            'loan_no' => '借款编码',
            'user_id' => '业务端用户ID',
            'realname' => '用户真实姓名',
            'identity' => '用户身份证号',
            'is_black' => '借款黑名单触发情况',
            'type' => '用户类型：1首次借贷类型；2复贷类型；',
            'source' => '借款来源',
            'amount' => '借款金额',
            'mobile' => '用户手机号',
            'telephone' => '公司电话',
            'business_type' => '借款类型（业务类型）1:好友;2:担保;3:担保人;4:担保卡',
            'come_from' => '用户来源：1原先花过审用户；2新增；3和4为app注册的用户；5 百思;6 28元优惠券领取注册；9 短信推广；10 注册失败召回；11 快服务合作；12 乡村推广',
            'days' => '借款天数',
            'bph_fm_sx' => '手机号命中同盾失信证据库',
            'bph_y' => '手机号命中先花黑名单库',
            'bph_other' => '手机号命中三方黑名单库',
            'bph_fm_small' => '手机号命中同盾小号库',
            'bph_fm_fack' => '手机号命中同盾虚假号码库',
            'bph_br' => '手机号命中百融黑名单库',
            'bid_fm_sx' => '身份证号命中同盾失信证据库',
            'bid_fm_court_sx' => '身份证号命中同盾法院失信证据库',
            'bid_fm_court_enforce' => '身份证号命中同盾法院执行证据库',
            'bid_fm_lost' => '身份证号命中同盾失联证据库',
            'bid_y' => '身份证号命中先花黑名单库',
            'bid_other' => '身份证号命中三方黑名单库',
            'bid_br' => '身份证号命中百融黑名单库',
            'mph_y' => '手机号一亿元多投',
            'mph_fm' => '手机号同盾多投',
            'mph_other' => '手机号第三方多投',
            'mph_br' => '手机号百融多投',
            'mid_y' => '身份证号一亿元多投',
            'mid_fm' => '身份证号同盾多投',
            'mid_other' => '身份证号第三方多投',
            'mid_br' => '身份证号百融多投',
            'addr_contacts_count' => '常用联系人与通讯录匹配度',
            'addr_relative_count' => '亲属联系人与通讯录匹配度',
            'com_r_total_mavg' => '亲属联系人次数月均',
            'com_c_total_mavg' => '社会联系人次数月均',
            'com_r_rank' => '亲属联系人通话次数排名',
            'com_c_total' => '常用联系人通话次数',
            'com_r_total' => '亲属联系人通话次数',
            'addr_count' => '通讯录去重后个数',
            'report_use_time' => '运营商手机号注册时长',
            'report_loan_connect' => '贷款号码联系情况',
            'report_110' => '出现与110电话通话记录',
            'report_120' => '出现与120电话通话记录',
            'report_lawyer' => '多次出现与律师电话通话记录',
            'report_aomen' => '澳门通话记录',
            'report_court' => '法院通话记录',
            'report_fcblack' => '借款人出现在聚信立金融黑名单',
            'report_shutdown' => '手机号静默时间',
            'com_hours_connect' => '时段: 总通话时段（过去时期=90天）',
            'com_valid_all' => '通话次数>=15次',
            'com_valid_mobile' => '有效联系人个数',
            'vs_phone_match' => '运营商手机与通讯录匹配度',
            'vs_valid_match' => '有效手机号与通讯录匹配数',
            'addr_has_black' => '通讯录中有黑名单',
            'is_amount_up' => 'Is Amount Up',
            'is_white_true' => 'Is White True',
            'is_bank_edit' => 'Is Bank Edit',
            'is_info_edit' => 'Is Info Edit',
            'is_report_edit' => 'Is Report Edit',
            'report_night_percent' => '运营商夜间通话占比',
            'addr_collection_count' => '通讯录中含催收字段联系人个数',
            'query_time' => '注册决策1请求时间',
            'loan_create_time' => '用户借款时间',
            'one_more_loan_value' => '当天申请借款次数',
            'seven_more_loan_value' => '其他申请借款次数(七天内)',
            'last_step' => '最终触发决策步骤：默认为1，1为最终只触发第一步决策，N为触发第N步决策',
            'success_num' => '历史借款成功个数',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
            'request_id_two' => '借款决策2请求ID',
            'one_number_account_value' => 'One Number Account Value',
            'prd_type' => '产品类型，1 一亿元；8 7-14天',
        ]; 
    } 

    public function addLoanInfo($postData)
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

    public function getLoanInfo($postData)
    {
        return $this->find()->where($postData)->one();
    }

    public function updateLoanInfo($postData)
    {   
        foreach ($postData as $k => $val) {
            $this->$k = $val;
        }
        $this->modify_time = date('Y-m-d H:i:s');
        $this->last_step = 2;
        return $this->save();
    }
}
