<?php

namespace app\models\news;

use Yii;


class Manager_logs extends \yii\db\ActiveRecord {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_manager_logs';
    }


    /*
     * 添加manager_log
     * operation_type 9 :app还款方式修改
     * type :7：微信；8：支付宝；9：银行卡；10：线下
     * reason:修改的状态 0;关闭，1开启，2取消
     */
    public function updateManagerlogs($condition) {
        if (empty($condition)) {
            return null;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = date("Y-m-d H:i:s");
        $this->create_time = date("Y-m-d H:i:s");
        if ($this->save() > 0) {
            return $this;
        } else {
            return false;
        }
    }

    public function getRemit() {
        return $this->hasOne(User_remit_list::className(), ['id' => 'log_id']);
    }

    /**
     * 获取记录，根据operation_type及log_id
     * @param $operation_type
     * @param $log_id
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/7/11 17:07
     */
    public function getByOperationTypeAndLogid($operation_type, $log_id,$order = "") {
        if (empty($operation_type) || empty($log_id)) {
            return null;
        }
        $where = [
            'operation_type' => $operation_type,
            'log_id' => $log_id
        ];
        if($order == 1){
            return self::find()->where($where)->orderBy('create_time DESC')->one();
        }else{
            return self::find()->where($where)->one();
        }
    }
}