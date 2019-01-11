<?php

/**
 * 一亿元订单查询
 * 
 */
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\common\Logger;
use app\models\Payorder;
use yii\helpers\ArrayHelper;
use app\models\txsk\TxskBindBank;
class PayorderController extends ApiController {

    protected $server_id = 105;//服务号
    public function init() {
        parent::init();
    }
    /**
     * Undocumented function
     * 订单查询
     * @return void
     */
    public function actionOrderquery() {
        $postdata = $this->reqData; //解密的数据1
        $aid = $this->appData['id']; //aid
        Logger::dayLog('payorder', '请求数据', $postdata);
        //字段判断是否为空
        $cardno = ArrayHelper::getValue($postdata,'cardno');
        $identityid = ArrayHelper::getValue($postdata,'identityid');
        if(empty($cardno) && empty($identityid)){
            return $this->resp('-1','参数缺失');
        }
        $dataList = (new Payorder)->getPayorder($cardno,$identityid);
        $returnData = array(
			'res_code' => 0,
			'res_data' => $dataList,
		);
        echo json_encode($returnData,JSON_UNESCAPED_UNICODE);
    }
    /**
     * Undocumented function
     * 绑卡查询
     * @return void
     */
    pubLic function actionBindcard(){
        $postdata = $this->reqData; //解密的数据1
        $aid = $this->appData['id']; //aid
        Logger::dayLog('payorder', '请求数据', $postdata);
        //字段判断是否为空
        $phone = ArrayHelper::getValue($postdata,'phone');
        $cardno = ArrayHelper::getValue($postdata,'cardno');
        if(empty($phone) && empty($cardno)){
            return $this->resp('-1','参数缺失');
        }
        $dataList = (new TxskBindBank)->getBindcard($phone,$cardno);
        $returnData = array(
			'res_code' => 0,
			'res_data' => $dataList,
		);
        echo json_encode($returnData,JSON_UNESCAPED_UNICODE);
    }
}
