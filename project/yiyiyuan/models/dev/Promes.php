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
class Promes extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_promes';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }
    /**
     * 批量添加 
     * @param [type] $userData [description]
     */
    public static function addBatchByUsers($userData){
        if(empty($userData)){
            return 0;
        }
        $time = date('Y-m-d H:i:s');
        $saves = [];
        foreach ($userData as $data) {
            $saves[] = [
                'user_id' => $data['user_id'],
                'loan_id' => $data['loan_id'],
                'type' => $data['type'],
                'prome_status' => 1, 
                'prome_score' =>0.00,
                'modify_time' => $time,
                'create_time' =>  $time,
            ];
        }

        return static::insertBatch($saves);
    }
    /**
     * 获取需要处理的正常数据
     * @param date $result_time 当前时间
     * @return bool
     */
    public function getNormal($result_time){
        $where = [
            'AND', 
            ['prome_status' => 3],
            ['type' => [1,2]], // 正常借款
            ['<', 'create_time', $result_time], 
            ['>', 'create_time', date('Y-m-d H:i:s', strtotime($result_time) - 3600*3) ], // 1小时内
        ];
        $data = static::find() -> where($where) -> orderBy('create_time ASC') -> limit(1000) -> all();
        return $data;
    }
    

}
