<?php

/**
 * 一亿元推送用户
 * 
 */
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\xn\XnRemit;
use app\models\xn\XnBank;
use app\modules\api\common\xn\XnRemitRule;

class XnremitController extends ApiController {

    protected $server_id = 104;//服务号
    public function init() {
        parent::init();
    }

    public function actionReceive() {
        $postdata = $this->reqData; //解密的数据1
        $aid = $this->appData['id']; //aid
        Logger::dayLog('xn/xnremit', '请求数据', $postdata);
        //字段判断是否为空
        $oXnRemit = new XnRemit;
        $check_result = $oXnRemit->getVerifyEmptyData($postdata);
        if (!$check_result){
            Logger::dayLog('xn/xnremit', 'errinfo',$oXnRemit->errinfo);
            $this->resp(104001, $oXnRemit->errinfo);
        }
        //获取小诺出款规则限制
        $remitRule = new XnRemitRule;
        $res = $remitRule->getRemitRule($postdata);
        if ($res['res_code'] != '0000') {
            Logger::dayLog('xn/xnremit', $postdata,$res);
            return $this->resp($res['res_code'], $res['res_data']);
        }
         //银行限制
        $bank_addr = ArrayHelper::getValue($postdata,'bank_addr','');
        $bankInfo = (new XnBank)->getBankInfo($bank_addr);
        if(empty($bankInfo)){
             return $this->resp(104009,'银行不再所授范围内');
        }
        $postdata['bank_code'] = $bankInfo->bank_addr;
        $postdata['aid'] = $aid;
        $resultData = $oXnRemit->saveRemitData($postdata);
        if (!$resultData){
            Logger::dayLog('xn/xnremit', 'saveRemitData', 'remit 数据保存失败', '提交数据', $postdata, '错误原因', $oXnRemit->errinfo);
            return $this->resp(104010, $oXnRemit->errinfo);
        }
        return $this->resp(0, $resultData);
    }
    
}
