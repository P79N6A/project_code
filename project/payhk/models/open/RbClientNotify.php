<?php

namespace app\models\open;

use Yii;

/**
 * This is the model class for table "cj_client_notify".
 *
 * @property integer $id
 * @property integer $remit_id
 * @property string $tip
 * @property integer $remit_status
 * @property integer $notify_num
 * @property integer $notify_status
 * @property string $notify_time
 * @property string $reason
 * @property string $create_time
 */
class RbClientNotify extends \app\models\open\OpenBase
{
    // 通知状态
    const STATUS_INIT = 0; // 初始
    const STATUS_DOING = 1; // 通知中
    const STATUS_SUCCESS = 2; // 成功
    const STATUS_RETRY = 3; // 重试
    const STATUS_FAILURE = 11; // 通知失败
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rb_client_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remit_id', 'tip', 'notify_time', 'create_time'], 'required'],
            [['remit_id', 'remit_status', 'notify_num', 'notify_status'], 'integer'],
            [['notify_time', 'create_time'], 'safe'],
            [['tip'], 'string', 'max' => 255],
            [['reason'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'remit_id' => 'Remit ID',
            'tip' => 'Tip',
            'remit_status' => 'Remit Status',
            'notify_num' => 'Notify Num',
            'notify_status' => 'Notify Status',
            'notify_time' => 'Notify Time',
            'reason' => 'Reason',
            'create_time' => 'Create Time',
        ];
    }

    public static function getStatus(){
        return [
            self::STATUS_INIT => '初始',
            self::STATUS_DOING => '通知中',
            self::STATUS_SUCCESS => '成功',
            self::STATUS_RETRY => '重试',
            self::STATUS_FAILURE => '通知失败',
        ];
    }

    /**
     * 回写响应结果
     * $this 操作数据
     * @param $data
     * @return bool
     */
    public function saveNotifyStatus($data) {
        //更新通知次数以及通知的发送状态
        $this->notify_num = intval($data['notify_num']);
        $this->notify_status = intval($data['notify_status']);
        $this->notify_time = $data['notify_time'];
        $result = $this->save();
        return $result;
    }
}