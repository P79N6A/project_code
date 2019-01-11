<?php
/**
 * 华付金科银联四要素验证
 * 内部错误码范围 10000
 * @author lian0707
 */
namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\BankValid;
use app\models\BankValidLog;
use app\models\BankValidPlateform;
use app\modules\api\common\ApiController;
use app\modules\api\common\bank\HfjkApi;

class HfjkbankvalidController extends ApiController {
  /**
   * 服务id号
   */
  protected $server_id = 17;

  /**
   * 银行四联API
   */
  private $oBankApi;
  private $config;

  public function init() {
    parent::init();
    $env = YII_ENV_DEV ? 'dev' : 'prod';
    $this->oBankApi = new HfjkApi($env);
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
    $log = $oLog->existOne($data);
    if ($log) {
      if ($log->status == BankValidLog::STATUS_OK) {
        return $this->resp(0, [
          'cardno' => $data['cardno'],
          'idcard' => $data['idcard'],
          'username' => $data['username'],
          'phone' => $data['phone'],
          'status' => true,
          'type' => 'db',
        ]);
      } else {
        $error = $log->error_msg ? $log->error_msg : "验证失败";
        return $this->resp(10010, $error);
      }
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
    $params["header"] = array(
          "qryBatchNo" => $data['aid'].'-'.date('YmdHis').rand(1000,9999), //查询批次号(唯一，不超过20位)
          "qryReason"  => "信贷审批",  //查询原因(简单说明调用原由，可为空)
          "qryDate"    => date("Ymd"),  //查询日期(格式：yyyyMMdd，可为空)
          "qryTime"    => date("His"),    //查询时间(格式：hhmmss，可为空)
    );

    $params["condition"] = array(
          "realName"  =>  $data['username'], //姓名(不超过20位)
          "idCard"    =>  $data['idcard'], //身份证号码(必须符合身份证标准规范)
          "bankCard"  =>  $data['cardno'], //银行卡号
          "mobile"    =>  $data['phone'], //手机号
    );

    //发送请求
    $result = $this->oBankApi->send($params);
    $res_code = isset($result->data[0]->record[0]->resCode) ? $result->data[0]->record[0]->resCode : '';
    if ($res_code  != '00') {
      $error = isset($result->data[0]->record[0]->resDesc) ? $result->data[0]->record[0]->resDesc : $result->msg->codeDesc;
      // 失败时
      $oLog->error_code = 10007;
      $oLog->error_msg = $error;
      $oLog->status = BankValidLog::STATUS_FAIL;
      $res = $oLog->save();
      return $this->resp(10007, $error);
    }

    //9 保存到本地数据库中
    $result = $oBankValid->saveData($data);
    if (!$result) {
      Logger::dayLog("bankvalid", "index", $data, "错误原因", $oBankValid->errors);
      return $this->resp(10008, "系统错误");
    }
    $oLog->status = BankValidLog::STATUS_OK;
    $res = $oLog->save();

    $data['order_no'] = $params["header"]['qryBatchNo'];
    $data['plateform'] = 1;
    $oBankValidPlateform = new BankValidPlateform;
    $result_plateform = $oBankValidPlateform->saveData($data);
    if(!$result_plateform){
      Logger::dayLog("bankvalidplatefrom", "index", $data, "错误原因", $oBankValidPlateform->errors);
      return $this->resp(10008, "系统错误");
    }

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
