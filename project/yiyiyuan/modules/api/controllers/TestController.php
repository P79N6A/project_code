<?php

namespace app\modules\api\controllers;

use app\commonapi\appLogger;
use app\commonapi\Common;
use app\commonapi\Logger;
use app\modules\api\common\ApiController;
use Exception;
use Yii;
use app\models\service\StageService;
use app\models\news\User_loan;
class TestController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $oUserLoan = (new User_loan)::findOne(223728667);
        $oStageService = new StageService;
        $result = $oStageService->addStageBill($oUserLoan);
    }
    public function actionTestcheck(){
        $periods = [
            //2=>500,
            //1=>550,
            3=>500,
            //4=>500,
        ];
        $loan_id = 223728667;
        $oStageService = new StageService;
        $result = $oStageService->checkSubmitRepayBill($periods,$loan_id);
        $repay_id = 1111112233;
        $oStageService->lockToRepay($repay_id,$result);
        $oStageService->lockToRepaying($repay_id);
        $oStageService->toSuccess($repay_id);
        //$oStageService->toFail($repay_id);
    }
    public function actionTestchecktime(){
        $loan_id = 223728667;
        $oStageService = new StageService;
        $res = $oStageService->checkRepaybillModifytime($loan_id);
        var_dump($res);
    }
}
