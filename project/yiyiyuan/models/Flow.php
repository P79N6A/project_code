<?php

namespace app\models;

use Yii;

class Flow extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_loan_flows';
    }

    public function rules() {
        return [
        ];
    }

    static public function CreateFlow($loan, $type = 1) {
        //type 0 用户  -1 系统
        $flow = new Flow();
        $flow->loan_id = $loan->loan_id;
        if ($type == 1) {
            $userinfo = Yii::$app->session->get("user");
            $flow->admin_id = $userinfo->id;
            $flow->admin_name = $userinfo->realname;
        } else {
            $flow->admin_id = $type;
        }
        $flow->loan_status = $loan->status;
        $flow->create_time = date('Y-m-d H:i:s', time());
        $flow->save();
    }

}
