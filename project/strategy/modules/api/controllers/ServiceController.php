<?php
namespace app\modules\api\controllers;

use app\modules\api\common\Compliance;
use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\modules\api\logic\ServiceLogic;

class ServiceController extends ApiController
{   

    public function init() {
        parent::init();
    }

    public function actionCreditData() {
        $postData = $this->postdata;
        // if (!SYSTEM_PROD) {
        //     $postData = [
        //         'request_id' => '10707369', // 运营商报告请求ID
        //         'user_id'    => '2599989', // 智融用户对应的一亿元user_id
        //         'aid'        => '10', // 智融钥匙产品ID   10
        //     ];
        // }
        
        if (!is_array($postData) || empty($postData)) {
            Logger::dayLog(
                'strategyReq', 'postdata','请求数据异常',$postData
            );
            return $this->resp(100001, '请求数据异常');
        }
        //业务参数
        $aid        = ArrayHelper::getValue($postData,'aid','');
        $request_id = ArrayHelper::getValue($postData,'request_id','');
        $user_id    = ArrayHelper::getValue($postData,'user_id','');

        if (empty($request_id) || empty($aid) || empty($user_id)){
            return $this->resp(100002, "参数信息不完整");
        }

        $oServiceLogic = new ServiceLogic(); 
        $operator = $oServiceLogic->getCreditData($postData);
        $ret_data = [
            'user_id'    => $user_id,
            'request_id' => $request_id,
            'aid'        => $aid,
            'operator'   => $operator,
        ];
        return $this->resp('0000', $ret_data);
    }

    /**
     * 合规请求数据接口
     * 请求参数：user_id
     *          loan_id
     *          strategy_req_id（请求评测时决策返回的ID）
     *          request_id（请求运营商报告时的ID）
     *          aid
     *          loan_create_time(借款时间或评测时间)
     *          relation_phone(亲属联系人)
     *          idcard  身份证
     *          phone
     *
     */
    public function actionCompliance()
    {
        $postData = $this->postdata;
        Logger::dayLog('compliance', 'postdata','请求数据',$postData);
        /*
        if (!SYSTEM_PROD){
            $postData = [
                'user_id'           => '2599989',
                'loan_id'           => '18121526',
                'strategy_req_id'   => '', //（请求评测时决策返回的ID）
                'request_id'        => '2258691', //（请求运营商报告时的ID）
                'aid'               => '1', //
                'loan_create_time'  => '', //(借款时间或评测时间)
                'relation_phone'    => '', //(亲属联系人)
                'idcard'            => '510113199005035623', //身份证
                'phone'             => '18665130369', //
            ];
        }
        */

        if (!is_array($postData) || empty($postData)) {
            Logger::dayLog(
                'compliance', 'postdata','请求数据异常',$postData
            );
            return $this->resp(100001, '请求数据异常');
        }
        
        $compliance = new Compliance();
        $ret_data = $compliance->logicalProcessing($postData);
        return $this->resp(ArrayHelper::getValue($ret_data, 'res_code', "100001"), ArrayHelper::getValue($ret_data, 'res_data', '请求数据异常'));
    }
}