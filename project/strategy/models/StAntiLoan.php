<?php

namespace app\models;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "st_anti_loan".
 *
 * @property string $id
 * @property string $request_id
 * @property string $loan_id
 * @property string $user_id
 * @property integer $addr_contacts_count
 * @property integer $addr_relative_count
 * @property string $com_r_total_mavg
 * @property string $com_c_total_mavg
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
 * @property string $report_night_percent
 * @property integer $addr_collection_count
 * @property string $loan_create_time
 * @property string $create_time
 * @property string $modify_time
 * @property integer $aid
 */
class StAntiLoan extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_anti_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'loan_id', 'user_id', 'addr_contacts_count', 'addr_relative_count', 'com_r_rank', 'com_c_total', 'com_r_total', 'addr_count', 'report_use_time', 'report_loan_connect', 'report_110', 'report_120', 'report_lawyer', 'report_aomen', 'report_court', 'report_fcblack', 'report_shutdown', 'com_hours_connect', 'com_valid_all', 'com_valid_mobile', 'vs_phone_match', 'vs_valid_match', 'addr_has_black', 'addr_collection_count', 'aid'], 'integer'],
            [['user_id', 'create_time', 'modify_time'], 'required'],
            [['com_r_total_mavg', 'com_c_total_mavg', 'report_night_percent'], 'number'],
            [['loan_create_time', 'create_time', 'modify_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'request_id' => 'Request ID',
            'loan_id' => '业务端借款ID',
            'user_id' => '业务端用户ID',
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
            'report_night_percent' => '运营商夜间通话占比',
            'addr_collection_count' => '通讯录中含催收字段联系人个数',
            'loan_create_time' => '用户借款时间',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
            'aid' => '产品类型，1 一亿元；8 7-14天',
        ];
    }
    //入库
    public function saveAnti($postData)
    {
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $postData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            Logger::dayLog('antiloan','用户记录失败', $error,$postData);
            return $this->returnError(false, $error);
        }
        return $this->save();
    }
}
