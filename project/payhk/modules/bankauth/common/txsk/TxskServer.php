<?php
/**
 * 天行数科银联四要素验证
 * 内部错误码范围 10000
 * @author lijin
 */
namespace app\modules\bankauth\common\txsk;

use app\common\Logger;
use app\models\txsk\TxskBindBank;
use app\models\AuthbankOrder;
use app\modules\bankauth\common\AuthBankInterface;
use app\modules\bankauth\common\ExceptionHandler;
use yii\helpers\ArrayHelper;
use yii\db\Command;

class TxskServer implements AuthBankInterface
{

    /**
     * 银行四联API
     */
    private $txskApi;
    private $txskBind;

    /**
     * TxskServer constructor.
     */
    public function __construct()
    {
        $this->txskBind = new TxskBindBank;
        
    }

    /**
     * 绑定银行卡
     *
     * @param $bindcardData
     * @return array
     */
    public function requestAuth($bindcardData)
    {
        //引入配置文件
        $this->txskApi = new TxskApi($bindcardData);

        //1 数据检测
        if (empty($bindcardData)) {
            return $this->returnError('2010001');
        }

        //2 从天行绑卡记录表中获取是否超限
        $result = $this->txskBind->chkQueryNum($bindcardData['idcard']);
        if (!$result) {
            return $this->returnError('2010002');
        }

        //3 是否曾经存在于天行绑卡记录表中
        $log = $this->txskBind->existSameFail($bindcardData);
        if ($log) {
            $errorCode = $log->error_code ? $log->error_code : 2010100;
            $error = $log->error_msg ? $log->error_msg : "验证失败";
            $errormsg = $this->txskApi->parseError($error);
            return $this->returnError($errorCode, $errormsg);
        }

        //一分钟内限制
        $log_one = $this->txskBind->existSameFailOne($bindcardData);
        $create_time = ArrayHelper::getValue($log_one, 'create_time', 0);
        $limit_time = time() - strtotime($create_time);
        if ($limit_time <= 60){
            return $this->returnError("2010005");
        }
        
        //4 保存到天行绑卡记录中
        $result = $this->txskBind->savaData($bindcardData, TxskBindBank::STATUS_INIT);
        if (!$result) {
            Logger::dayLog(
                'txskbindbank',
                '记录保存失败', $this->txskBind->errors, $this->txskBind->errinfo,
                'data', $bindcardData
            );
            return $this->returnError('2010003');
        }
        //5 银行四要素验证接口
        $res = $this->txskApi->getApiBank($bindcardData);
        Logger::dayLog('txskServer', '请求绑卡', $bindcardData, '天行结果', $res);
        if (!$res) {
            return $this->returnError('2010105', $this->txskApi->errinfo);
        }
        if ($res['status'] != 200) {
            return $this->returnError($res['status'], '__timeout');
//            return ['code'=>$res['status'],'data'=>'__timeout'];
        }
        // 解析json
        $resultData = json_decode($res['data'], true);
        if (!isset($resultData['success']) || !$resultData['success']) {
            $error = isset($resultData['errorDesc']) ? $resultData['errorDesc'] : "查询失败";
            $errorCode = '2010101';
            $this->saveErrorMsg($errorCode, $error);
            return $this->returnError($errorCode, $error);
        }
        //3 获取结果字符串
        $r = ArrayHelper::getValue($resultData, "data.checkStatus");
        if ($r != 'SAME') {
            $err = ArrayHelper::getValue($resultData, "data.result");
            $errorCode = '2010102';
            $errormsg = $r.'|'.$err;
            $this->saveErrorMsg($errorCode, $errormsg);
            return $this->returnError($errorCode, $errormsg);
        }

        // 6 保存到主绑卡表中
        $oAborder = new AuthbankOrder();
        $authObj = $oAborder->getByCardno($bindcardData['cardno']);
        if (!$authObj) {
            $mainRes = $oAborder->savaData($bindcardData);
            if (!$mainRes) {
                return $this->returnError('2010004', $oAborder->errinfo);
            }
        } else {
            $mainRes = $authObj->updateData($bindcardData, AuthbankOrder::STATUS_SUCC);
            if (!$mainRes) {
                return $this->returnError('2010004');
            }
        }
    
        // 7 修改绑卡记录表的状态
        $this->txskBind->status = TxskBindBank::STATUS_OK;
        $resSucc = $this->txskBind->save();

        $finalRes = [
            'code'=>$res['status'],
            'data'=>[
                'cardno'        => $this->txskBind->cardno,
                'idcard'        => $this->txskBind->idcard,
                'username'      => $this->txskBind->username,
                'phone'         => $this->txskBind->phone,
                'status'        => $this->txskBind->status,
                'channel_id'    => $this->txskBind->channel_id,
                'from'          => 'txsk',
            ]
        ];
        return $finalRes;
    }
    /**
     * 解除绑卡
     *
     * @param obj $oCard
     * @return void
     */
    public function overAuth($oCard, $identityid)
    {
        //1 生成一条解除绑卡记录
        $data = [
            'channelId' => $oCard ->channel_id,
            'cardno' => $oCard ->cardno,
            'idcard' => $oCard ->idcard,
            'username' => $oCard ->username,
            'phone' => $oCard ->phone,
        ];
        $result = $this->txskBind->savaData($data, TxskBindBank::STATUS_OVER);
        if (!$result) {
            ExceptionHandler::make_throw(2010103, $this->txskBind->errinfo);
        }
        //2 主绑卡表解除
        $oCard->status = AuthbankOrder::STATUS_UNBIND;
        $oCard->modify_time = date('Y-m-d H:i:s');
        $res = $oCard->save();
        if (!$res) {
            ExceptionHandler::make_throw(2010104, $oCard->errinfo);
        }
        return true;
    }

    /**	 * 返回错误信息
     * @param  false | null $result 错误信息
     * @param  string $errinfo 错误信息
     * @return array 同参数$result
     */
    private function returnError($code, $msg=''){
        if (empty($msg)) {
            $configPath = __DIR__ . "/../../config/errorCode.php";
            if (!file_exists($configPath)) {
                throw new \Exception($configPath . "配置文件不存在");
            }
            $config = include $configPath;
            $msg = !empty($config[$code]) ? $config[$code] : '';
        }
        return ['code'=>$code, 'data'=>$msg];
    }

    /**
     * 保存绑卡失败信息
     * @param $code
     * @param string $msg
     * @return bool
     */
    private function saveErrorMsg($code, $msg='',$status=TxskBindBank::STATUS_FAIL){
        $this->txskBind->error_code = $code;
        $this->txskBind->error_msg = $msg;
        $this->txskBind->status = $status;
        return $this->txskBind->save();
    }
}
