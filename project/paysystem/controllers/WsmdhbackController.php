<?php
/**
 * 微神马贷后中间接口
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/19
 * Time: 16:34
 *  回调地址：http://paytest.xianhuahua.com/wsm/wsmdhback/notify
 *  测试数据：{
                "data": [
                {
                "shddh": "wsm_20171019141901"
                },
                {
                "shddh": "wsm_20171019140155"
                }
                ],
                "time":"12323232",
                "sign":"e3e1ab7a22df744bd1e89e165ef2963b"
                }
 */
namespace app\controllers;

use app\common\Crypt3Des;
use app\common\Http;
use app\models\App;
use app\models\wsm\WsmRemit;
use app\modules\api\common\ApiController;

use app\modules\api\common\wsm\WSMApi;
use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * Class WsmdhbackController
 * @package app\controllers
 *  测试地址：http://paytest.xianhuahua.com/wsmdhback/notify
 * 本地地址：http://paysystem.com/wsmdhback/notify
 */

class WsmdhbackController extends ApiController
{

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }
    public function actionNotify() {
        $getData = file_get_contents("php://input");
        Logger::dayLog('wsm/wsmdh', 'content',$getData);
        $satet = $this->logicalProcessing($getData);

        return $satet;

    }

    private function logicalProcessing($data_set)
    {
        //验证数据
        //1.获取参数
        $clent_id_data = json_decode($data_set, true);
        if (empty($clent_id_data) || empty($clent_id_data['data']) || empty($clent_id_data['time'])){
            return $this->getError(1030021, 'data数据不能为空！');
        }
        //2.取出req_id
        $wsm_api_object = new WSMApi();
        //验签
        $verfy_sign = $clent_id_data['sign'];
        $sign = md5($clent_id_data['time'] .  $wsm_api_object->getYiyiyuanEncode());
        if ($verfy_sign != $sign){
            return $this->getError(1030022, '验签失败');
        }
        $wsm_remit_object = new WsmRemit();
        $req_id_data = [];
        foreach($clent_id_data['data'] as $value){
            if (empty($value['shddh'])) {
                Logger::dayLog('wsm/wsmdh', 'error',$value['shddh']);
                continue;
            }
            $wsm_remit_info = $wsm_remit_object->getWsmRemitOne($value['shddh']);
            if (!empty($wsm_remit_info->req_id)) {
                $req_id_data[] = $wsm_remit_info->req_id;
            }
        }
        //3.发送
        $send_notify_data = $this->sendNotify($req_id_data);
        if (empty($send_notify_data) || empty($send_notify_data['res_data']) || $send_notify_data["res_code"] != "0000"){
            return $this->getError(1030023, '查询订单失败');
        }

        //4.返回数据
        $return_data = $this->returnData($send_notify_data['res_data'], $wsm_remit_object);
        if (empty($return_data)){
            return $this->getError(1030024, '查询订单失败');
        }
        //5.加密
        $data_info =$wsm_api_object -> wsm_encrypt(json_encode($return_data), $wsm_api_object->getEnkeys(), $wsm_api_object->getEnkeys());
        $data_info = $return_data;
        return $this->getError('200', $data_info);
    }

    private function formatData($data, $wsm_remit_info, $wsm_api_object)
    {
        if (empty($data) || empty($wsm_remit_info)){
            return false;
        }
        $tip = json_decode($wsm_remit_info['tip'],true);
        $schedules = [
            [
                'periodStage' => $wsm_api_object->emptyArrDefaultNull($data, "periodStage"), //当前期数
                'dueAt' => $wsm_api_object->emptyArrDefaultNull($data, "dueAt"), //应还日期
                'repayAtPartial' => $wsm_api_object->emptyArrDefaultNull($data, "repayAtPartial"), //本息还清日期
                'repayAt' => $wsm_api_object->emptyArrDefaultNull($data, "repayAt"), //实际全部还清日期（+罚息）
                'status' => $wsm_api_object->emptyArrDefaultNull($data, "status"), //还款状态（1.还款中 2.本息已完清 罚息未还清 3.全部已还清 ）
                'principal' => $wsm_api_object->emptyArrDefaultNull($data, "principal"), //应还本金
                'repaidCapital' => $wsm_api_object->emptyArrDefaultNull($data, "repaidCapital"), //已还本金
                'interest' => $wsm_api_object->emptyArrDefaultNull($data, "interest"), //应还利息
                'repaidInterest' => $wsm_api_object->emptyArrDefaultNull($data, "repaidInterest"), //已还利息
                'repaidPenalty' => $wsm_api_object->emptyArrDefaultNull($data, "repaidPenalty"), //已还罚息
                'overdues' => $wsm_api_object->emptyArrDefaultNull($data, "overdues"), //逾期天数
            ],
        ];
        $format_data = [
            'shddh' => ArrayHelper::getValue($wsm_remit_info, 'client_id', ''), //商户订单号（微神马资产唯一识别号）
            'userName' => ArrayHelper::getValue($wsm_remit_info, 'realname', ''), //用户姓名
            'idCard' =>  ArrayHelper::getValue($wsm_remit_info, 'identityid', ''), //用户身份证号
            'periodTotal' => ArrayHelper::getValue($tip, 'qx', ''), //总分期
            'amount' => ArrayHelper::getValue($wsm_remit_info, 'settle_amount', ''), //合同金额 没有就等于应还本金
            'lastRepayAt' => $wsm_api_object->emptyArrDefaultNull($data, "lastRepayAt"), //最近还款日期
            'loanSuccess' => $wsm_api_object->emptyArrDefaultNull($data, "loanSuccess"), //是否放款成功（1.是 2.否）
            'schedules' => $schedules,
        ];
        return $format_data;
    }

    private function sendNotify($req_data)
    {
        $wsm_api_object = new WSMApi();
        $data_aes = WSMApi::encode(json_encode($req_data), $wsm_api_object->getYiyiyuanEncode());

        $data_set = [
            'res_data' => $data_aes,
        ];

        Logger::dayLog('wsm/wsmdh', 'send_data',$req_data);
        $ret = Http::interface_post($wsm_api_object->getAfterTheLoan(), $data_set);
        Logger::dayLog('wsm/wsmdh', 'return_data',$ret);
        $status_data = Crypt3Des::decrypt($ret,$wsm_api_object->getYiyiyuanEncode());
        /*
        $status_data = [
            "res_code" => "0000",
            "res_data" => [
                "Y20171018042441ID444311" => 'fd',
                "Y20171018042441ID44238" => 'dfd',
            ],
        ];
        */
        $status_data = json_decode($status_data,true);
        return $status_data;
    }

    private function returnData($send_notify_data, $wsm_remit_object)
    {
        if (empty($send_notify_data)){
            return false;
        }
        $wsm_api_object = new WSMApi();
        $return_data = [];
        foreach($send_notify_data as $key => $value){
            $info = $wsm_remit_object->getWsmRemitReqIdOne($key);
            $format_data = $this->formatData($value, $info, $wsm_api_object);
            if ($format_data) {
                $return_data[] = $format_data;
            }
        }
        return $return_data;
    }

    /**
     * 错误输出
     * @param $code
     * @param string $msg
     * @return array
     */
    private function getError($code, $msg='')
    {
        return json_encode(['code'=>$code, 'msg'=>$msg]);
    }
}