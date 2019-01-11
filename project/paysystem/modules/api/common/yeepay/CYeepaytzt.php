<?php
/**
 * 易宝API投资通服务
 * @author lijin
 */
namespace app\modules\api\common\yeepay;
use app\common\Logger;
use app\models\Payorder;
use app\models\yeepay\YpBindbank;
use app\models\yeepay\YpTztOrder;
use app\modules\api\common\yeepay\YeepayTzt;
use Yii;
use yii\helpers\ArrayHelper;
class CYeepaytzt {

    private $oTztOrder;

    public function init() {
        parent::init();
    }

    /**
     * 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod102
     */
    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }
    /**
     * 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    private function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new YeepayTzt($cfg);
        }
        return $map[$channel_id];
    }

    /**
     * 创建支付订单
     * @param  obj $oPayorder
     * @return  [res_code,res_data]
     */
    public function createOrder($oPayorder) {
        //1. 数据检测
        if (empty($oPayorder)) {
            return ['res_code' => 26031, 'res_data' => '没有提交数据！'];
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
        $this->oTztOrder=$oTztOrder = new YpTztOrder();
        $result = $oTztOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('yeepay/tzt', '提交数据', $data, '失败原因', $oTztOrder->errors);
            return ['res_code' => 26036, 'res_data' => '订单保存失败'];
        }

        //5. 同步主订单状态
        $result = $oPayorder->saveStatus($oTztOrder->status);

        //6. 返回下一步处理流程
        $res_data = $oTztOrder->getPayUrls();
        return ['res_code' => 0, 'res_data' => $res_data];
    }
    /**
     * 获取绑卡信息
     * @param  [] $data
     * @return [res_code,res_data]
     */
    private function getBindBank($data) {
        $oBind = (new YpBindbank)->getSameUserCard(
            $data['channel_id'],
            $data['identityid'],
            $data['cardno']
        );
        if ($oBind) {
            return ['res_code' => 0, 'res_data' => $oBind];
        }

        return $this->bindCard($data);
    }
    /**
     * 根据订单号进行绑卡
     * @param  [] $oPayorder
     * @return [res_code,res_data]
     */
    private function bindCard($data) {
        //1. 保存到易宝投资通绑卡表中
        $oBind = new YpBindbank;
        $result = $oBind->saveCard($data);
        if (!$result) {
            return ['res_code' => 26001, 'res_data' => '数据保存失败'];
        }

        //2. 组合四要素等参数
        $requestid = $oBind->requestid;
        // 加上前缀，以免不同的通道重复
        $cli_identityid = $oBind->cli_identityid;
        $ybData = [
            'requestid' => $requestid, //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'identityid' => $cli_identityid, //用户标识√string最长50位，商户生成的用户唯一标识
            'cardno' => $oBind['cardno'], //银行卡号√string
            'idcardno' => $oBind['idcard'], //证件号√string
            'username' => $oBind['name'], //持卡人姓名√string
            'phone' => $oBind['phone'], //银行预留手机号√string
            'userip' => $oBind['userip'], //用户请求ip√string用户支付时使用的网络终端IP
        ];

        //3. 调用绑卡接口
        /*if (defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
            $ybResult = array(
                'codesender' => 'MERCHANT',
                'merchantaccount' => '10012471228',
                'requestid' => $requestid,
                'smscode' => '627561',
            );
        } else {*/
            $ybResult = $this->getApi($oBind['channel_id'])->invokebindbankcard($ybData);
        //}
        Logger::dayLog('yeepay/tzt', '绑卡提交数据', $ybData, '易宝结果', $ybResult);
        //4. 保存短信验证码
        $result = $oBind->saveReqStatus($ybResult);
        if (!$result) {
            $error_msg = $oBind->error_msg ? $oBind->error_msg : '';
            return ['res_code' => 26002, 'res_data' => $error_msg];
        }
        //5. 确认绑卡操作
        /*if (defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
            $ybResult = array(
                'bankcode' => 'CMBCHINA',
                'card_last' => '7653',
                'card_top' => '621485',
                'merchantaccount' => '10012471228',
                'requestid' => $requestid,
            );
        } else {*/
            $validatecode = $oBind->smscode;
            $ybResult = $this->getApi($oBind['channel_id'])->confirmbindbankcard($requestid, $validatecode);
        //}
        Logger::dayLog('yeepay/tzt', '绑卡验证数据', $requestid, $validatecode,'易宝结果', $ybResult);
        //5 保存结果状态
        $result = $oBind->saveRspStatus($ybResult);
        if (!$result) {
            $error_msg = $oBind->error_msg ? $oBind->error_msg : '';
            return ['res_code' => 26002, 'res_data' => $error_msg];
        }

        return ['res_code' => 0, 'res_data' => $oBind];
    }
    /**
     * 支付结果
     * @param  object $oLianOrder
     * @return int 支付状态. 目前只可能是 4, 11(支付中, 支付失败) 和 -1 (无效)
     */
    public function pay($oTztOrder) {
        //1. 增加状态锁定
        $result = $oTztOrder->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            return -1;
        }

        /*if (defined('SYSTEM_LOCAL') && SYSTEM_LOCAL) {
            // 测试环境
            $ybResult = [
                'orderid' => $oTztOrder['orderid'],
                'yborderid' => "123432142134",
                'amount' => $oTztOrder['amount'],
            ];
        } else {*/
            $cli_orderid = $oTztOrder['cli_orderid'];
            $cli_identityid = $oTztOrder['cli_identityid'];

            $cfg = $this->getCfg($oTztOrder['channel_id']);
            $ybcallbackurl = Yii::$app->request->hostInfo . '/yeepay/tztcallurl/' . $cfg;
            

            $card_top = substr($oTztOrder->cardno, 0, 6);
            $card_last = substr($oTztOrder->cardno, -4);

            $ypData = [
                'orderid' => $cli_orderid, //     商户订单号√string商户生成的唯一订单号，最长50位
                'transtime' => strtotime($oTztOrder['create_time']), //   交易时间√int时间戳，例如：1361324896，精确到秒
                'amount' => intval($oTztOrder['amount']), //  交易金额√int以"分"为单位的整型
                'productname' => $oTztOrder['productname'], //     商品名称√string最长50位
                'productdesc' => $oTztOrder['productdesc'], //     商品描述string最长200位
                'identityid' => $cli_identityid, //      用户标识√string最长50位，商户生成的用户唯一标识
                'card_top' => $card_top, //    卡号前6位√string
                'card_last' => $card_last, //   卡号后4位√string
                'orderexpdate' => intval($oTztOrder['orderexpdate']), //    订单有效期int单位：分钟，例如：60，表示订单有效期为60分钟
                'callbackurl' => $ybcallbackurl, //     回调地址√string用来通知商户支付结果
                'userip' => $oTztOrder['userip'], //  用户请求ip√string用户支付时使用的网络终端IP
            ];

            // 生产环境 修改实际回调地址
            $ybResult = $this->getApi($oTztOrder['channel_id'])->directbindpay($ypData);
            Logger::dayLog('yeepay/tzt', '提交数据', $ypData, '易宝结果', $ybResult);
        //}

        //2. 保存结果信息
        $isError = is_array($ybResult) && isset($ybResult['error_code']);
        if ($isError) {
            // 失败时处理
            $result = $oTztOrder->savePayFail($ybResult['error_code'], $ybResult['error_msg']);
        }

        //3. 返回当前状态
        return $oTztOrder->status;
    }
    /**
     * 代扣直接支付
     * @param  obj $oPayorder 
     * @return  [res_code,res_data]
     */
    public function directpay($oPayorder){
        //1 创建易宝订单
        $res = $this->createOrder($oPayorder);
        if ($res['res_code'] != 0) {
            return ['res_code' => $res['res_code'], 'res_data' => $res['res_data']];
        }

        //2 直接支付
        $status = $this->pay($this->oTztOrder);
        if($status == Payorder::STATUS_PAYFAIL){
            return ['res_code' => 26003, 'res_data' => "支付失败了"];
        }

        // 重获取数据
        $oPayorder->refresh();
        $data =  $oPayorder->clientData();
        return ['res_code' => 0, 'res_data' => $data];
    }
    /**
     * $desc 处理时间内异常订单
     * @return int
     */
    public function runQuery($start_time, $end_time) {
        $model = new YpTztOrder();
        $dataList =$model->getAbnorList($start_time, $end_time);
        //var_dump($dataList);die;
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $oTztOrder) {
                $result = $this->queryorder($oTztOrder);
                if (isset($result['res_code']) && $result['res_code'] == 0) 
                    $success++;
            }
        }
        //5 返回结果
        return $success;
    }
    /**
     * Undocumented function
     * 查询订单接口
     * @param [type] $oTztOrder
     * @return void
     */
    public function queryorder($oTztOrder){
        //判断订单状态
        // if($oTztOrder->status!=Payorder::STATUS_DOING) return false;
        $cli_orderid = $oTztOrder->cli_orderid;
        $ybResult = $this->getApi($oTztOrder['channel_id'])->queryorder($cli_orderid);
        Logger::dayLog('yeepay/tzt', '提交数据', $cli_orderid, '易宝结果', $ybResult);

        if(empty($ybResult)) return false;
        //如果订单不存在 返回结果 array ('error_code' => 600072,'error_msg' => '订单不存在',)
        $error_code = ArrayHelper::getValue($ybResult, 'error_code', '');
        $error_msg = ArrayHelper::getValue($ybResult, 'error_msg', '');
        if(!empty($error_code) && $error_code=='600072'){
            $result = $oTztOrder->savePayFail($error_code, $error_msg);
            $oTztOrder->payorder->clientNotify();
            return ['res_code'=>$error_code,'res_data'=>$error_msg];
        }
        if(empty($ybResult['orderid'])) return false;
        $oTzt = (new YpTztOrder)->getByCliOrderId($ybResult['orderid']);
        if (empty($oTzt)) {
            Logger::dayLog('yeepay/tzt','queryorder',$cli_orderid,'数据库中无orderid', $ybResult);
            return false;
        }
        // 检测本地已经是支付成功状态了，则没必要再处理一次
        $resultInfo = [];
        if (!$oTzt->is_finished()) {
            // 获取查询状态对应关系
            $status = $oTzt->syncStatus($ybResult['status']);
            if (Payorder::STATUS_PAYOK == $status) {
                // 成功处理逻辑
                $yborderid = (string) $ybResult['yborderid'];
                $result = $oTzt->savePaySuccess($yborderid);
                $resultInfo =  ['res_code'=>0,'res_data'=>'操作成功'];

            } elseif (Payorder::STATUS_PAYFAIL == $status || Payorder::STATUS_CANCEL==$status) {
                // 失败处理逻辑
                $errorcode = ArrayHelper::getValue($ybResult, 'errorcode', '');
                $errormsg = ArrayHelper::getValue($ybResult, 'errormsg', '');
                $yborderid = ArrayHelper::getValue($ybResult, 'yborderid', '');
                $result = $oTzt->savePayFail($errorcode, $errormsg,$yborderid);
                $resultInfo =  ['res_code'=>$errorcode,'res_data'=>$errormsg];
            }
        }
        // 通知客户端
        $oTzt->payorder->clientNotify();
        return $resultInfo;
    }
}
