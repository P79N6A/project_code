<?php

namespace app\models;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "xhh_mf_risk".
 *
 * @property integer $id
 * @property string $event_type
 * @property string $request_id
 * @property string $token_id
 * @property string $black_box
 * @property string $resp_detail_type
 * @property string $event_occur_time
 * @property string $account_login
 * @property string $account_mobile
 * @property string $account_email
 * @property string $id_number
 * @property string $account_password
 * @property string $rem_code
 * @property string $ip_address
 * @property string $state
 * @property string $refer_cust
 * @property integer $success
 * @property string $reason_code
 * @property string $seq_id
 * @property integer $spend_time
 * @property string $final_decision
 * @property integer $final_score
 * @property string $policy_set_name
 * @property string $url
 * @property integer $version
 * @property string $create_time
 */
class MfRisk extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_mf_risk';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['event_occur_time', 'create_time'], 'safe'],
            [['success', 'spend_time', 'final_score', 'version'], 'integer'],
            [['create_time'], 'required'],
            [['event_type', 'final_decision'], 'string', 'max' => 30],
            [['request_id', 'resp_detail_type'], 'string', 'max' => 50],
            [['token_id', 'account_email', 'account_password', 'policy_set_name'], 'string', 'max' => 64],
            [['black_box', 'refer_cust'], 'string', 'max' => 128],
            [['account_login', 'account_mobile'], 'string', 'max' => 20],
            [['id_number', 'ip_address'], 'string', 'max' => 32],
            [['rem_code', 'state'], 'string', 'max' => 10],
            [['seq_id', 'url', 'reason_code'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'event_type' => Yii::t('app', 'Event Type'),
            'request_id' => Yii::t('app', 'Request ID'),
            'token_id' => Yii::t('app', 'Token ID'),
            'black_box' => Yii::t('app', 'Black Box'),
            'resp_detail_type' => Yii::t('app', 'Resp Detail Type'),
            'event_occur_time' => Yii::t('app', 'Event Occur Time'),
            'account_login' => Yii::t('app', 'Account Login'),
            'account_mobile' => Yii::t('app', 'Account Mobile'),
            'account_email' => Yii::t('app', 'Account Email'),
            'id_number' => Yii::t('app', 'Id Number'),
            'account_password' => Yii::t('app', 'Account Password'),
            'rem_code' => Yii::t('app', 'Rem Code'),
            'ip_address' => Yii::t('app', 'Ip Address'),
            'state' => Yii::t('app', 'State'),
            'refer_cust' => Yii::t('app', 'Refer Cust'),
            'success' => Yii::t('app', 'Success'),
            'reason_code' => Yii::t('app', 'Reason Code'),
            'seq_id' => Yii::t('app', 'Seq ID'),
            'spend_time' => Yii::t('app', 'Spend Time'),
            'final_decision' => Yii::t('app', 'Final Decision'),
            'final_score' => Yii::t('app', 'Final Score'),
            'policy_set_name' => Yii::t('app', 'Policy Set Name'),
            'url' => Yii::t('app', 'Url'),
            'version' => Yii::t('app', 'Version'),
            'create_time' => Yii::t('app', 'Create Time'),
        ];
    }

    /**
     * 乐观锁
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 添加一条同盾接口返回信息
     * @param $condition
     * @return bool|false|null
     */
    public function addRisk($condition) {
        if (empty($condition)) {
            return false;
        }
        $save_data = [
            'event_type'                => ArrayHelper::getValue($condition, 'event_type', ''), //事件类型',
            'request_id'                => ArrayHelper::getValue($condition, 'request_id', ''), //请求ID',
            'token_id'                  => ArrayHelper::getValue($condition, 'token_id', ''), //JS方式对接，用于关联设备指纹',
            'black_box'                 => ArrayHelper::getValue($condition, 'black_box', ''), //sdk方式对接，用于关联设备指纹',
            'resp_detail_type'          => ArrayHelper::getValue($condition, 'resp_detail_type', ''), //可支持API实时返回设备或解析信息',
            'event_occur_time'          => ArrayHelper::getValue($condition, 'event_occur_time', ''), //事件时间',
            'account_login'             => ArrayHelper::getValue($condition, 'account_login', ''), //注册账户(如昵称等默认账户名)',
            'account_mobile'            => ArrayHelper::getValue($condition, 'account_mobile', ''), //注册手机',
            'account_email'             => ArrayHelper::getValue($condition, 'account_email', ''), //注册邮箱',
            'id_number'                 => ArrayHelper::getValue($condition, 'id_number', ''), //注册身份证',
            'account_password'          => ArrayHelper::getValue($condition, 'account_password', ''), //注册密码摘要：建议先哈希加密后再提供（保证相同密码Hash值一致即可）',
            'rem_code'                  => ArrayHelper::getValue($condition, 'rem_code', ''), //注册邀请码',
            'ip_address'                => ArrayHelper::getValue($condition, 'ip_address', ''), //注册IP地址',
            'state'                     => ArrayHelper::getValue($condition, 'state', -1), //状态校验结果（密码校验结果：0表示账户及密码一致性校验成功，1表示账户及密码一致性校验失败）',
            'refer_cust'                => ArrayHelper::getValue($condition, 'refer_cust', ''), //网页端请求来源，即用户HTTP请求的refer值（JS方式对接）',
            'success'                   => (int)ArrayHelper::getValue($condition, 'success', 0), //提交是否成功',
            'reason_code'               => ArrayHelper::getValue($condition, 'reason_code', ''), //错误代码',
            'seq_id'                    => ArrayHelper::getValue($condition, 'seq_id', ''), //本次调用的请求id，用于事后反查事件',
            'spend_time'                => ArrayHelper::getValue($condition, 'spend_time', 0), //本次调用在服务端的执行时间',
            'final_decision'            => ArrayHelper::getValue($condition, 'final_decision', ''), //风险评估结果（Accept无风险，通过；Review低风险，审查；Reject高风险，拒绝）',
            'final_score'               => ArrayHelper::getValue($condition, 'final_score', 0), //风险系数',
            'policy_set_name'           => ArrayHelper::getValue($condition, 'policy_set_name', ''), //策略集名称',
            'url'                       => ArrayHelper::getValue($condition, 'url', ''), //日志URL',
            'version'                   => '1', //版本号',
            'create_time'               => date("Y-m-d H:i:s", time()), //保存时间',
        ];
        $errors = $this->chkAttributes($save_data);
        if ($errors){
            Logger::dayLog('mfrisk/save', '保存数据错误', json_encode($errors));
            return $this->returnError(null, implode('|', $errors));
        }
        return $this->save();
    }
}