<?php

namespace app\modules\api\common\rongbao;

use app\models\BindBank;
use app\models\Payorder;
use app\models\rongbao\RbwithholdBindbank;
use app\models\rongbao\RbWithholdOrder;
use app\common\Logger;
use Yii;

/**
 * 融宝支付类
 * @author lubaba
 */
class CRbwithhold {

    private $oRongApi;

    private $config;

    private $rbFailCode = [
        '1002','1058','1005','1027','1020','1007','1039','1070','1053','1055',
        '1056','1057','1064','1068','1063','1062','1071','1085','2025','2020',
        '2007','3133','1174','3136','3135','3137','3138','1168','3141','1183','1158'                    
    ];

    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg     = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }

    private function getConfig($cfg) {
        $configPath = __DIR__ . "/config/{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    /**
     * 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    private function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg              = $this->getCfg($channel_id);
            $map[$channel_id] = new RbWithholdApi($cfg);
            $this->config     = $this->getConfig($cfg);
        }
        return $map[$channel_id];
    }

    public function createOrder($oPayorder) {
        //1. 数据检测
        if (empty($oPayorder)) {
            return ['res_code' => '1210001', 'res_data' =>'主订单不存在'];
        }
        $data = $oPayorder->attributes;
        $data['payorder_id'] = $data['id'];
        //2. 绑定银行卡
        $res = $this->getBindBank($data);
        if ($res['res_code'] != 0) {
            return ['res_code' => $res['res_code'], 'res_data' => $res['res_data']];
        }
        $oBind = $res['res_data'];
        $data['bind_id'] = $oBind->id;
        $data['cli_identityid'] = $oBind->cli_identityid;
        $data['status'] = Payorder::STATUS_BIND;
        //3. 字段检查是否正确
        $rbOrder = new RbWithholdOrder();
        $orderRes = $rbOrder->saveOrder($data);
        if (!$orderRes) {
            Logger::dayLog('bWithhold/createOrder', '提交数据', $data, '失败原因', $rbOrder->errors);
            return ['res_code' => '1210004', 'res_data' => '订单保存失败'];
        }
        //4. 加锁
        $lockRes = $rbOrder->saveStatus(Payorder::STATUS_DOING, '');
        if (!$lockRes) {
            return ['res_code' => '1210005', 'res_data' => '加锁状态失败'];
        }
        //5. 请求代扣接口
        $obj = $this->getApi($rbOrder['channel_id']);
        $batch_content = [1,$rbOrder->cardno,$oBind->name,$oBind->bankname,'','','私',$rbOrder->amount/100,'CNY','','',$oBind->phone,'身份证',$oBind->idcard,$rbOrder->cli_identityid,$rbOrder->cli_orderid,''];
        $notify_url = Yii::$app->request->hostInfo .'/rbwithhold/backpay/';
        // $notify_url = 'http://paytest.xianhuahua.com/rbwithhold/backpay/';
        $orderData = [
            'merchant_id'   => $this->config['merchant_id'],
            'notify_url'    => $notify_url,
            'batch_no'      => time().uniqid(),
            'batch_date'    => date('Ymd'), 
            'batch_content' =>implode(',',$batch_content)
        ];
        $rbRes = $obj->send($orderData,'single');
        //5. 同步订单状态
        if($rbRes){
            if($rbRes['result_code'] == "0000"){
                $result = $rbOrder->savePaySuccess('');
            }elseif(in_array($rbRes['result_code'],$this->rbFailCode)){
                // 失败时处理
                $result = $rbOrder->savePayFail($rbRes['result_code'],$rbRes['result_msg']);   
            }
        }
        if (in_array($rbOrder->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])) {
            //7 异步通知客户端
            $result = $oPayorder->clientNotify();

            $url = $oPayorder->clientBackurl();
            return  ['res_code' => '0', 'res_data' =>['callbackurl' => $url]];
        }elseif($rbOrder->status == Payorder::STATUS_DOING){
            $url = $oPayorder->clientBackurl();
            return  ['res_code' => '0', 'res_data' =>['callbackurl' => $url]];
        }else {
            return ['res_code' => '1210006', 'res_data' => '交易失败，请联系客服'];;
        }
        
    }

    private function getBindBank($data) {
        $oBind = (new RbwithholdBindbank)->getSameUserCard(
            $data['aid'],
            $data['channel_id'],
            $data['identityid'],
            $data['cardno']
        );
        if ($oBind) {
            return ['res_code' => 0, 'res_data' => $oBind];
        }

        return $this->bindCard($data);
    }

    private function bindCard($data) {
        //1. 保存到融宝绑卡表中
        $oBind = new RbwithholdBindbank;
        $result = $oBind->saveCard($data);
        if (!$result) {
            return ['res_code' => '1210002', 'res_data' =>'保存绑卡信息失败'];
        }
        $rbObj = $this->getApi($oBind['channel_id']);
        //2. 组合四要素等参数
        $batch_content = [1,$this->config['merchant_id'],$data['cardno'],$data['name'],$data['phone'],'身份证',$data['idcard'],$oBind->cli_identityid,date('Y-m-d'),'2099-01-01',$oBind->requestid,''];
        $rbData = [
            'merchant_id'   => $this->config['merchant_id'],
            'batch_no'      => time().uniqid(),
            'batch_date'    => date('Ymd'), 
            'batch_content' =>implode(',',$batch_content)
        ];
        $rbResult = $rbObj->send($rbData,'singlewhite');
        //5 保存结果状态
        $saveRes = $oBind->saveRspStatus($rbResult);
        if (!$saveRes) {
            $error_msg = $oBind->error_msg ? $oBind->error_msg : '更新绑卡信息失败';
            return ['res_code' => '1210003', 'res_data' => $error_msg];
        }
        if (is_array($rbResult) && $rbResult['result_code']!='0000') {
            return ['res_code' => $oBind->error_code, 'res_data' => $oBind->error_msg];
        }
        return ['res_code' => 0, 'res_data' => $oBind];
    }
     /**
     * Undocumented function
     * 余额查询接口
     * @param [type] $channel_id
     * @return void
     */
    public function acctQuery($channel_id){
        $paramArr = [
            'charset'   => 'UTF-8',
        ];
        $url = "https://agentpay.reapal.com/agentpay/balancequery";//余额请求地址
        $res = $this->getApi($channel_id)->sendquery($paramArr,$url);
        var_dump($res);
    }
}
