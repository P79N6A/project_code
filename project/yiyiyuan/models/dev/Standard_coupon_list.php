<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Standard_coupon_list extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_standard_coupon_list';
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
    /**
     * 根据手机号查询收益券
     * @param type $mobile
     * @return type
     */
    public function getStandByMobile($mobile, $status = 1,$order = '') {
        if (empty($mobile)) {
            return null;
        }
        $now_time = date('Y-m-d H:i:s');
        if ($status == 4) {
            $stand = Standard_coupon_list::find()->where(['mobile' => $mobile]);
        }else{
            $stand = Standard_coupon_list::find()->where(['status' => $status, 'mobile' => $mobile]);
            if($status==1){
                $stand = $stand->andWhere("end_date > '$now_time'");
            }
        }        
        if(!empty($order)){
            $stand = $stand->orderBy($order);
        }
        $standlist = $stand->all();
//        print_r($standlist);
        return $standlist;
    }
    
    
    public function updateStandardCoupon($condition) {
        $now_time = date('Y-m-d H:i:s');
        foreach ($condition as $key=>$val){
            $this->{$key}=$val;
        }
        $result = $this->save();
        return $result;
    }

}
