<?php
/**
 * IP地址库
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/12
 * Time: 15:47
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ipaddr\IPAddrFactory;
use app\models\repo\IPAddrChannel;
use app\models\repo\IPRepository;
use app\models\repo\IPPlus;

class IprepoController extends CloudApiController
{
    private $oIPReposi;
    protected static $ipHandler = null;

    public function init() {
        $this->oIPReposi = new IPRepository();
    }

    /**
     * 获取并存储IP地址信息
     */
    public function actionAnalysisip() {
        # 1、参数校验
        $data = $this->post();
        $iplist = isset($data['iplist']) ? trim($data['iplist']) : '';
        if(empty($iplist)){
            return $this->returnMsg('1000001');
        }

        $ips = json_decode($iplist ,true);
        $res = [];
        foreach($ips as $ip){
            if (!$this->oIPReposi->validateIP($ip)){
                $res[$ip] = [];
                Logger::dayLog('ipaddr', 'validateip', 'ip地址非法', $ip);
                continue;
            }

            # 2、查询IP库
            $ip = trim($ip);
            $res[$ip] = $this->oIPReposi->getInfoByIP($ip);

            if($res[$ip]){
                continue;
            }

            # 3、查询埃文科技IP地址库
            $oIPPlus = new IPPlus();
            $res[$ip] = $oIPPlus->getInfoByIPSeg($ip);
            if($res[$ip]){
                //存入IP表中
                $oIPReposi = new IPRepository();
                $resIP = $oIPReposi->createData($res[$ip]);
                if (!$resIP) {
                    Logger::dayLog('ipaddr', 'saveiprepo', 'ip信息保存失败', $this->oIPReposi->errinfo);
                }
                continue;
            }

            # 4、调用第三方接口（百度API、淘宝API）
            $channels = $this ->getSupportChannel();
            $res[$ip] = $this->getIPRoute($channels, $ip);
            if(empty($res[$ip])) {
                continue;
            }
            # 5、存入IP表中
            $oIPReposi = new IPRepository();
            $resIP = $oIPReposi->createData($res[$ip]);
            if (!$resIP) {
                Logger::dayLog('ipaddr', 'saveiprepo', 'ip信息保存失败', $this->oIPReposi->errinfo);
            }
        }
        return $this->returnMsg('0', $res);
    }

    private function getSupportChannel() {
        $oIPChannel = new IPAddrChannel();
        $channels = $oIPChannel->supportChannel();
        return $channels;
    }

    /**
     * @return mixed
     */
    private function getIPRoute($channels, $ip){
        if (!$channels) {
            return $this->returnMsg('1000002');
        }
        foreach ($channels as $key => $channel) {
            try {
                self::$ipHandler = IPAddrFactory::Create($channel);
                $res =  self::$ipHandler->getIPAddress($ip);
                if(!empty($res)){
                    return $res;
                }
            } catch (\Exception $e) {
                Logger::dayLog('ipaddr', 'getiproute', $channel['name'], $ip, $e->getMessage());
            }
        }
        return [];
    }
}