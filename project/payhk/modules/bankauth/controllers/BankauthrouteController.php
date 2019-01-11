<?php
namespace app\modules\bankauth\controllers;

use app\models\AuthbankChannel;
use app\modules\bankauth\common\ApiController;
use app\common\Logger;
use app\modules\bankauth\common\ExceptionHandler;
use app\modules\bankauth\common\AuthBankFactory;
use app\models\AuthbankOrder;
use app\models\AuthbankChannelBank;
use app\models\CardInfoChannel;
use app\models\AuthbankCardbin;

/**
 * 鉴权请求路由
 * @author lubaba <luchao@xianhuahua.com>
 */
class BankauthrouteController extends ApiController
{
    protected $server_id = 100;

    protected $aid;

    protected static $dictBindChannels;
    /**
     * 初始化操作
     */
    public function init() {
        parent::init();
        self::$dictBindChannels = $this->getDictChannels();
    }

    /**
     * 获取鉴权通道程序入口
     * @return array
     */
    private function getDictChannels() {
        $oAuthChannel = new AuthbankChannel();
        $oAuthChannels = $oAuthChannel->getDictChannels();
        $dictChannels = $this->mapDictChannels($oAuthChannels);
        return $dictChannels;
    }
    private function mapDictChannels($oAuthChannels) {
        if (empty($oAuthChannels)) {
            ExceptionHandler::make_throw('2000006');
        }
        $dictChannelRes = [];
        foreach ($oAuthChannels as $oAuthChannel) {
            $dictChannelRes[$oAuthChannel['aid']] = [$oAuthChannel['id']];
        }
        return $dictChannelRes;
    }

    /**
     *  鉴权请求入口
     * @return json
     */
    public function actionBind()
    {
        try {
            //1  参数验证
            $postData = $this->reqData;
            Logger::dayLog('BankauthRoute', 'Bankauth/Bind', $postData);
            if (!isset($postData['username']) || empty($postData['username'])) {
                ExceptionHandler::make_throw('2000001');
            }

            if (!isset($postData['idcard']) || empty($postData['idcard'])) {
                ExceptionHandler::make_throw('2000002');
            }

            if (!isset($postData['cardno']) || empty($postData['cardno'])) {
                ExceptionHandler::make_throw('2000003');
            }

            if (!isset($postData['phone']) || empty($postData['phone'])) {
                ExceptionHandler::make_throw('2000004');
            }

//            if (!isset($postData['identityid']) || empty($postData['identityid'])) {
//                ExceptionHandler::make_throw('2000013');
//            }

            //获取 应用id
            $this->aid = $postData['aid'] = $this->appData['id'];
            //$this->aid = 1;
            //2 本地数据库校验
            $oAuthbank = new AuthbankOrder();
            $oCard = $oAuthbank->getByCardno($postData['cardno']);
            //$oCard = '';
            if ($oCard && $oCard['status'] == AuthbankOrder::STATUS_SUCC) {
                $res = $oCard->chk($postData);
                if ($res) {
                    $channel_name_ids = $this->getChannelName();
                    $from = '';
                    foreach($channel_name_ids as $key=>$value){
                        if(in_array($oCard->channel_id,$value)){
                            $from = $key;
                            break;
                        }
                    }
                    $resData = [
                        'cardno'        => $oCard->cardno,
                        'idcard'        => $oCard->idcard,
                        'username'      => $oCard->username,
                        'phone'         => $oCard->phone,
                        'status'        => $oCard->status,
                        'channel_id'    => $oCard->channel_id,
                        'from'          => $from,
                    ];
                    return $this ->resp(0, $resData);
                }
            }
            //3 通过卡号找到卡信息
            $cardInfo = $this ->getCardInfo($postData['cardno']);
            //4 通过卡信息找到支持通道((1)没有卡bin信息则直接走天行(2)不传user_id直接走天行)
            if( $cardInfo && !empty($postData['identityid']) ){
                $channels = $this ->getSupportChannel($cardInfo);
                if(!$channels){ //无可用通道到走天行
                    $channels = self::$dictBindChannels[$this->aid];
                }
            }else{
                $channels = self::$dictBindChannels[$this->aid];
            }

            //5 路由到多通道
            $postData = array_merge($postData, $cardInfo);
            $res = $this->bindRoute($channels, $postData);
            
            if (empty($res)) {
                ExceptionHandler::make_throw('2000007');
            }
            return $this ->resp(0, $res);
        } catch (\Exception $e) {
            $this->returnException($e, 'Bind');
        }
    }
    
    /**
     * 通过cardbin 找到对应银行信息
     *
     * @param string $cardno
     * @return []
     */
    private function getCardInfo($cardno)
    {
        $oCardBin = new AuthbankCardbin();
        $cardInfo = $oCardBin ->getCardBin($cardno);
        // if (!$cardInfo) {
        //     ExceptionHandler::make_throw('2000005');
        // }
        $result = [];
        if($cardInfo){
            if ($cardInfo['card_type'] == 1) // 信用卡
                $cardType = 2;
            elseif ($cardInfo['card_type'] == 0) // 储蓄卡
                $cardType = 1;
            else
                return $result;
            $result = [
                'bankName' => $cardInfo['bank_name'],
                'bankNo' => $cardInfo['bank_code'],
                'bankCode' => $cardInfo['bank_abbr'],
                'cardName' => $cardInfo['card_name'],
                'prefixValue' => $cardInfo['prefix_value'],
                'prefixLength' => $cardInfo['prefix_length'],
                'cardLength' => $cardInfo['card_length'],
                'cardType' => $cardType,
                'commonName' => $cardInfo['bank_name']
            ];
        }
        return  $result;
    }

    /**
     * 通过银行卡找到符合条件通道
     *
     * @param [] $cardInfo
     * @return bool|array
     */
    private function getSupportChannel($cardInfo)
    {
        $oBankChannel = new AuthbankChannelBank();
        $aid = $this->aid;
        $channels = $oBankChannel->supportChannel($cardInfo, $aid);
        if (!$channels) {
//            ExceptionHandler::make_throw('2000006');
            return false;
        }
        return $channels;
    }
    
    /**
     *  多通道，当超时等情况自动切换下一个通道鉴权
     *
     * @param [] $channels
     * @param [] $data
     * @return []
     */
    private function bindRoute($channels, $data)
    {
        if (!$channels) {
            ExceptionHandler::make_throw('2000006');
        }
        foreach ($channels as $key => $channelId) {
            $res = [];
            $oAuthbank = AuthBankFactory::Create($channelId);
            $data['channelId'] = $channelId;
            $res  = $oAuthbank ->requestAuth($data);
            if ($res['code'] == 200) {
                return $res['data'];
            }
//            if($res['data'] != '__timeout'){
//                break;
//            }
        }
        return [];
    }
    
    /**
     * 解绑卡
     *
     * @return void
     */
    public function actionUnbind()
    {
        try {
            //1  参数验证
            $postData = $this->reqData;
            Logger::dayLog('BankauthRoute', 'Bankauth/UnBind', $postData);

            if (!isset($postData['cardno']) || empty($postData['cardno'])) {
                ExceptionHandler::make_throw('2000003');
            }

            if (!isset($postData['identityid']) || empty($postData['identityid'])) {
                ExceptionHandler::make_throw('2000013');
            }
             //2 本地数据库校验
            $oAuthbank = new AuthbankOrder();
            $oCard = $oAuthbank->getByCardno($postData['cardno']);
            if (!$oCard || $oCard['status'] != AuthbankOrder::STATUS_SUCC) {
                ExceptionHandler::make_throw('2000012');
            }
            $channelId =$postData['channelId'] = $oCard->channel_id;
            $oChannel = AuthBankFactory::Create($channelId);
            $res = $oChannel->overAuth($oCard, $postData['identityid']);
            return $this->resp(0, '解绑成功');
        } catch (\Exception $e) {
            $this->returnException($e, 'UnBind');
        }
    }

    /**
     * 错误输出
     *
     * @param [type] $e
     * @return void
     */
    private function returnException($e, $method)
    {
        $code = $e->getCode();
        $msg   = $e->getMessage();
        Logger::dayLog( //记录日志
            'BankauthRoute',
            'Bankauth/'.$method,
            '失败代码', $code,
            '失败原因', $msg
        );
        $this->resp($code, $msg);
    }

    /**
     * 根据channel_id获取通道名称
     */
    private function getChannelName() {
        $oAuthChannel = new AuthbankChannel();
        $oAuthChannels = $oAuthChannel->getByChannelId();
        $channelName = [];
        foreach($oAuthChannels as $oAuthChannel){
            $name = $oAuthChannel['gateway'];
            $channelName[substr($name,0,strpos($name,"\\"))][] = $oAuthChannel['id'];
        }
        return $channelName;
    }

    /**
     * 获取卡bin等信息
     * @return array
     */
    public function actionCardinfo(){
        //1  参数验证
        try{
            $postData = $this->reqData;
            if (empty($postData['cardno']) && empty($postData['cardno'])) {
                ExceptionHandler::make_throw('3010100');
            }
            //获取 应用id
            $postData['aid'] = $this->appData['id'];
//            先查找本地数据库
            $cardInfo = $this ->getCardInfo($postData['cardno']);
            if(!empty($cardInfo)){
                $cardRes = $this->cardFieldMap($cardInfo);
                return $this ->resp(0, $cardRes);
            }
//            查不到调用第三方接口
            $oCardChannel = new CardInfoChannel();
            $channels = $oCardChannel->cardChannel($postData);
            if (!$channels) {
                ExceptionHandler::make_throw('3010101');
            }
            //路由到不同的通道
            $res = $this->cardInfoRoute($channels, $postData);

            if (empty($res)) {
                ExceptionHandler::make_throw('3010102'); //卡bin信息获取失败
            }
            //更新本地数据库
            $oCardBin = new AuthbankCardbin();
            $res['card_length'] = strlen($postData['cardno']);
            $saveRes = $oCardBin ->saveData($res);
            if(!$saveRes){
                Logger::dayLog(
                    'updCardInfo',
                    '记录保存失败', $oCardBin->errors, $oCardBin->errinfo,
                    '保存记录', $res
                );
            }
            return $this ->resp(0, $res);
        }catch (\Exception $e) {
            $this->returnException($e, 'cardinfo');
        }
    }

    private function cardInfoRoute($channels, $postData){
        if (!$channels) {
            ExceptionHandler::make_throw('3010101');
        }
        foreach ($channels as $key => $channelId) {
            $res = [];
            $oCardInfo = AuthBankFactory::CreateCardinfo($channelId);
            $postData['channelId'] = $channelId;
            $res  = $oCardInfo ->getCardInfo($postData);
            if ($res['code'] == 200) {
                return $res['data'];
            }
//            if($res['data'] != '__timeout'){
//                break;
//            }
        }
        return [];
    }

    private function cardFieldMap($cardInfo){
        $res['bank_name'] = $cardInfo['bankName'];
        $res['bank_code'] = $cardInfo['bankNo'];
        $res['bank_abbr'] = $cardInfo['bankCode'];
        $res['card_name'] = $cardInfo['cardName'];
        $res['prefix_value'] = $cardInfo['prefixValue'];
        $res['prefix_length'] = $cardInfo['prefixLength'];
        $res['card_length'] = $cardInfo['cardLength'];
        $res['card_type'] = $cardInfo['cardType'];
        $res['common_name'] = $cardInfo['commonName'];
        return $res;
    }
    /**
     * 成功 失败 标准输出
     *
     * @param [type] $obj
     * @return void
     */
    // private function respResult($arr)
    // {
    //     if($arr['status'] == AuthbankOrder::STATUS_SUCC){
    //         return $this->resp(0,$arr);
    //     }elseif($arr['status'] == AuthbankOrder::STATUS_FAIL){
    //         return $this->resp(-1,$arr['error_msg']);
    //     }else{
    //         ExceptionHandler::make_throw('2000008');
    //     }
    // }

    /**
     * 获取卡bin等信息
     * @return array
     */
    public function actionCardbin(){
        //1  参数验证
        try{
            $postData = $this->reqData;
            Logger::dayLog('bankauth',$postData,$this->appData['id']);
            if (empty($postData['cardno'])) {
                ExceptionHandler::make_throw('3010100');
            }
            //获取 应用id
            $postData['aid'] = $this->appData['id'];
            $cardInfo = $this ->getCardInfo($postData['cardno']);
            if(empty($cardInfo)){
                $this->resp(-1, '查询不到卡bin信息'.$postData['cardno']);
            }
            $cardRes = $this->cardFieldMap($cardInfo);
            return $this ->resp(0, $cardRes);
        }catch (\Exception $e) {
            $this->returnException($e, 'cardinfo');
        }
    }
}
