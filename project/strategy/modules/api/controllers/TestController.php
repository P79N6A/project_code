<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/30
 * Time: 17:12
 */
namespace app\modules\api\controllers;

use app\commands\yyy\logic\AllInLogic;
use Yii;
class TestController extends ApiController
{
    public function init()
    {

    }
    //天启决策
    public function actionTianqi()
    {
        $oAllInLogic = new AllInLogic();
        $user_crif_data = [];
        $prome_crif_res = '{
                "AMOUNT":"2",
                "CARD_MONEY":"0",
                "CRAD_RATE":"0",
                "DAYS":"0",
                "INTEREST_RATE":"0",
                "RESULT":"3",
                "ious_days":"0",
                "ious_status":"0",
                "result_tq":"0",
                "result_model_tq":"0",
                "id_ph_black_LOAN_A":"0",
                "id_ph_LOAN_A":"0",
                "request_id":"2245",
                "result_tx":1,
        }';
        $prome_crif_res = json_decode($prome_crif_res, true);
        $process_code = "YI_YI_YUAN_TIANQI";
        $a = $oAllInLogic -> queryOriginCrif($user_crif_data, $prome_crif_res,$process_code);
        var_dump($a);
    }
}