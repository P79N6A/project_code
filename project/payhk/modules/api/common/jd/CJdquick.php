<?php
namespace app\modules\api\common\jd;

use app\common\Logger;
use app\models\Payorder;
use app\models\jd\JdOrder;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\ChannelBank;

/**
 * @desc 京东快捷支付
 * @author xlj
 */
class CJdquick {

    const RES_PAYOK = 0;    #成功
    const RES_REFUND = 3;    #退款  不用
    const RES_DOING = 6;    #处理中
    const RES_PAYFAIL = 7;    #失败


    //可能为处理中的状态
    private static $handleCode = [
        '0001','EEB0058','EEB0060','EEB0061','EEB0063','EEE0002','EEE0003','EEN0015','EES0032',
    ];
    /**
     * @desc 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod160
     */
    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }
    /**
     * @desc 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    public function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new JdApi($cfg);
        }
        return $map[$channel_id];
    }

    /**
     * @desc 创建支付订单
     * @param  obj $oPayorder  主订单对象
     *          array $postData     数组
     * @return  [res_code,res_data]
     */
    public function createOrder($oPayorder,$postData) {
        //1. 数据检测
        if (empty($oPayorder)) {
            return ['res_code' => 16015, 'res_data' => '数据不完整'];
        }
        $data = $oPayorder->attributes;
        $data['payorder_id'] = ArrayHelper::getValue($data,'id','0');
        $data['callbackurl'] = ArrayHelper::getValue($postData,'callbackurl','0');
        $data['loan_id'] = ArrayHelper::getValue($postData,'loan_id','0');
        $data['interest_fee'] = ArrayHelper::getValue($postData,'interest_fee','0');
        $data['account_id'] = ArrayHelper::getValue($postData,'account_id','0');
        $data['coupon_repay_amount']  = ArrayHelper::getValue($postData,'coupon_repay_amount','0');
        //获取银行编码  京东必填
        $bank_code = (new ChannelBank())->getBankCode($data['channel_id'],ArrayHelper::getValue($data,'bankname','0'));
        if(!$bank_code){
            Logger::dayLog('jd/createOrder', '提交数据', $data, '失败原因:银行编码找不到');
            return ['res_code' => 16016, 'res_data' => '不支持此银行！'];
        }
        $data['card_bank_code']  = $bank_code;
        //2. 字段检查是否正确
        $oJdOrder = new JdOrder();
        $result = $oJdOrder->saveOrder($data);
        if (!$result) {
            Logger::dayLog('jd/createOrder', '提交数据', $data, '失败原因', $oJdOrder->errors);
            return ['res_code' => 16017, 'res_data' => '订单保存失败'];
        }

        //3. 同步主订单状态
        $result = $oPayorder->saveStatus($oJdOrder->status);
        //4. 返回下一步处理流程
        $res_data = $oJdOrder->getPayUrls();
        Logger::dayLog('jd', 'getPayUrls', $res_data);
        return ['res_code' => 0, 'res_data' => $res_data];

    }

    /**
     * 签约流程--获取短信验证码
     * @param $jdInfo  object
     *  return object
     *  V
     */
    public function jdSendSms($jdInfo){
        Logger::dayLog('jd/jdSendSms', '签约请求提交数据', $jdInfo);
        //收集请求参数
        $card_bank = $jdInfo['card_bank_code'];
        //C 信用卡 //D 借记卡
        $card_type = $jdInfo['card_type']==1?'D':$jdInfo['card_type'];
        $card_exp = '';     #信用卡有效期
        $card_cvv2 = '';    #信用卡有校验码
        $card_no = $jdInfo['cardno'];
        $card_name = $jdInfo['name'];
        $card_idtype = $jdInfo['card_idtype']==1?'I':$jdInfo['card_idtype'];
        $card_idno = $jdInfo['idcard'];
        $card_phone = $jdInfo['phone'];
        $trade_id = $jdInfo['orderid'];
        $trade_amount = $jdInfo['amount'] ;
        $trade_type = 'V';   //交易类型  V  签约
        $trade_currency ='CNY'; //货币  人民币
        $limittime = 10;    #订单在第三方的有效期  分钟
       /* echo "v.php银行==============".$card_bank."<br>";
        echo "v.php交易类型==============".$card_type."<br>";
        echo "v.php卡号=============".$card_no."<br>";
        echo "v.php姓名=============".$card_name."<br>";
        echo "v.php证件类型==============".$card_idtype."<br>";
        echo "v.php证件号==============".$card_idno."<br>";
        echo "v.php手机号==============".$card_phone."<br>";
        echo "v.php交易类型==============".$trade_type."<br>";
        echo "v.php交易号==============".$trade_id."<br>";
        echo "v.php金额==============".$trade_amount."<br>";*/
        $v_xml = (new Xml())->v_data_xml_create($card_bank,$card_type,$card_no,$card_exp,$card_cvv2,$card_name,$card_idtype,$card_idno,$card_phone,$trade_type,$trade_id,$trade_amount,$trade_currency,$limittime);
        $result = $this->getApi($jdInfo['channel_id'])->trade($v_xml);
        Logger::dayLog('jd/jdSendSms', '签约返回结果', $result);
        //返回结果
        return $result;
    }


    /**
     * @desc 支付结果
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function confirmPay($jdInfo,$validatecode) {
        //生产地址
        $trade_notice =   Yii::$app->request->hostInfo.'/jd/backpay?code='.$jdInfo['channel_id'];
        //测试地址
        if (!SYSTEM_PROD) {
            $trade_notice =  Yii::$app->request->hostInfo.'/jd/backpay?code='.$jdInfo['channel_id'];
        }
        Logger::dayLog('jd/confirmPay', '支付请求提交数据', $jdInfo, $validatecode,$trade_notice);
        //1. 增加状态锁定
        $result = $jdInfo->saveStatus(Payorder::STATUS_DOING, '');
        if (!$result) {
            #todo
            Logger::dayLog('jd/confirmPay', '修改状态失败', $result->errors);
        }

        $card_bank = $jdInfo['card_bank_code']; #
        //C 信用卡 //D 借记卡
        $card_type = $jdInfo['card_type']==1?'D':$jdInfo['card_type'];  #
        $card_no = $jdInfo['cardno'];   #
        $card_exp = '';     #信用卡有效期
        $card_cvv2 = '';    #信用卡有校验码
        $card_name = $jdInfo['name'];   #
        // I 为身份证号码
        $card_idtype = $jdInfo['card_idtype']==1?'I':$jdInfo['card_idtype'];    #
        $card_idno = $jdInfo['idcard']; #
        $card_phone = $jdInfo['phone']; #
        $trade_type = 'S';   //交易类型    S 支付
        $trade_id = $jdInfo['orderid']; #
        $trade_amount = $jdInfo['amount'] ;     #
        $trade_currency ='CNY'; //货币  人民币
        $trade_date = date('Ymd',time());        #日期 20180529
        $trade_time = date('His',time());        #时间 183000
        $trade_note = '消费';     #备注
        $trade_code = $validatecode;


        $s_xml = (new Xml())->s_data_xml_create($card_bank,$card_type,$card_no,
            $card_exp,$card_cvv2,$card_name,
            $card_idtype,$card_idno,$card_phone,
            $trade_type,$trade_id,$trade_amount,
            $trade_currency,$trade_date,$trade_time,
            $trade_notice,$trade_note,$trade_code);

       /* $cgData = [
            'card_bank' => $jdInfo->loan_id, //借款ID
            'card_type' => $jdInfo->orderid,
            'card_no' => $jdInfo->account_id,
            'card_name' => $jdInfo->payorder->idcard,
            'card_idtype' => $jdInfo->payorder->name,
            'card_idno' => $jdInfo->payorder->phone,
            'card_phone' => $jdInfo->payorder->cardno,
            'trade_id' => $jdInfo->payorder->amount / 100,// 单位元
            'trade_amount' => $jdInfo->payorder->amount,// 单位分
            'trade_currency' => $jdInfo->smsseq,
            'trade_date' => $jdInfo->smsseq,
            'trade_time' => $jdInfo->smsseq,
            'trade_notice' => $jdInfo->smsseq,
            'trade_note' => $jdInfo->smsseq,
            'trade_code' => $jdInfo->smsseq,
        ];*/
        $oJdpay = $this->getApi($jdInfo->channel_id)->trade($s_xml);
        Logger::dayLog('jd/jdSendSms', '支付请求返回结果', $oJdpay);
        //返回结果
        return $oJdpay;
    }

    /**
     * @desc 主动查询结果
     * @param  object $cgInfo  validatecode 验证码   smsSeq: 短信序号
     * @return int 支付状态.
     */
    public function JdQuery($jdInfo) {
        #Logger::dayLog('jd/JdQuery', '异常订单查询参数', $jdInfo);
        $trade_type = 'Q';  //查询
        $trade_id = $jdInfo->orderid;
        $q_xml = (new Xml())->q_data_xml_create($trade_type,$trade_id);
        $result = $this->getApi($jdInfo->channel_id)->trade($q_xml);
        #Logger::dayLog('jd/JdQuery', '异常订单查询结果', $result);
        return $result;
    }



    /**
     * $desc 处理时间内异常订单
     * @return int
     */
    public function runMinute($start_time, $end_time) {
        $model = new JdOrder();
        $dataList =$model->getAbnorList($start_time, $end_time);
        //逐条处理
        $success = 0;
        $total = count($dataList);
        if($total > 0){
            foreach ($dataList as $jdOrder) {
                $result = $this->orderQuery($jdOrder);
                if ($result){
                    $success++;
                }
            }
        }
        Logger::dayLog('jd/orderQuery', '查询成功条数：', $success);
        var_dump($success);die;
        //5 返回结果
        return $success;
    }


    /*
    * @des 京东订单查询
    * @param $baofooOrder
    * @return array
    */
    public function orderQuery($jdOrder){
        //1.条件判断
        if(($jdOrder->status != Payorder::STATUS_DOING) && ($jdOrder->status != Payorder::STATUS_BIND) ){
            Logger::dayLog('jd/orderQuery', '订单状态有误', $jdOrder);
        }
        #$jdModel = new JdOrder();
        #$jdInfo = $jdModel->getByJdId($jdOrder->id);
        #$oPayorder = $jdInfo->payorder;
        //去第三方查询
        $resPay = $this->JdQuery($jdOrder);
        if(!empty($resPay)){
            $rsp_code = (string)$resPay->RETURN->CODE;
            $rsp_msg = (string)$resPay->RETURN->DESC;
            if($rsp_code == "0000" && $rsp_msg == '成功'){
                $rsp_status = (string)$resPay->TRADE->STATUS;
                if($rsp_status == self::RES_PAYOK){
                    //成功时处理
                    $result=$jdOrder->savePaySuccess($jdOrder->orderid);
                    if(!$result){
                        Logger::dayLog('jd/orderQuery', '同步更新订单失败', $result);
                        return false;
                    }
                }
                if($rsp_status == self::RES_PAYFAIL){
                    //失败时处理
                    $result = $jdOrder->savePayFail($rsp_code,$rsp_msg);
                    if(!$result){
                        Logger::dayLog('jd/orderQuery', '同步更新订单失败', $result);
                        return false;
                    }

                }
                if($rsp_status == self::RES_DOING){
                    //失败时处理
                  return false;
                }
            }
            elseif(in_array($rsp_code,self::$handleCode)){
                Logger::dayLog('jd/orderQuery', '该订单还在处理中。', $jdOrder);
                return false;
            }else{
                $result = $jdOrder->savePayFail($rsp_code,$rsp_msg);
                if(!$result){
                    Logger::dayLog('jd/orderQuery', '同步更新订单失败', $result);
                    return false;
                }
            }
            //查询订单状态 并通知
            if(in_array($jdOrder->status, [Payorder::STATUS_PAYOK, Payorder::STATUS_PAYFAIL])){
                $resnotice = $jdOrder->payorder->clientNotify();
                if(!$resnotice){
                    Logger::dayLog('jd/orderQuery', '通知失败', $jdOrder->payorder);
                    return false;
                }
                return true;
            }
        }
        return false;
    }

}
