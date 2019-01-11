<?php
namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\Selection;
use Yii;

class NotifybankflowController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    //在线还款服务器异步通知地址
    public function actionIndex()
    {
        $data = $this->post('res_data');
        Logger::dayLog('selection', 'notify', $data);
        $openApi = new ApiClientCrypt;
        $parr = $openApi->parseReturnData($data);
//        $parr = [
//            'res_code' => 0,
//            'res_data' => [
//                'user_id' => 8079073,
//                'task_id' => 'TASKCHSI00000015271275854223192q4534645745656',
//                'request_id' => 716,
//                'status' => 11,
//                'source' => 1,
//                'create_time' => '2018-05-24 10:05:13',
//                'reason' => '{"code":2414,"message":"\\u4efb\\u52a1id\\u7f3a\\u5931\\u6216\\u65e0\\u6548"}',
//                'name' => '邵彩红',
//                'mobile' => '',
//                'idcard' => '132624199212288102',
//                'app_id' => '2810335722015',
//            ]
//        ];
        Logger::dayLog('selection', 'notify', $parr);
        if (!isset($parr['res_data']['request_id']) || empty($parr['res_data']['request_id']) || $parr['res_code'] != 0) {
            exit('request_id is null or res_code error');
        }
        $selection = (new Selection())->getSelectionByRequestId($parr['res_data']['request_id']);
        if (empty($selection)) {
            exit('request_id error');
        }
        if ($selection['user_id'] != $parr['res_data']['user_id'] || $selection['type'] != $parr['res_data']['source']) {
            exit('user_id error or type error');
        }
        $this->postNotify($selection, $parr['res_data']);
    }

    private function postNotify($selection, $parr)
    {
        if (empty($selection) || empty($parr)) {
            exit;
        }
        $time = date('Y-m-d H:i:s');
        switch ($parr['status']) {
            case 2:
                $result = $selection->saveSucc($parr['reason'], $time);
                break;
            case 11:
                $result = $selection->saveFail($parr['reason'], $time);
                break;
            default :
                $result = $selection->saveFail($parr['reason'], $time);
        }
        if (empty($result)) {
            exit('record error');
        }
        exit('SUCCESS');
    }
}