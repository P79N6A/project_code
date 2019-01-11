<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_umeng_send".
 *
 * @property string $id
 * @property string $device_token
 * @property string $user_id
 * @property string $loan_id
 * @property integer $device_type
 * @property string $content
 * @property integer $send_type
 * @property integer $status
 * @property integer $channel
 * @property string $send_time
 * @property string $create_time
 * @property integer $version
 */
class UmengSend extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_umeng_send';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['device_token', 'user_id', 'content','title', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'device_type', 'send_type', 'status', 'channel', 'version'], 'integer'],
            [['send_time', 'create_time'], 'safe'],
            [['device_token'], 'string', 'max' => 64],
            [['content'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'device_token' => 'Device Token',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'device_type' => 'Device Type',
            'title' => 'Title',
            'content' => 'Content',
            'send_type' => 'Send Type',
            'status' => 'Status',
            'channel' => 'Channel',
            'send_time' => 'Send Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * 获取未处理的订单
     * @param $limit 条数
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getInitData($limit) {
        $where = [
            'AND',
            [
                'status' => 0,
            ],
            ['>', 'create_time', date("Y-m-d H:i:s", strtotime('-12 hour'))],
            ['not', ['device_token' => null]],
            ['not', ['device_token' => '']],
        ];
        $datas = static::find()->where($where)->orderBy('create_time ASC')->limit($limit)->all();
        return $datas;
    }

    /**
     * 批量锁定
     * @param $ids
     * @return int
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
     * @param $ids
     * @return int
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
     * @param $ids
     * @return int
     */
    public function fails($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['status' => 3], ['id' => $ids]);
        return $ups;
    }

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
     * 添加提醒用户体现通知
     * @param $loan
     * @return bool
     */
    public function saveUmengSend($loan,$send_type,$times = '',$time_type = ''){

        if(empty($loan) || empty($send_type)){
            return false;
        }
        if($send_type == 1){
            $title = '审核通过了，快来！';
            $content = '您的借款审核已经通过，请尽快登录账户进行处理。';
        }elseif($send_type == 2){
            if($time_type == 1){
                $title = '借款审核通过了，请快快处理。';
                $content = '您的借款审核已通过'.$times.'小时，你还在等什么？';
            }elseif ($time_type == 2){
                $title = '审核通过'.$times.'小时了，还不抓紧？';
                $content = '您的借款审核通过已经'.$times.'个小时了，速速处理吧！';
            }else{
                $title = '借款即将失效，请尽快处理！';
                $content = '您的借款资格即将失效，请抓紧处理！';
            }
        }elseif($send_type == 3){
            $title = '借款已到账，请尽快处理！';
            $content = '您的借款已经在您的账户中蓄势待发，快来提走！';
        }elseif($send_type == 4){
            $title = '借款已到账，尽快提现！';
            $content = '银行已经把钱打到您的账户'.$times.'个小时了，是时候该提现了！';
        }
        $umengModel = new UmengSend();
        $res = $umengModel->saveMsg($loan,$send_type,$title,$content);
        return $res;
    }

    /**
     * 保存提示信息
     * @param $loan
     * @param $title
     * @param $content
     * @param $send_type
     * @return bool
     */
    public function saveMsg($loan,$send_type,$title,$content) {
        if(empty($loan) || empty($send_type) || empty($title) || empty($content)){
            return false;
        }
        if(!in_array($loan->source,[2,4])){
            return false;
        }
        $password = (new User_password)->getUserPassword($loan->user_id);
        if(empty($password->device_tokens)){
            return false;
        }
        $come_from = ['android'=>2, 'Android'=>2,'IOS'=>1, 'ios'=>1];
        $data = [
            'device_token' => $password->device_tokens,
            'user_id'      => $loan->user_id,
            'loan_id'      => $loan->loan_id,
            'device_type'  => empty($come_from[$password->device_type]) ? 2: $come_from[$password->device_type],
            'title'        => $title,
            'content'      => $content,
            'send_type'    => $send_type,
            'create_time'  => date('Y-m-d H:i:s')
        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
