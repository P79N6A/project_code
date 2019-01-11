<?php
/**
 * 微神马用户进件信息
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/13
 * Time: 15:30
 * 测试地址：http://paysystem.com/weishenma/intopiecesuser
 */
namespace app\modules\api\controllers;

use app\models\ZFLimit;
use app\modules\api\common\wsm\CWSMRemit;
use app\modules\api\common\wsm\Intopiecesuserverfy;
use Yii;
use app\modules\api\common\ApiController;
use app\models\wsm\WsmRemit;
use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * Class WsmremitController
 * @package app\modules\api\controllers
 * 地址：http://paysystem.com/api/wsmremit
 */

class WsmremitController extends ApiController
{
    protected $server_id = 103;  //服务号

    /**
     * 入口
     */
    public function actionIndex()
    {
        $postdata = $this->reqData; //解密的数据
        //$postdata = $this->notNullData();
        Logger::dayLog('wsm/wsmremit', 'content', $postdata);
        $oCWSMRemit = new CWSMRemit();
        $check_parrams_state = $oCWSMRemit->formatData($postdata);
        if ($check_parrams_state['code'] != 200){
            Logger::dayLog('wsm/wsmremit', 'error',$check_parrams_state['msg']);
            $this->resp($check_parrams_state['code'], $check_parrams_state['msg']);
        }
        $logical_state = $this->logicalProcessing($check_parrams_state['msg']);

        $return_coce = $logical_state['code'] == 200 ? 0:$logical_state['code'];
        $this->resp($return_coce, $logical_state['msg']);

    }

    /**
     * 逻辑处理
     * @param $data_set
     * @return array
     */
    private function logicalProcessing($data_set)
    {
        $oCWSMRemit = new CWSMRemit();
        //(1).时间，订单限制
        $time_limit_info = $oCWSMRemit->timeLimitInfo($data_set);
        if ($time_limit_info['code'] != 200){
            return $time_limit_info;
        }

        //(2).格式数据
        $foramt_wsm_remit_data = $oCWSMRemit->foramtWsmRemitData($data_set);

        if ($foramt_wsm_remit_data['code'] != 200){
            return $foramt_wsm_remit_data;
        }

        //(3).插入数据
        $wsm_remit_object = new WsmRemit();
        $result = $wsm_remit_object->saveOrder($foramt_wsm_remit_data['msg']);
        if ($result)
        {
            return ['code'=>200,'msg'=>['bidNum'=>$foramt_wsm_remit_data['msg']['req_id'], 'client_id'=>$foramt_wsm_remit_data['msg']['req_id']]];
        }
        return ['code' => 1030009, 'msg' => $wsm_remit_object->errinfo];

    }

    
}