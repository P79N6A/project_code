<?php
/**
 * 接口基类
 */
namespace app\modules\service\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;


use app\models\StCreditRequest;
use app\models\StCreditResult;

class SaveFunc {

	function __construct()
    {

    }

    //记录请求
    public function saveRequest($data)
    {
        $saveData = [
            'mobile' => ArrayHelper::getValue($data,'mobile',''),
            'aid' => ArrayHelper::getValue($data,'aid',0),
            'basic_id' => ArrayHelper::getValue($data,'basic_id',0),
            'afbase_id' => ArrayHelper::getValue($data,'afbase_id',0),
            'credit_data' => json_encode($data,JSON_UNESCAPED_UNICODE),
        ];
        
    	$oStCreditRequest = new StCreditRequest();
        $credit_id = $oStCreditRequest->saveData($saveData);
        if (!$credit_id) {
            Logger::dayLog('addRequest', 'addRequest',$oStCreditRequest->errors,$saveData);
            return 0;
        }
        return $credit_id;
    }

    // save result
    public function saveResult($init_data, $crif_res, $from){
        $saveData = [
            'credit_id' => ArrayHelper::getValue($init_data,'credit_id',0),
            'mobile' => ArrayHelper::getValue($init_data,'mobile',0),
            'aid' => ArrayHelper::getValue($init_data,'aid',0),
            'res_json' => json_encode($crif_res,JSON_UNESCAPED_UNICODE),
            'come_from' => $from,
        ];
        $oStCreditResult = new StCreditResult();
        $save_res = $oStCreditResult->saveData($saveData);
        if (!$save_res) {
            Logger::dayLog('service/saveResult', 'saveResult', $oStCreditResult->errors, $saveData);
        }
        return $save_res;
    }
    /**
     * [saveScoreData 记录评分卡数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveScoreData($data)
    {
        //记录借款附属信息
        $st_loan_extend = new StloanExtend();
        $ex_res = $st_loan_extend->addInfo($data);
        if (!$ex_res) {
            Logger::dayLog('reloan/addExtendInfo', '附属记录失败', $data,$st_loan_extend->errors);
            return false;
        }
        return true;
    }
    /**
     * [saveReg 记录用户注册信息]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveReg($data)
    {
        $record_user = new Stuser;
        $res = $record_user->addUserInfo($data);
        if (!$res) {
            Logger::dayLog('reg','用户记录失败', $record_user->errors,$data);
            return false;
        }
        return $res;
    }
    
    /**
     * [savePeriods 记录分期决策数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function savePeriods($data)
    {
        $stPeriods = new StPeriods;
        $res = $stPeriods->addPeriodsInfo($data);
        if (!$res) {
            Logger::dayLog('reg','用户记录失败', $stPeriods->errors,$data);
            return false;
        }
        return $res;
    }
}