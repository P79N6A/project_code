<?php
/**
 *
 * 融360运营商定时跑文件
 * status  1:初始   2：锁定   3：推送成功   4：推送失败   5:空文件   6:文件不存在  10：其他
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/7
 * Time: 14:55
 * d:\xampp\php\php.exe d:\www\yiyiyuan\yii rongoperator
 */
namespace app\commands;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\models\news\RongOperator;
use Yii;
use yii\console\Controller;
set_time_limit(0);
ini_set('memory_limit', '-1');

class RongoperatorController extends Controller
{
    public function actionIndex()
    {
        $limit = 500;
        $whereconfig = [
            'AND',
            ['source'=>8],
            ['status' => 1]

        ];
        $sql = RongOperator::find()->where($whereconfig)->orderBy("create_time ASC");
        $operator_data = $sql->limit($limit)->asArray()->all();
        if (!empty($operator_data)){
            $operator_id_data = Common::ArrayToString($operator_data, 'id');
            RongOperator::updateAll(['status' => 2], ['status' => 1, 'id' => explode(',', $operator_id_data)]);
            foreach($operator_data as $key => $value){
                $start_time = $this->microtime_float();
                $this->logicalProcessing($value);
                $end_time = $this->microtime_float();
                $all_time = intval($end_time) - intval($start_time);
                if (!empty($value['r_loan_id'])) {
                    Logger::errorLog(print_r(array($value['r_loan_id'] => $all_time), true), 'operator_send_time', 'r360');
                }
            }
        }
    }

    private function logicalProcessing($data)
    {
        if (empty($data['filename'])){
            return false;
        }
        $send_state = $this->readFileData($data['filename']);
        $update_rong_operator = RongOperator::find()->where(['r_loan_id'=>$data['r_loan_id'], 'source'=>8])->one();
        if ($send_state == 200){
            $ret_stat = $update_rong_operator->updateRongOperator(3);
            if ($ret_stat && file_exists($data['filename'])){
                //返回成功时删除文件
                unlink($data['filename']);
                Logger::errorLog(print_r(array($data['filename']), true), 'operator_del_file_name', 'r360');
            }
        }else{
            $data_error = [
                '400' => 4, //推送失败
                '401' => 5, //空文件
                '402' => 6, //文件不存在
            ];
            $error_data = empty($data_error[$send_state]) ? 10 : $data_error[$send_state];
            $update_rong_operator->updateRongOperator($error_data);
        }
    }

    /**
     * 读取文件
     * @param $file_name
     * @return bool
     */
    private function readFileData($file_name)
    {
        if (empty($file_name) || !file_exists($file_name))return 402; //文件不存在
        $file_data = file_get_contents($file_name);
        if (empty($file_data)) return 401; //空文件
        //将数据发送到开放平台
        $send_state = $this->sendMoble(json_decode($file_data,true));
        if ($send_state){
            return 200; //成功
        }
        return 400; //推送失败
    }

    /**
     * 运营商数据推送
     * @param array $mobile
     * @return array
     */
    private function sendMoble(array $mobile)
    {
        $mobile_data = [
            'data' => $mobile,
        ];
        //Logger::errorLog(print_r(array($mobile_data), true), 'mobile_data', 'r360');
        $result_jxl = (new Apihttp())->postRongJuxinli($mobile_data);
        Logger::errorLog(print_r(array($result_jxl), true), 'mobile_data_state', 'r360');
        if (!empty($result_jxl) && $result_jxl['res_code'] == '65004'){
            return true;
        }
        return false;
    }

    /**
     * 返回时间戳
     * @return float
     */
    private function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }
}