<?php

namespace app\models\news;

/**
 * 通用通知表
 *
 */
class CommonNotify extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_common_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['notify_id', 'notify_type', 'notify_time', 'create_time'], 'required'],
            [['notify_id', 'notify_type', 'notify_status', 'version'], 'integer'],
            [['notify_time', 'create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'notify_id' => '通知id',
            'notify_type' => '类型:1:出款后通知; 2:...',
            'notify_status' => '通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 11:通知失败',
            'notify_time' => '通知时间',
            'create_time' => '创建时间',
            'version' => '版本号',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 获取未处理的订单
     * @param  int $notify_type 类型
     * @param  int $limit           条数
     * @return []
     */
    public function getInitData($notify_type, $limit) {
        $where = [
            'AND',
            [
                'notify_status' => 0,
                'notify_type' => $notify_type,
            ],
            ['>', 'create_time', strtotime('-12 hour')],
        ];
        $remits = static::find()->where($where)->orderBy('id ASC')->limit($limit)->all();
        return $remits;
    }

    /**
     * 锁定正在出款接口的状态
     */
    public function lockNotifys($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['notify_status' => 1], ['id' => $ids]);
        return $ups;
    }

    /**
     * 保存为锁定: 锁定当前出款纪录
     * @return  bool
     */
    public function lock() {
        try {
            $this->notify_status = 1;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function saveSuccess() {
        try {
            $this->notify_status = 2;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存出款纪录
     * @param  int $notify_id
     * @param  int $notify_type
     *                     1: 借款出款通知
     *                     2: 扩展用
     * @return bool
     */
    public function saveNotify($notify_id, $notify_type) {
        $notify_id = intval($notify_id);
        $notify_type = intval($notify_type);
        $postData = [
            'notify_id' => $notify_id,
            'notify_type' => $notify_type,
            'notify_status' => 0,
            'notify_time' => '0000-00-00',
            'create_time' => date('Y-m-d H:i:s'),
            'version' => 0,
        ];
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 添加出款推送消息
     * @param obj $oRemit user_remit_list
     */
    public function addNotify($oRemit, $status) {
        if (in_array($status, ['DOREMIT', 'SUCCESS'])) {
            $result = $this->saveNotify($oRemit['loan_id'], 1);
            if (!$result) {
                Logger::dayLog('autoremit', 'addNotify', $this->errors);
                return false;
            }
        }
        return true;
    }

}
