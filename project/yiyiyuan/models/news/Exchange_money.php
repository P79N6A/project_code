<?php

namespace app\models\news;

use app\models\BaseModel;
use app\commonapi\Logger;
use Yii;

/**
 * This is the model class for table "yi_exchange_money".
 *
 * @property string $id
 * @property string $amdin_id
 * @property integer $channel
 * @property string $money
 * @property string $cur_date
 * @property string $last_modify_time
 * @property string $create_time
 */
class Exchange_money extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_exchange_money';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['admin_id', 'channel', 'money', 'cur_date', 'last_modify_time', 'create_time'], 'required'],
            [['admin_id', 'channel', 'type'], 'integer'],
            [['money'], 'number'],
            [['cur_date', 'last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'admin_id' => 'Admin ID',
            'channel' => 'Channel',
            'money' => 'Money',
            'cur_date' => 'Cur Date',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function addMoneyLimit($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateMoneyLimit($condition, $userinfo) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }

        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        //记录变更日志
        $this->saveUpLog($condition, $userinfo);

        return $this->save();
    }

    private function saveUpLog($condition, $userinfo) {
        $old = self::findOne($this->id);
        if ($old->money != $condition['money']) {
            Logger::dayLog('UpMoneyLimit', 'UpMoney', $old->money . ' to ' . $condition['money'], '管理员ID：', $userinfo->id);
        }
    }

    /**
     * 查询某个通道当天的最大出款额
     * @param type $channel_id
     * @param type $type 代表资方
     * @return boolean
     */
    public function todayMaxMoney($channel_id, $type = 1) {
        if (empty($channel_id)) {
            return false;
        }

        $cur_date = date('Y-m-d 00:00:00');
        $where = [
            'and',
            ['channel' => $channel_id],
            ['cur_date' => $cur_date],
            ['type' => $type],
        ];
        $money = self::find()->select('money')->where($where)->one();

        return empty($money) ? 0 : $money->money;
    }

    /**
     * 查询某个通道当天某时间段内的最大出款额
     * @param type $channel_id
     * @param type $type 代表资方
     * @return boolean
     */
    public function todayTimeMaxMoney($channel_id, $type = 1) {
        if (!isset($channel_id)) {
            return false;
        }
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $todaymax = self::find()->where(['between', 'cur_date', $today_start, $today_end])->andFilterWhere(['channel' => $channel_id, 'type' => $type])->one();
        if (empty($todaymax)) {
            return 0;
        }
        $cur_date = date('Y-m-d H:i:s');
        if ($cur_date > $todaymax->cur_date) {
            return $todaymax->money;
        }
        return 0;
    }

}
