<?php
/**
 * 定时获取融360运营商报告
 * D:\software\amp\php\php.exe D:\workspace\open\yii rong-timer index
 */
namespace app\commands;

use app\models\RongOperatorModel;
use yii\helpers\BaseArrayHelper;

class RongTimerController extends BaseController
{
    public function actionIndex()
    {
        $limit = 50;
        $where_config = [
            'status'=>['INIT'],
        ];

        //获取最近一小时推送失败的报告
        $queryTime = date('Y-m-d H:i:s', time() - 3600);
        $rongOperatorTimer_sql = RongOperatorModel::find()->where($where_config)->andWhere(['>=', 'create_time', $queryTime])->orderBy('id DESC');
        $total = $rongOperatorTimer_sql->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $rongOperator_info = $rongOperatorTimer_sql->limit($limit)->all();
            $order_no_ids = BaseArrayHelper::getColumn($rongOperator_info, 'order_no');
            RongOperatorModel::updateAll(['status' => 'LOCK'], ['mobile' =>$order_no_ids]);
            if (!empty($rongOperator_info)){
                foreach($rongOperator_info as $key=>$value){
                    //重新拉取运营商报告
                    $ret = $this->saveRongReport($value);
                    if($ret == 'success'){
                        $value->status = "DOING";
                    }else{
                        $value->status = "FAIL";
                    }
                    $value->last_modify_time = date("Y-m-d H:i:s", time());
                    $value->save();
                }
            }
            RongOperatorModel::updateAll(['status' => 'SUCCESS'], ['status' => 'DOING']);
        }
        RongOperatorModel::updateAll(['status' => 'INIT'], ['status' => 'FAIL']);
    }
    //拉取融360运营商数据
    private function saveRongReport($order_info){
        if(!$order_info){
            return 'order_no_error';
        }
        //重新拉取运营商数据
        $data['order_no'] = $order_info['order_no'];
        $data_order['biz_data'] = json_encode($data);
        $data_order['ptype'] = 'open_rong';
        $url = "weixin.xianhuahua.com/foreign/pushmobilereport";
//        Logger::dayLog('rong', '重推运营商报告传参：'.json_encode($data_order));
        $result_status = $this->curl(json_encode($data_order),$url);
//        Logger::dayLog('rong', '重推运营商报告返回结果：'.$result_status);
        return "success";
    }

    private function curl($postData,$url){
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,$postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($postData))
        );
        $result = curl_exec($ch);
        return $result;
    }
}