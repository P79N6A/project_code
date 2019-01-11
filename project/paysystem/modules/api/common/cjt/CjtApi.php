<?php

namespace app\modules\api\common\cjt;
use app\common\Curl;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use app\modules\api\common\cjt\Util;
/**
 * 畅捷通支付类
 */
class CjtApi {

    private $config;
    private $object;

    public function __construct($cfg) {
        // 获取配置文件
        $this->config = $this->getConfig($cfg);
        $this->object = new Util($this->config);
    }
    
    /**
     * 获取配置文件
     * @param  str $env
     * @param  str $aid
     * @return   []
     */
    private function getConfig($cfg) {
        $configPath = __DIR__ . "/config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }
    /**
     * Undocumented function
     * 三合一认证签约代扣接口
     * @param [type] $oPayorder
     * @return void
     */
    public function pay($oPayorder){
        $nowDate = date('YmdHis',time());
        $account_type = '00';
        if($oPayorder->card_type=='1'){
            $account_type = '00';//银行卡
        }else if($oPayorder->card_type=='2'){
            $account_type = '02';//信用卡
        }
        $data = [
            'message'=>[
                'info'=>[
                    'trx_code'  => 'G10015',
                    'version'   => $this->config['version'],
                    'merchant_id'=> $this->config['merchant_id'],
                    'req_sn'    => $this->config['merchant_id'].substr($oPayorder->orderid,2),
                    'timestamp' => $nowDate,
                    'signed_msg'=> '',
                ],
                'body'=>[
                    'business_code' => $this->config['business_code'],
                    'validate_mode' => 'V003',
                    'corp_acct_no'  => $this->config['corp_acct_no'],
                    'product_code'  => $this->config['product_code'],
                    'account_prop'  => '0',
                    'bank_general_name' => $oPayorder->bankname,
                    'account_no'    => $oPayorder->cardno,
                    'account_name'  => $oPayorder->name,
                    'currency'      => 'CNY',
                    'amount'        => $oPayorder->amount,
                    'id_type'       => '0',
                    'id'            => $oPayorder->idcard,
                    'tel'           => $oPayorder->phone,
                    'account_type'  => $account_type,
                    'protocol_no'   => $oPayorder->orderid
                ]
            ]
            
            
        ];
        //var_dump($data);
        $result = $this->object->createXml($data);
        return $result;
    }

    public function cjBalance(){//畅捷余额查询
        $nowDate = date('YmdHis');
        $data = [
            'message'=>[
                'info'=>[
                    'trx_code'  => 'G20015',
                    'version'   => $this->config['version'],
                    'merchant_id'=> $this->config['merchant_id'],
                    'req_sn'    => $this->config['merchant_id'].time(),
                    'timestamp' => $nowDate,
                    'signed_msg'=> '',
                ],
                'body'=>[
                    'corp_account_no'  => $this->config['corp_acct_no']
                ]
            ]
            
        ];
        //var_dump($data);
        $result = $this->object->createXml($data);
        return $result;
    }


    public function statement($downTime){//畅捷对账文件下载
        $nowDate = date('YmdHis');
        if(!$downTime){
            $downTime = date('Ymd');
        }
        
        $data = [
            'message'=>[
                'info'=>[
                    'trx_code'  => 'G40003',
                    'version'   => $this->config['version'],
                    'merchant_id'=> $this->config['merchant_id'],
                    'req_sn'    => $this->config['merchant_id'].time(),
                    'timestamp' => $nowDate,
                    'signed_msg'=> '',
                ],
                'body'=>[
                    'bill_type' => '00',
                    // 'bill_month' => 'yyyyMM',//月
                    'bill_day' => $downTime,//日
                ]
            ]
            
        ];
        //var_dump($data);
        $result = $this->object->createXml($data);
        return $result;
    }

    /**
     * Undocumented function
     * 订单交易查询
     * @param [type] $cjtOrder
     * @return void
     */
    public function orderQuery($cjtOrder){
        $nowDate = date('YmdHis',time());
        $req_sn = empty($cjtOrder->other_orderid)?$this->config['merchant_id'].$cjtOrder->cli_orderid:$cjtOrder->other_orderid;
        $data = [
            'message'=>[
                'info'=>[
                    'trx_code'  => 'G20001',
                    'version'   => $this->config['version'],
                    'merchant_id'=> $this->config['merchant_id'],
                    'req_sn'    => $nowDate.uniqid('cjt'),
                    'timestamp' => $nowDate,
                    'signed_msg'=> '',
                ],
                'body'=>[
                    'QRY_REQ_SN' => $req_sn,
                  
                ]
            ]
            
            
        ];
        //var_dump($data);die;
        $result = $this->object->createXml($data);
        return $result;
    }
    
}
