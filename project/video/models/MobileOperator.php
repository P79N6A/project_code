<?php
/**
 * 手机号运营商信息
 */
namespace app\models;

use Yii;

class MobileOperator extends \app\models\BaseModel
{
    public static function tableName(){
        return 'mobile_operator';
    }

    /**
     * 保存请求
     * @param $postData
     * @return bool|string
     */
    public function saveMobileInfo($postData) {
        // 检测数据
        if (!$postData) {
            return $this->returnError(false, '数据不能为空');
        }
        $time = date('Y-m-d H:i;s',time());
        $this->mobile = isset($postData['mobile']) ? $postData['mobile'] : '';
        $this->location = isset($postData['location']) ? $postData['location'] : '';
        $this->operator = isset($postData['operator']) ? $postData['operator'] : '';
        $this->create_time = $time;
        $this->last_modify_time = $time;
        $result = $this->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    /**
     * 根据手机号获取运营商信息
     * @param $mobile
     * @return array|bool
     */
    public function getInfoByMobile($mobile){
        $data = static::find()->where(['mobile' => $mobile])
            ->orderBy('create_time DESC')->limit(1)->one();
        if($data){
            return $data;
        }
        return false;
    }
}