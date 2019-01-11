<?php
namespace app\modules\borrow\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\models\news\Selection_bankflow;
use Yii;

class NotifyselectionController extends BorrowController {
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    //银行流水异步通知地址
    public function actionIndex() {
        $data = $this->post('res_data');
//        $data = 'ozmyT46i1CBllOc2nJVlbiAaDfLXivGrHboq4Q4PXNuEgAcQ7F8JwNP21ZTpXIEe65oUvW7FhqW0mSShyJjupd1Ooe3jsxcTKeSufzaOVmttMfyWtWELBS8P+hdQrm2F29PEWad6eFgV+oORmc8rERvaQQ1xuRxR0';
        Logger::dayLog('bankflow', 'notify', $data);
        $openApi = new ApiClientCrypt;
        $parr = $openApi->parseReturnData($data);
//        $parr = [
//            'res_code' => 0,
//            'res_data' => [
//                'user_id' => 8079073,
//                'request_id' => 716,
//                'status' => 11,
//            ]
//        ];
        Logger::dayLog('bankflow', 'notify', $parr);
        if (!isset($parr['res_data']['request_id']) || empty($parr['res_data']['request_id']) || $parr['res_code'] != 0) {
            exit('request_id is null or res_code error');
        }
        $selection = (new Selection_bankflow())->getSelectionByRequestId($parr['res_data']['request_id']);
        if (empty($selection)) {
            exit('request_id error');
        }
        if ($selection['user_id'] != $parr['res_data']['user_id']) {
            exit('user_id error or type error');
        }
        $this->postNotify($selection, $parr['res_data']);
    }

    private function postNotify($selection, $parr) {
        if (empty($selection) || empty($parr)) {
            exit;
        }
        $time = date('Y-m-d H:i:s');
        switch ($parr['status']) {
            case 2:
                $result = $selection->saveSucc($time);
                break;
            case 11:
                $result = $selection->saveFail($time);
                break;
            default :
                $result = $selection->saveFail($time);
        }
        if (empty($result)) {
            exit('record error');
        }
        exit('SUCCESS');
    }
}