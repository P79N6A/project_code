<?php

namespace app\models\dev;

use Yii;

class User_amount_list extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_amount_list';
    }

    public function rules() {
        return [
        ];
    }

    static public function CreateAmount($array_amount) {
        $amount = new User_amount_list();
        $amount->type = $array_amount['type'];
        $amount->user_id = $array_amount['user_id'];
        $amount->amount = $array_amount['amount'];
        if (isset($array_amount['operation']) && $array_amount['operation'] == 0) {
            $amount->operation = $array_amount['operation'];
        }
        $amount->create_time = date('Y-m-d H:i:s', time());
        $amount->save();
    }
    public function getListByType($user_id,$type){
        $result = User_amount_list::find()->where(['user_id'=>$user_id,'operation'=>1,'type'=>$type])->all();
        return $result;
    }

        /**
     * 获取用户某一类型得到额度的总和
     * @param type $user_id
     * @param type $type
     */
    static public function getSumByType($user_id,$type=0){
        $sum = User_amount_list::find()->where(['user_id'=>$user_id,'operation'=>1]);        
        $d_sum = User_amount_list::find()->where(['user_id'=>$user_id,'operation'=>0]);
        if($type!=0){
            $sum->andWhere(['type'=>$type]);
            $d_sum->andWhere(['type'=>$type]);
        }
        $num = $sum->sum('amount');
        $d_num = $d_sum->sum('amount');
        return $num-$d_num;
    }

}
