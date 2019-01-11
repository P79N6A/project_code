<?php
/**
 *融360运营商
 */
namespace app\models;

use Yii;

class RongOperatorModel extends \app\models\BaseModel
{
    public static function tableName(){
        return 'rongoperator_request';
    }

    //根据订单编号获取最近的请求
    public function getRongrequestByNo($orderNo){
            $data = static::find()->where(['order_no' => $orderNo])
//                ->andWhere(['status'=>'SUCCESS'])
                ->orderBy('create_time DESC')->limit(1)->one();
            if($data){
                return [
                    'id' => $data['id'],
                ];
            }
            return false;
    }
    //保存请求
    public function saveRongrequest($postData) {
        // 检测数据
        if (!$postData) {
            return $this->returnError(false, '数据不能为空');
        }
        $time = date('Y-m-d H:i;s',time());
        $this->mobile = isset($postData['mobile']) ? $postData['mobile'] : '';
        $this->order_no = isset($postData['order_no']) ? $postData['order_no'] : '';
        $this->status = isset($postData['status']) ? $postData['status'] : 'INIT';
        $this->create_time = $time;
        $this->last_modify_time = $time;
        $result = $this->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }
    //更新请求的状态
    public function updateRongrequest($request_id, $upd_status=array()){
        if (!isset($request_id)||empty($request_id)) {
            return $this->returnError(false, '请求id不能为空');
        }
        $model = static::find()
            ->where(['id' => $request_id])
            ->one();
        if(isset($upd_status['status'])){
            $model->status = $upd_status['status'];
        }
        $model->last_modify_time = date('Y-m-d H:i:s',time());
        $result = $model->save();
        if(!$result){
            return $this->returnError(false, '数据更新失败');
        }
        return true;
    }
}