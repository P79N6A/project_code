<?php
namespace app\modules\peanut\logic;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\Stuser;

use app\modules\peanut\common\JavaCrif;
use app\modules\peanut\common\PublicFunc;


class RiskLogic extends BaseLogic {
    private $oPublicFunc;
    private $oJavaCrif;
    public function __construct() {
        $this->oPublicFunc = new PublicFunc();
        $this->oJavaCrif = new JavaCrif();
    }
    public function Withdraw($data) {
        // save request
        $request_id = $this->oPublicFunc->saveRequest($data);
        if (!$request_id) {
            return $this->returnInfo(false, '请求记录失败');
        }
        $data['request_id'] = $request_id;
        // query JavaCrif
        $process_code = JavaCrif::PRO_PEA_WITHDRAW;
        $crif_res = $this->oJavaCrif->queryCrif($request_id,$data,$process_code);
        if (empty($crif_res)) {
            return $this->returnInfo(false, '决策异常');
        }
        // save result
        $save_res = $this->oPublicFunc->saveResult($data,$crif_res);
        if (!$save_res) {
            return $this->returnInfo(false, '结果记录异常');
        }
        return $this->returnInfo(true, $crif_res);
    }
}