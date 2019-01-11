<?php
/**
 * 小诺回调
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/9/27
 * Time: 16:13
 */
namespace app\modules\newdev\controllers;

use app\commonapi\Logger;
use app\common\ApiClientCrypt;
use app\models\news\User_remit_list;
use app\models\news\YiLoanNotify;
use Yii;
use yii\web\Controller;

class XiaonuonotifyController extends NewdevController
{

    public $enableCsrfValidation = false;
    public function behaviors() {
        return [];
    }

    //出款异步通知地址
    public function actionXiaonuo()
    {
        $openApi = new ApiClientCrypt;
        if (isset($_GET['res_data'])) {
            $data = Yii::$app->request->get('res_data');
        } else {
            $data = Yii::$app->request->post('res_data');
        }
        //Logger::errorLog(print_r($data, true), 'Xiaonuonotify_data');
        Logger::dayLog("Xiaonuonotify_data", print_r($data, true));
        if (empty($data)) {
            echo '非法请求';
            exit;
        }
        //$parr = json_decode($data, true);
        $parr = $openApi->parseReturnData($data);
        //Logger::errorLog(print_r($parr, true), 'Xiaonuonotify');
        Logger::dayLog("Xiaonuonotify", print_r($parr, true));
        if ($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 6) {
            //出款成功
            $status = 'SUCCESS';
        } else if($parr['res_code'] == 0 && $parr['res_data']['remit_status'] == 11) {
            //出款失败
            $status = 'FAIL';
        } else {
            echo "非法数据！";
            exit;
        }

        //订单号
        $req_id = $parr['res_data']['req_id'];
        //出款请求号
        $client_id = isset($parr['res_data']['client_id']) ? $parr['res_data']['client_id'] : '';
        $user_remit_list = User_remit_list::find()->joinWith('loanExtend', true, 'LEFT JOIN')->where([User_remit_list::tableName() . '.order_id' => $req_id])->one();
        if (empty($user_remit_list)) {
            Logger::dayLog("Xiaonuonotify", $req_id, "出款订单号不存在");
            echo 'SUCCESS';
            exit;
        }
        if ($status == 'FAIL') {
            //1 将出款表中的状态改为失败，将借款附属表中的状态改为失败
            $rsp_code = '9999';
            $rsp_msg = isset($parr['res_data']['rsp_status_text']) ? $parr['res_data']['rsp_status_text'] : '失败';
            $result_remit = $user_remit_list->savePayFail($rsp_code, $rsp_msg, $client_id);

        } else {
            $result_remit = $user_remit_list->savePaySuccess($client_id);
        }

        if (!$result_remit) {
            Logger::dayLog("Xiaonuonotify", $req_id, "出款记录表修改失败");
            return false;
        }

        $loan_notify = new YiLoanNotify();
        $loan_notify->saveNotifyRecord($user_remit_list);

        echo 'SUCCESS';
        exit;
    }
}