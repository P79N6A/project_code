<?php

namespace app\models\dev;

use Yii;

class User_extend extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_extend';
    }

    public static function getUserExtend($user_id) {
        $user_extend = User_extend::find()->where(['user_id' => $user_id])->one();
        return $user_extend;
    }

    public function getEdu() {
        switch ($this->edu) {
            case 1:
                $ext_diploma = '博士';
                break;
            case 2:
                $ext_diploma = '硕士';
                break;
            case 3:
                $ext_diploma = '本科';
                break;
            default:
                $ext_diploma = '专科';
        }
        return $ext_diploma;
    }

    public function addRecord($condition) {
        if (empty($condition) || !isset($condition['user_id'])) {
            return false;
        }
        $user_extend = User_extend::getUserExtend($condition['user_id']);
        if (!empty($user_extend)) {
            $result = $user_extend->updateRecord($condition);
        } else {
            $extendModel = new User_extend();
            foreach ($condition as $key => $val) {
                $extendModel->{$key} = $val;
            }
            $extendModel->version = 1;
            $extendModel->is_new = 1;
            $extendModel->last_modify_time = date('Y-m-d H:i:s');
            $extendModel->create_time = date('Y-m-d H:i:s');
            $result = $extendModel->save();
        }
        return $result;
    }

    public function updateRecord($condition) {
        if (empty($condition) || !isset($condition['user_id'])) {
            return false;
        }
        $user = User::findOne($condition['user_id']);
        $user_extend = User_extend::getUserExtend($user->user_id);
        if ((isset($condition['edu']) || isset($condition['marriage'])) && !empty($user_extend->home_address)) {
            $data_type = 3;
            $h_condition = [
                'user_id' => $user->user_id,
                'user_type' => $user->user_type,
                'data_type' => $data_type,
                'industry_edu' => $user_extend->edu,
                'marriage' => $user_extend->marriage,
                'area' => $user_extend->home_area,
                'address' => $user_extend->home_address,
            ];
            $history_id = User_history_info::addHistoryInfo($user, $h_condition);
        } else if (isset($condition['email']) && !empty($user_extend->company)) {
            $data_type = 2;
            $h_condition = [
                'user_id' => $user->user_id,
                'user_type' => $user->user_type,
                'data_type' => $data_type,
                'company_school' => $user_extend->company,
                'industry_edu' => $user_extend->industry,
                'position_schooltime' => $user_extend->position,
                'telephone' => $user_extend->telephone,
                'area' => $user_extend->company_area,
                'address' => $user_extend->company_address,
                'profession' => $user_extend->profession,
                'email' => $user_extend->email,
                'income' => $user_extend->income,
            ];
            $history_id = User_history_info::addHistoryInfo($user, $h_condition);
        }

        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->version = $this->version + 1;
        $this->is_new = 1;
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

//     /**
//      * @inheritdoc
//      */
//     public function rules()
//     {
//         return [
//         ];
//     }
//     /**
//      * @inheritdoc
//      */
//     public function attributeLabels()
//     {
//         return [
//             'id' => 'ID',
//         ];
//     }
}
