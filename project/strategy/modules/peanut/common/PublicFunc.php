<?php
/**
 * 接口基类
 */
namespace app\modules\peanut\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

use app\models\peanut\PeaRequest;
use app\models\peanut\PeaResult;

class PublicFunc {

	// function __construct()
 //    {

 //    }

    //记录米富请求
    public function saveRequest($data)
    {
        
        if (empty($data) || !is_array($data)) {
            Logger::dayLog('peanut/addRequest', 'data is null',json_encode($data));
            return 0;
        }
        $saveArr = [
            'order_id' => ArrayHelper::getValue($data,'order_id',''),
            'user_id' => ArrayHelper::getValue($data,'user_id',0),
            'st_source' => ArrayHelper::getValue($data,'st_source','3'),
        ];
    	$oPeaRequest = new PeaRequest();
        $request_id = $oPeaRequest->saveData($saveArr);
        if (!$request_id) {
            Logger::dayLog('peanut/addRequest', 'addRequest',$request->errors,$data);
            return 0;
        }
        return $request_id;
    }

	//记录决策结果
	public function saveResult($data,$result)
	{
        if (empty($data) || empty($result) || !is_array($data) || !is_array($result)) {
            Logger::dayLog('peanut/saveRes', 'result','数据异常',$result,$data);
            return false;
        }
        $saveArr = [
            'request_id' => ArrayHelper::getValue($data, 'request_id',0),
            'order_id' => ArrayHelper::getValue($data, 'order_id',''),
            'user_id' => ArrayHelper::getValue($data, 'user_id',0),
            'st_source' => ArrayHelper::getValue($data, 'st_source',0),
            'res_status' => ArrayHelper::getValue($result, 'result',0),
            'res_info' => json_encode($result, JSON_UNESCAPED_UNICODE),
        ];
        $record_res = new PeaResult();
        $res = $record_res->saveData($saveArr);
        if (!$res) {
            Logger::dayLog('peanut/saveRes', 'result','结果记录失败:',$record_res->errors,$result,$data);
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
}