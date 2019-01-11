<?php

namespace app\models;
use app\common\Http;

class Sms extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%sms}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'receive_mobile', 'content'], 'required'],
            [['aid', 'sms_type'], 'integer'],
            [['create_time'], 'safe'],
            [['msgid'], 'string', 'max' => 50],
            [['code'], 'string', 'max' => 10],
            [['receive_mobile'], 'string', 'max' => 16],
            [['content'], 'string', 'max' => 256],
            [['channel_type'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'aid' => 'app号',
            'msgid' => '短信消息ID号',
            'code' => '短信码',
            'receive_mobile' => '接收手机号码',
            'content' => '发送内容',
            'channel_type' => '通道类型',
            'sms_type' => '短信类型:1 一亿元;2 先花花;3 商户贷', //1001 开放平台支付使用
            'create_time' => '创建时间:1手机验证；',
        ];
    }

    /**
     * 添加一条新的短信信息
     * 使用时每次new 对象
     * @param $data
     * @return bool
     */
    public function addSms($data) {
        // 组合参数
        $attr = [
            'aid' => $data['aid'],
            'msgid' => $data['msgid'],
            'code' => $data['code'],
            'receive_mobile' => $data['receive_mobile'],
            'content' => $data['content'],
            'channel_type' => $data['channel_type'],
            'sms_type' => $data['sms_type'],
            'create_time' => date('Y-m-d H:i:s'),
        ];

        // 错误检测
        $error = $this->chkAttributes($attr);
        if ($error) {
            return null;
        }

        // 保存信息
        return $this->save();
    }
    /**
     * 发送验证码
     * @param  str $phone
     * @param  str $content
     * @param  str $sign         签名
     * @param  str $channel
     * @return  bool
     */
    public function sendCodeBySign($phone, $content, $sign, $channel) {
        $channel = strtolower($channel);
        switch ($channel) {
        case 'yunxin':
            $result = Http::sendByMobile($phone, $content, $sign);
            break;
        case 'chuanglan':
            $content = $sign . $content;
            $result = Http::sendSmsByChuanglan($phone, $content, 2);
            break;
        default:
            # code...
            $result = false;
            break;
        }
        return $result ? true : false;
    }
    /**
     * 上限校验, 分钟, 小时, 天
     * @param  []  $phone    []
     * @param  int  $sms_type 1001:支付 .....
     * @return boolean          
     */
    public function isSmsLimit($phone, $sms_type) {
        //1. 分钟校验
        $where = [
            'phone' => $phone,
            'sms_type' => $sms_type,
            'create_time' => date('Y-m-d', strtotime('-5 minute')),
        ];
        $total = static::find()->where($where)->count();
        if($total > 10){
            return true;
        }

        //2. 小时校验
        $where['create_time'] = date('Y-m-d', strtotime('-1 hour'));
        $total = static::find()->where($where)->count();
        if( $total > 20 ){
            return true;
        }

        //3. 天校验. 不能大于50次
        $where['create_time'] = date('Y-m-d', strtotime('-1 day'));
        $total = static::find()->where($where)->count();
        if( $total > 50 ){
            return true;
        }

        return false;
    }
}
