<?php
/**
 *运营商数据请求表
 */
namespace app\models;

use Yii;

class BasicOperator extends \app\models\BaseModel
{
    // 请求状态
    const STATUS_INIT = 0; // 初始
    const STATUS_OK = 1; // 成功
    const STATUS_FAIL = 2; // 失败

    public static function tableName(){
        return 'operator_request';
    }

    /**
     * 获取最近请求失败的次数
     * @param $mobile
     * @param $from
     * @param $status
     * @return int
     */
    public function getCountsByMobile($mobile, $from, $status){
        //1 限制时间（最近四个月）
        $limitTime = 86400 * 120;
        $t = time() - $limitTime;
        $d = date('Y-m-d 00:00:00', $t);

        $count = static::find()
            ->where(['mobile' => $mobile])
            ->where(['from' => $from])
            ->andWhere(['status_detail' => $status])
            ->andWhere(['>=', 'create_time', $d])
            ->count();
        return $count;
    }

    /**
     * 保存请求
     * @param $postData
     * @return bool|string
     */
    public function saveRequest($postData) {
        // 检测数据
        if (!$postData) {
            return $this->returnError(false, '数据不能为空');
        }
        $time = date('Y-m-d H:i;s',time());
        $this->mobile = isset($postData['mobile']) ? $postData['mobile'] : '';
        $this->from = isset($postData['from']) ? $postData['from'] : '';
        $this->status_detail = isset($postData['status_detail']) ? $postData['status_detail'] : static::STATUS_INIT;
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
     * 更新请求的状态
     *
     * @param $request_id
     * @param array $upd_status
     * @return bool
     */
    public function updateDetailrequest($request_id, $upd_status=array()){
        if (!isset($request_id)||empty($request_id)) {
            return $this->returnError(false, '请求id不能为空');
        }
        $model = static::find()
            ->where(['id' => $request_id])
            ->one();
        if(isset($upd_status['status_detail'])){
            $model->status_detail = $upd_status['status_detail'];
        }
        if(isset($upd_status['status_report'])){
            $model->status_report = $upd_status['status_report'];
        }
        $model->last_modify_time = date('Y-m-d H:i:s',time());
        $result = $model->save();
        if(!$result){
            return $this->returnError(false, '数据更新失败');
        }
        return true;
    }

    /**
     * 根据订单编号获取最近的请求
     * @param $id
     * @return array|bool
     */
    public function getRequestById($id){
        $data = static::find()->where(['id' => $id])
            ->orderBy('create_time DESC')->limit(1)->one();
        if($data){
            return $data;
        }
        return false;
    }
}