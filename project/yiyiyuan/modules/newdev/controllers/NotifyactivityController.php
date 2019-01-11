<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/23
 * Time: 11:33
 */

namespace app\modules\newdev\controllers;


use app\common\ApiClientCrypt;
use app\common\Logger;
use app\models\dev\ActivityLoanRepay;
use app\models\news\Coupon_list;
use app\models\news\User;
use Yii;

class NotifyactivityController extends NewdevController
{
    public $enableCsrfValidation = false;
    private $notify_url;
    private $error_url;

    public function behaviors()
    {
        return [];
    }
    public function init()
    {
        $this->notify_url = ['/borrow/purchasecardsactivity/index'];
        $this->error_url  = ['/borrow/purchasecardsactivity/index'];
    }

    /**
     * @return bool|void|\yii\web\Response
     * 同步异步方法
     */
    public function actionNotifybackurl()
    {
        $openApi = new ApiClientCrypt();
        if (isset($_GET['res_data'])) {
            $data = Yii::$app->request->get('res_data');
        } else {
            $data = Yii::$app->request->post('res_data');
        }
        $isPost = Yii::$app->request->isPost;
        $parr = $openApi->parseReturnData($data);
        Logger::dayLog('notify/notiftActivity', $parr);  //记录解析出来的数据
        if (!isset($parr['res_data']['orderid']) || empty($parr['res_data']['orderid'])) {
            return FALSE;
        }
        $activityLoanRepayData = ActivityLoanRepay::find()->where(['order_pay_no' => $parr['res_data']['orderid']])->one();
        if (empty($activityLoanRepayData)) {
            return FALSE;
        }
        if ($isPost) {
           return $this->postNotify($activityLoanRepayData, $parr);
        } else {
            return $this->getNotify($activityLoanRepayData, $parr);
        }
    }

    /**
     * @param $activityLoanRepayData
     * @param $parr
     * @return \yii\web\Response
     * 同步
     */
    public function getNotify($activityLoanRepayData, $parr)
    {
        if($activityLoanRepayData->status==0){
            if (isset($parr['res_data']['status']) && $parr['res_data']['status'] == '11') {
                $status = 2;
            } else {
                $status = -1;
            }
            $condition = [
                'status' => $status,
                'return_code' => $parr['res_data']['res_code'],
                'return_msg'  => $parr['res_data']['res_msg'],
            ];
            $res = $activityLoanRepayData->update_batch($condition);
            if (!$res) {
                Logger::dayLog('notify/repay', 'get_update_fail' . $parr['res_data']['orderid'], $condition);
            }
        }
        if ($parr['res_code'] == 0) {
            if (($parr['res_data']['status'] == '2') || ($parr['res_data']['status'] == '3') || ($parr['res_data']['status'] == '4')) {
                $url = $this->getUrl(1);
                Logger::dayLog('new_notiftavtivity', 'get_url_1', $url);
                return $this->redirect($url);
            } else {
                $url = $this->getUrl(2);
                Logger::dayLog('new_notiftavtivity', 'get_url_2', $url);
                return $this->redirect($url);
            }
        } else {
            $url = $this->getUrl(2);
            return $this->redirect($url);
        }
    }

    /**
     * @param $orderRepay
     * @param $parr
     * 异步
     */
    private function postNotify($orderRepay, $parr) {
        if (empty($orderRepay) || empty($parr)) {
            exit;
        }
        if($orderRepay->status==1 || $orderRepay->status==2){
            exit('SUCCESS');
        }
        if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 2) {//成功处理
            $condition = ['status' => 1];
            $res = $orderRepay->update_batch($condition);
            if(!$res){
                Logger::dayLog('new_notiftavtivity', '成功-修改失败'.$orderRepay->order_pay_no);
            }else{
                $this->sendCoupon($orderRepay->user_id);  //成功-发送优惠券
                exit('SUCCESS');
            }
        } else if ($parr['res_code'] == 0 && $parr['res_data']['status'] == 11) {//失败处理
            $condition = ['status' => 2];
            $res = $orderRepay->update_batch($condition);
            if(!$res){
                Logger::dayLog('new_notiftavtivity', '失败-修改失败'.$orderRepay->order_pay_no);
            }else{
                exit('SUCCESS');
            }
        }
    }


    /**
     * @param $userInfo
     * 购买成功
     * 发送优惠券
     */
    private function sendCoupon($uid)
    {
        $model = new Coupon_list();
        $userInfo = User::findOne($uid);
        $arr[] = [
            'title' => '20元还款券',
            'type' => 5,
            'val' => 20,
            'mobile' => $userInfo->mobile,
            'start_date' => date('Y-m-d 00:00:00'),
            'end_date' => date('Y-m-d 00:00:00', strtotime('+40 days')),
            'create_time' => date('Y-m-d H:i:s'),
            'sn' => date('ymdHis', time()) . '1',
        ];
        for ($i=0;$i<5;$i++){
            $res =  $model->insertBatch($arr);
            if(!$res){
                Logger::log('sendCoupon','发送失败--'.$i.'--'.$userInfo->mobile);
            }
        }
    }

    /**
     * 获取还款的同步路径
     * @param type $loan_repay 还款记录
     * @param type $status 还款状态  1 成功，2失败
     * @param type $source 还款来源 目前只针对我们自己的app
     */
    private function getUrl($status) {
        if ($status == 1) {
            $url = $this->notify_url;
        } else {
            $url = $this->error_url;
        }
        return $url;
    }
}