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
class Banner extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_banner';
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
     * 
      $condition = array(
      'status'=>$status,
      'type'=>$type,
      );
     * @param type $condition
     * @return type
     */
    public function getBanner($condition) {
        if (!empty($condition)) {
            $bankNotice = $this->find()->where($condition)->orderBy('last_modify_time desc')->all();
        } else {
            $bankNotice = $this->find()->orderBy('last_modify_time desc')->all();
        }
        return $bankNotice;
    }

    public static function addBanner($condition) {
        if (!isset($condition['admin_id']) || !isset($condition['type']) || !isset($condition['url']) || !isset($condition['status'])) {
            return false;
        }
        $bannerModel = new Banner();
        $now_time = date('Y-m-d H:i:s');
        foreach ($condition as $key => $val) {
            $bannerModel->{$key} = $val;
        }
        $bannerModel->create_time = $now_time;
        $bannerModel->last_modify_time = $now_time;
        $bannerModel->version = 1;
        $result = $bannerModel->save();
        return $result;
    }

    public function updateBanner($condition) {
        if (empty($condition)) {
            return false;
        }
        $now_time = date('Y-m-d H:i:s');
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = $now_time;
        $this->version = $this->version + 1;
        $result = $this->save();
        return $result;
    }

}
