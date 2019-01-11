<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_sms_send".
 *
 * @property string $id
 * @property string $mobile
 * @property string $content
 * @property integer $sms_type
 * @property integer $status
 * @property integer $channel
 * @property string $send_time
 * @property string $create_time
 */
class SmsSend extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_sms_send';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['sms_type', 'status', 'channel'], 'integer'],
            [['send_time', 'create_time'], 'safe'],
            [['mobile'], 'string', 'max' => 12],
            [['content'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'content' => 'Content',
            'sms_type' => 'Sms Type',
            'status' => 'Status',
            'channel' => 'Channel',
            'send_time' => 'Send Time',
            'create_time' => 'Create Time',
        ];
    }

    public function addSmsSend($data) {
        $data['create_time'] = date("Y-m-d H:i:s");
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取未处理的订单
     * @param  int $notify_type 类型
     * @param  int $limit           条数
     * @return []
     */
    public function getInitData($limit) {
        $where = [
            'AND',
            [
                'status' => 0,
            ],
            ['>', 'create_time', date("Y-m-d H:i:s", strtotime('-12 hour'))],
        ];
        $remits = static::find()->where($where)->orderBy('create_time ASC')->limit($limit)->all();
        return $remits;
    }

    /**
     * 锁定正在发送的状态
     */
    public function lockNotifys($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['status' => 1], ['id' => $ids]);
        return $ups;
    }

    /**
     * 批量改为成功
     */
    public function successs($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['status' => 2, 'send_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        return $ups;
    }

    /**
     * 批量改为失败
     */
    public function fails($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['status' => 3], ['id' => $ids]);
        return $ups;
    }

    /**
     * 保存为锁定: 锁定当前发送纪录
     * @return  bool
     */
    public function lock() {
        try {
            $this->status = 1;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function saveSuccess() {
        try {
            $this->status = 2;
            $this->send_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function saveFail() {
        try {
            $this->status = 3;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 目前是审核通过，发送短信提示去微信公众号
     * @param $type
     * @param $cgRemit
     * @return bool
     */
    public function saveSendOutmoney($userLoan, $sms_type = 8, $sms_channel = 3) {
        $user = $userLoan->user;
        $content = $user->realname . '先生/女士，您的借款已经通过审核，请关注微信公众号“先花一亿元”进行提现，长时间不提现视为自动放弃！回T退订';

        $mobile = $userLoan->user->mobile;
        $addData['mobile'] = $mobile;
        $addData['content'] = $content;
        $addData['sms_type'] = $sms_type;
        $addData['status'] = 0;
        $addData['channel'] = $sms_channel;
        $addData['send_time'] = date('Y-m-d H:i:s');
        $sms_model = new SmsSend();
        $res = $sms_model->addSmsSend($addData);
        return $res;
    }

}
