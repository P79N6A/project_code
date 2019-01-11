<?php
/**
 * 易宝银联四要素验证
 * 内部错误码范围 10000
 * @author lijin
 */
namespace app\modules\bankauth\common\yeepay;

use app\common\Logger;
// use app\models\yeepay\YeepayBindBank;
use app\models\yeepay\YpBindbank;
use app\models\AuthbankOrder;
use app\modules\bankauth\common\AuthBankInterface;
use app\common\Func;
use app\modules\bankauth\common\ExceptionHandler;

class YeepayServer implements AuthBankInterface
{
    /**
     * 服务id号
     */
    protected $server_id = 10;

    /**
     * 银行四联API
     */
    private $yeepayBind;

    /**
     * YeepayServer constructor.
     */
    public function __construct()
    {
        $this->yeepayBind = new YpBindbank;
    }

    /**
     * 获取此通道对应的配置
     *
     * @return yeepayApi
     */
    private function getApi()
    {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg = $is_prod ? "prod" : 'dev';
        $map = new YeepayApi($cfg);
        return $map;
    }

    private function getPayChannel_id($channel_id){
        $configPath = __DIR__ . "/../../config/config.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在");
        }
        $config = include $configPath;
        return $config['channel_id_map'][$channel_id];
    }

    /**
     * 绑定银行卡
     *
     * @param $bindcardData
     * @return array
     */
    public function requestAuth($bindcardData)
    {
        //1 数据检测
        if (empty($bindcardData)) {
            return $this->returnError('2020001');
        }
        $nData = [
            'channel_id'    => $this->getPayChannel_id($bindcardData['channelId']),
            'aid'           => $bindcardData['aid'],
            'identityid'    => $bindcardData['identityid'],
            'cardno'        => $bindcardData['cardno'],
            'bankname'      => $bindcardData['bankName'],
            'bankcode'      => $bindcardData['bankCode'],
            'idcard'        => $bindcardData['idcard'],
            'name'          => $bindcardData['username'],
            'phone'         => $bindcardData['phone'],
        ];
        // 2 是否绑过卡
        $objCard = $this->yeepayBind->getSameUserCard($nData['channel_id'], $nData['identityid'], $nData['cardno']);

        if (!$objCard) {
            // 保存到易宝绑卡记录中
            $result = $this->yeepayBind->saveCard($nData);
            $objCard = $this->yeepayBind;
            if (!$result) {
                Logger::dayLog(
                    'yeepayBindbank',
                    '记录保存失败', $this->yeepayBind->errors, $this->yeepayBind->errinfo,
                    'data', $bindcardData
                );
                return $this->returnError('2020003');
            }
        }
       
        $requestid = $objCard->requestid;
        // 加上前缀，以免不同的通道重复
        $cli_identityid = $objCard->cli_identityid;
        $ybData = [
            'requestid'     => $requestid, //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'identityid'    => $cli_identityid, //用户标识√string最长50位，商户生成的用户唯一标识
            'cardno'        =>  $objCard->cardno, //银行卡号√string
            'idcard'        =>  $objCard->idcard, //证件号√string
            'username'      =>  $objCard->name, //持卡人姓名√string
            'phone'         =>  $objCard->phone, //银行预留手机号√string
            'userip'        =>  $objCard->userip, //用户请求ip√string用户支付时使用的网络终端IP
        ];
        //6 调用绑卡接口
        $ybResult = $this->getApi()->invokebindbankcard($ybData);
        Logger::dayLog('yeepay/bindcard',$ybResult);
        if (isset($ybResult['code']) && $ybResult['code'] != 200) {
            return $this->returnError($ybResult['code'], '__timeout');
//            return ['code'=>$ybResult['code'],'data'=>'__timeout'];
        }
        //7 保存短信验证码
        $result = $objCard->saveReqStatus($ybResult);
        if(is_array($ybResult) && isset($ybResult['error_code'])){
            return ['code' => $ybResult['error_code'], 'data' => $ybResult['error_msg']];
        }
        if (!$result) {
            $errorCode = $objCard->error_code ? $objCard->error_code : 2020105;
            $errormsg = $objCard->error_msg ? $objCard->error_msg : '短信验证码保存失败';
            return $this->returnError($errorCode, $errormsg);
        }
        //8 确认绑卡操作
        #$objCard->refresh();
        $validatecode = $objCard->smscode;
        $ybResult = $this->getApi()->confirmbindbankcard($requestid, $validatecode);
        Logger::dayLog('yeepay/bindcard',$requestid,$ybResult);
        if (isset($ybResult['code']) && $ybResult['code'] != 200) {
            return ['code'=>$ybResult['code'],'data'=>'__timeout'];
        }
        //9 保存结果状态
        $result = $objCard->saveRspStatus($ybResult);
        if(is_array($ybResult) && isset($ybResult['error_code'])){
            return ['code' => $ybResult['error_code'], 'data' => $ybResult['error_msg']];
        }
        if (!$result) {
            $errorCode =$objCard->error_code ? $objCard->error_code : 2020106;
            $errormsg = $objCard->error_msg ? $objCard->error_msg : '绑卡成功状态更新失败';
            return $this->returnError($errorCode, $errormsg);
        }

        //10 保存到主绑卡表中
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
        $finalRes = [
                'code' => 200,
                'data' => [
                'cardno'        => $objCard->cardno,
                'idcard'        => $objCard->idcard,
                'username'      => $objCard->name,
                'phone'         => $objCard->phone,
                'status'        => AuthbankOrder::STATUS_SUCC,
                'channel_id'    => intval($bindcardData['channelId']),
                'from'          => 'yeepay',
            ]];
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
        //1 是否成功绑定过卡
        $objCard = $this->yeepayBind->getSameUserCard($this->getPayChannel_id($oCard ->channel_id), $identityid, $oCard ->cardno);
        if(!$objCard){
            ExceptionHandler::make_throw('2000012');
        }
        //2 更新成解除绑卡
        $result = $this->yeepayBind->updateStatus($objCard->requestid, YpBindBank::STATUS_OVER);
        if (!$result) {
            ExceptionHandler::make_throw(2020103, $this->yeepayBind->errinfo);
        }
        //2 主绑卡表解除
        $oCard->status = AuthbankOrder::STATUS_UNBIND;
        $oCard->modify_time = date('Y-m-d H:i:s');
        $res = $oCard->save();
        if (!$res) {
            ExceptionHandler::make_throw(2020104, $oCard->errinfo);
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
}
