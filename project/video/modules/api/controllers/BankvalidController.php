<?php
/**
 * 天行数科银联四要素验证
 * 内部错误码范围 10000
 * @author lijin
 */
namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\BankValid;
use app\models\BankValidLog;
use app\modules\api\common\ApiController;
use app\modules\api\common\bank\Bank4;

class BankvalidController extends ApiController {
    /**
     * 服务id号
     */
    protected $server_id = 10;

    /**
     * 银行四联API
     */
    private $oBankApi;

    public function init() {
        parent::init();
        $this->oBankApi = new Bank4;
    }

    public function actionIndex() {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['username'])) {
            return $this->resp(10001, "用户名不能为空");
        }
        if (!isset($data['idcard'])) {
            return $this->resp(10002, "身份证不能为空");
        }
        if (!isset($data['cardno'])) {
            return $this->resp(10003, "卡号不能为空");
        }
        if (!isset($data['phone'])) {
            return $this->resp(10004, "手机号不能为空");
        }
        $data['aid'] = $this->appData['id'];

        //2 本地数据库是否存在
        $oBankValid = new BankValid;
        $oCard = $oBankValid->getByCard($data['cardno']);
        if ($oCard) {
            $res = $oCard->chk($data);
            if ($res) {
                // 成功时
                return $this->resp(0, [
                    'cardno' => $data['cardno'],
                    'idcard' => $data['idcard'],
                    'username' => $data['username'],
                    'phone' => $data['phone'],
                    'status' => true,
                    'type' => 'db',
                ]);
            } else {
                // 本地测试出不匹配
                return $this->resp(10005, "身份证或手机等无法匹配");
            }
        }

        //4 从日志中获取是否超限
        $oLog = new BankValidLog;
        $result = $oLog->chkQueryNum($data['idcard']);
        if (!$result) {
            return $this->resp(10009, "今日您查询次数过多");
        }

        //5 是否曾经存在于日志中
        $log = $oLog->existSameFail($data);
        if ($log) {
                $error = $log->error_msg ? $log->error_msg : "验证失败";
                $errormsg = $this->oBankApi->parseError($error);
                return $this->resp(10010, $errormsg);
        }

        //6 检测是否支持该卡
        $isSupport = $oBankValid->support($data['cardno']);
        if (!$isSupport) {
            return $this->resp(10006, "不支持该银行卡");
        }

        //7 保存到本地数据log中
        $result = $oLog->savaData($data);
        if (!$result) {
            Logger::dayLog(
                'bankvalid',
                'log保存失败', $oLog->errors, $oLog->errinfo,
                'data', $data
            );
        }

        //8 银行四要素接口
        $res = $this->oBankApi->chk($data);

        if (!$res) {
            $error = $this->oBankApi->errinfo;
            // 失败时
            $oLog->error_code = 10007;
            $oLog->error_msg = $error;
            $oLog->status = BankValidLog::STATUS_FAIL;
            $res = $oLog->save();
            $errormsg = $this->oBankApi->parseError($error);
            return $this->resp(10007, $errormsg);
        }

        //9 保存到本地数据库中
        $result = $oBankValid->saveData($data);
        if (!$result) {
            Logger::dayLog("bankvalid", "index", $data, "错误原因", $oBankValid->errors);
            return $this->resp(10008, "系统错误");
        }
        $oLog->status = BankValidLog::STATUS_OK;
        $res = $oLog->save();

        //10 成功时
        return $this->resp(0, [
            'cardno' => $data['cardno'],
            'idcard' => $data['idcard'],
            'username' => $data['username'],
            'phone' => $data['phone'],
            'status' => true,
            'type' => 'tx',
        ]);
    }
}
