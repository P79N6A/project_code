<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/16
 * Time: 17:02
 */
namespace app\modules\api\common\wsm;

use app\common\Curl;
use app\common\Http;
use app\common\Logger;
use app\common\TimeLog;
use app\models\App;
use app\models\remit\ClientNotify;
use app\models\remit\RemitNotify;
use app\models\wsm\WsmBill;
use app\models\wsm\WsmRemit;
use app\models\wsm\WsmRemitNotify;
use app\models\ZFLimit;
use app\modules\api\common\CRemitNotify;
use yii\helpers\ArrayHelper;

class CWSMRemit
{


    private $wsm_api_object;
    private $oClientNotify;

    private $wsm_remit_object;
    private $channelId;

    //=========================发送========================
    public function runRemits()
    {
        $this->wsm_api_object = new WSMApi();
        //1 统计1小时剩余的数据
        $initRet = ['total' => 0, 'success' => 0];

        //2 一次性处理最大设置为20 约(200/12(60/5分))
        $oRemit = new WsmRemit();

        $remitData = $oRemit->getInitData(100);
        if (!$remitData) {
            return $initRet;
        }
        //3 锁定状态为出款中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockRemit($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }

        //4 逐条处理
        $total = count($remitData);
        $success = 0;
        foreach ($remitData as $oRemit) {
            //1 判断条件
            $isLock = $oRemit -> lockOneRemit();
            if(!$isLock){
                continue;
            }
            //2 发送订单到微神马
            $send_wsm_state = $this->sendWsmData($oRemit);

            //3.更改订单状态
            $isDoing = false;
            if ($send_wsm_state){
                $isDoing = $oRemit -> saveToDoing();
            }
            if($isDoing){
                $success ++;
            }

        }
        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }

    /**
     * 提交数据到微神马
     * @param $data_set
     * @return int
     */
    private function sendWsmData($data_set)
    {

        //1.格式数据
        /**
         * 注：//todo可能存在问题,超时，失败，后续优化
         */
        $format_wsm_data = $this->formatWsmData($data_set, $this->wsm_api_object);

        //2.提交数据
        $send_state = $this->sendDataToWSM($format_wsm_data, $this->wsm_api_object);
        if (!empty($send_state[1]) && strtolower(trim($send_state[1])) == 'success'){
            return true;
        }
        return false;

    }

    /**
     * 1.格式数据
     * @param $data_set
     * @param $http_data
     * @return mixed
     */
    private function formatWsmData($data_set, $http_data)
    {
        $tip = json_decode($data_set['tip'], true); //附加字段
        $favorite_contacts = json_decode($data_set['favorite_contacts'], true); //联系人

        $enkeys = $http_data->getEnkeys();   //加密解密串，由微神马提供
        $arr = array(
            "mid" => (int)$http_data->getMid(), //，由微神马提供
            "cpm" => (string)$http_data->getCpm(), //，由微神马提供
            "shmyc" => (string)$http_data->getShmyc(), //商户密钥串，由微神马提供
            "shddh" => (string)ArrayHelper::getValue($data_set, 'client_id', ''),
            "qx" => (int)ArrayHelper::getValue($tip, 'qx', ''),
            "ts" => (int)ArrayHelper::getValue($tip, 'ts', ''),
            "sqje" => (int)ArrayHelper::getValue($data_set, 'settle_amount', '') * 100,
            "xm" => (string)ArrayHelper::getValue($data_set, 'realname', ''),
            "sfzh" => ArrayHelper::getValue($data_set, 'identityid', ''),
            "sjh" => ArrayHelper::getValue($data_set, 'user_mobile', ''),
            "sflx" => (string)ArrayHelper::getValue($tip, 'sflx', ''),
            "sfzzpdz" => (string)ArrayHelper::getValue($tip, 'sfzzpdz', ''),
            "dzxx" => ArrayHelper::getValue($tip, 'dzxx', ''),
            "hyzk" => ArrayHelper::getValue($tip, 'hyzk', ''),
            "jkzk" => ArrayHelper::getValue($tip, 'jkzk', ''),
            "zgxl" => ArrayHelper::getValue($tip, 'zgxl', ''),
            "yx" => ArrayHelper::getValue($tip, 'yx', ''),
            "zy" => ArrayHelper::getValue($tip, 'zy', ''),
            "gsmc" => ArrayHelper::getValue($tip, 'gsmc', ''),
            "gsdh" => ArrayHelper::getValue($tip, 'gsdh', ''),
            "lxrxm1" => ArrayHelper::getValue($favorite_contacts, 'lxrxm1', ''),
            "lxrdh1" => ArrayHelper::getValue($favorite_contacts, 'lxrdh1', ''),
            "lxrgx1" => ArrayHelper::getValue($favorite_contacts, 'lxrgx1', ''),
            "lxrxm2" => ArrayHelper::getValue($tip, 'lxrxm2', ''),
            "lxrdh2" => ArrayHelper::getValue($tip, 'lxrdh2', ''),
            "lxrgx2" => ArrayHelper::getValue($tip, 'lxrgx2', ''),
            "dkyt" =>  1,
            "yhklx" => ArrayHelper::getValue($tip, 'yhklx', ''),
            "kh" => ArrayHelper::getValue($data_set, 'guest_account', ''),
            "khh" => ArrayHelper::getValue($tip, 'khh', ''),
            "ylsjh" => ArrayHelper::getValue($data_set, 'user_mobile', ''),
            "cpmx" => ArrayHelper::getValue($tip, 'cpmx', ''),
            "jjfwxyqysj" => empty($tip['jjfwxyqysj']) ?date("Y-m-d H:i:s", time()) : $tip['jjfwxyqysj'],//居间服务协议签约时间
            "jjfwxyckdz" => empty($tip['jjfwxyckdz']) ? "" : $tip['jjfwxyckdz'], //居间服务协议查看地址
            "casqxyqysj" => ArrayHelper::getValue($tip, 'casqxyqysj', ''), //CA授权协议签约时间
            "casqxyckdz" =>  ArrayHelper::getValue($tip, 'casqxyckdz', ''), //CA授权协议查看地址
            "dkxyqysj" =>  ArrayHelper::getValue($tip, 'dkxyqysj', ''), //代扣协议签约时间
            "dkxyckdz" =>  ArrayHelper::getValue($tip, 'dkxyckdz', ''), //代扣协议查看地址
            "qyszsheng" => ArrayHelper::getValue($tip, 'qyszsheng', ''), //签约所在省
            "qyszshi" => ArrayHelper::getValue($tip, 'qyszshi', ''), //签约所在市
            "timestamp" => time(),
            "sign" => "",
            "on_line" => ArrayHelper::getValue($tip, 'on_line', ''),
            "fkxx" => ArrayHelper::getValue($data_set, 'risk_management', ''),
            "sfdf" =>  ArrayHelper::getValue($tip, 'sfdf', ''), //是否代付
            "sfdk" =>  ArrayHelper::getValue($tip, 'sfdk', ''), //是否代扣
            "zfje" =>  ArrayHelper::getValue($tip, 'zfje', ''), //支付金额
            "ktje" => ArrayHelper::getValue($tip, 'ktje', ''), //居间服务费金额
            "dgkhh" => ArrayHelper::getValue($tip, 'dgkhh', ''), //对公开户行
            "dggsmc" => ArrayHelper::getValue($tip, 'dggsmc', ''), //对公开户行公司名称
            "dgkhhbh" => ArrayHelper::getValue($tip, 'dgkhhbh', ''), //对公开户行编号
            "dgkhhkh" => ArrayHelper::getValue($tip, 'dgkhhkh', ''), //对公开户行卡号
            "dgkhhsheng" =>  ArrayHelper::getValue($tip, 'dgkhhsheng', ''), //对公开户行省
            "dgkhhshi" => ArrayHelper::getValue($tip, 'dgkhhshi', ''), //对公开户行市
            "dsfzfjybh" =>  ArrayHelper::getValue($tip, 'dsfzfjybh', ''), //第三方支付交易编号
        );
        //		flag :对公对私标识 2-对私 1-对公-----flag=2至多一个。
//		jjfwf:是否是居间服务费标识 2-不是 1-是----jjfwf==2时ybdk可为1或者2；jjfwf==1时ybdk必须为1
        //情况1：代付到C端客户，且有居间服务费
        $zfmx = json_decode($data_set['payment_details'], true);
        foreach ($zfmx AS $key => $item) {
            foreach ($item AS $k => $val) {
                if (($k == 'xm') || ($k == 'sfzh') || ($k == 'yhkh')) {
                    $item[$k] = $http_data->wsm_encrypt($val, $enkeys, $enkeys);
                }
            }
            $zfmx[$key] = $item;
        }
        $arr['zfmx'] = json_encode($zfmx, JSON_UNESCAPED_UNICODE);
        $arr ["xm"] = $http_data->wsm_encrypt($arr ["xm"], $enkeys, $enkeys);
        $arr ["sfzh"] = $http_data->wsm_encrypt($arr ["sfzh"], $enkeys, $enkeys);
        $arr ["kh"] = $http_data->wsm_encrypt($arr ["kh"], $enkeys, $enkeys);
        $arr ["ylsjh"] = $http_data->wsm_encrypt($arr ["ylsjh"], $enkeys, $enkeys);
        $arr ["sjh"] = $http_data->wsm_encrypt($arr ["sjh"], $enkeys, $enkeys);
        $arr ["gsdh"] = $http_data->wsm_encrypt($arr ["gsdh"], $enkeys, $enkeys);
        $arr ["gsmc"] = $http_data->wsm_encrypt($arr ["gsmc"], $enkeys, $enkeys);
        $arr ["lxrxm1"] = $http_data->wsm_encrypt($arr ["lxrxm1"], $enkeys, $enkeys);
        $arr ["lxrdh1"] = $http_data->wsm_encrypt($arr ["lxrdh1"], $enkeys, $enkeys);
        $arr ["lxrxm2"] = $http_data->wsm_encrypt($arr ["lxrxm2"], $enkeys, $enkeys);
        $arr ["lxrdh2"] = $http_data->wsm_encrypt($arr ["lxrdh2"], $enkeys, $enkeys);

        $arr ['sign'] = md5(sha1($arr ['mid'] . $arr ['shmyc'] . $arr ['shddh'] . $arr ['xm'] . $arr ['sfzh'] . $arr ['sjh'] . $arr ['kh'] . $arr ['sqje']) . $arr ['timestamp']);
        $json = json_encode($arr, JSON_UNESCAPED_UNICODE);
        return $json;
    }

    /**
     * 发送订单到微神马
     * @param $data_set
     * @param $send_http
     * @return mixed
     */
    private function sendDataToWSM($data_set, $send_http)
    {
        //发送订单结构
        $arr = array(
            "data"=> $data_set,
        );
        Logger::dayLog('wsm/runremits', 'sent_data',$data_set);
        $ret = $send_http->curlPost( $send_http->getSendUrl(), $arr,30,false );//发送订单
        /**
         * 返回例子
         * array (
        0 => 'HTTP/1.1 200 OK
        Date: Sat, 21 Oct 2017 10:35:19 GMT
        Server: Apache
        Content-Length: 7
        Content-Type: text/html; charset=UTF-8

        ',
        1 => '
        success',
        2 => '',
        )
         */
        Logger::dayLog('wsm/runremits', 'return_data',$ret);
        return $ret;
    }

    //=========================微神马--补单========================
    public function runQuerys()
    {
        //2 一次性处理最大设置为10
        $initRet = ['total' => 0, 'success' => 0];
        $oRemit = new WsmRemit();
        $remitData = $oRemit->getDoingData(25);
        if (!$remitData) {
            return $initRet;
        }

        //3 锁定状态为查询中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockQuery($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理
        $total = count($remitData);
        $success = 0;
        $client_id_data = [];
        foreach ($remitData as $oRemit) {
            $client_id_data[] = $oRemit['client_id'];
        }
        $query_to_wsm = $this->QueryToWSM($client_id_data);
        if (!empty($query_to_wsm)){
            foreach ($query_to_wsm as $wsmRemit) {
                $result = $this->doQuery($wsmRemit);
                if ($result) {
                    $success++;
                }
            }
        }

        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }

    /**
     * 处理单条出款
     * @param $send_query
     * @return bool
     */
    private function doQuery($send_query)
    {
        if (empty($send_query['shddh'])){
            return false;
        }
        $oRemit = new WsmRemit();
        $oRemit = $oRemit->getWsmRemitOne($send_query['shddh']);
        if (empty($oRemit)){
            return false;
        }
        //失败
        $wsm_api_object = new WSMApi();
        if (!empty($send_query['state']) && strtolower($send_query['state']) == 'error'){
            if ($wsm_api_object->isFailCode($send_query['errorcode'])) {
                $oRemit->saveToFail($send_query['errorcode'], $send_query['state']);
                $this->addNotify($oRemit, $send_query);
            }else{
                Logger::dayLog('wsm/fail', 'content',$oRemit->req_id);
            }
            return false;
        }
        //成功
        if (!empty($send_query['state']) && strtolower($send_query['state']) == 'success') {
            //$oRemit->saveRspStatus(WsmRemit::STATUS_SUCCESS, "0000", $send_query[0]['state'], 2);
            $oRemit->saveToSuccess();
        }
        //2.格式数据
        $save_state = $this->saveBillData($send_query);

        //查看通知表
        if ($save_state) {
            $this->addNotify($oRemit, $send_query);
        }

        return $save_state;
    }

    /**
     * 请求查询
     * @param $oRemit
     * @return mixed
     */
    private function sendQueryToWSM($oRemit)
    {
        $http_data = new WSMApi();

        //查询订单结构
        $arr = array(
            "data"=> json_encode($oRemit),
            //"data"=>json_encode(array('wwtest_ss_2017_10_11_15_54_12_01','wwtest_ss_2017_10_11_15_45_21_01')),
            "mid" => $http_data->getMid(),
        );
        /*
        //查询订单结构
        $arr = array(
            "data"=> json_encode(array('wwtest_ss_2017_10_11_15_54_12_01')),
            "mid" => 25,
        );
        */
        Logger::dayLog('wsm/runremits', 'query_data',$arr);
        $send_state = $http_data->curlPost($http_data->getQueryUrl(), $arr,30,false );//查询订单
        Logger::dayLog('wsm/runremits', 'query_data',$send_state);
        return json_decode($send_state[1], true);
    }

    private function saveBillData($return_data)
    {
        $data_set = [
            'shddh' => ArrayHelper::getValue($return_data, 'shddh', ''),//[资产平台系统的商户订单]商户订单号',
            'errorcode' =>  ArrayHelper::getValue($return_data, 'errorcode', ''),//empty($return_data['errorcode']) ? "" : $return_data['errorcode'], //错误码',
            'msg' => ArrayHelper::getValue($return_data, 'state', ''), //empty($return_data['state']) ? "" : $return_data['state'], //返回信息',
            'pay_time' => ArrayHelper::getValue($return_data, 'pay_time', "0000-00-00" ), //empty($return_data['pay_time']) ? "0000-00-00" : $return_data['pay_time'], //微神马订单放款时间',
            'contract_link' => ArrayHelper::getValue($return_data, 'contract_link', ''), //empty($return_data['contract_link']) ? "" : $return_data['contract_link'], //合同下载址地',
            'service_charge' => ArrayHelper::getValue($return_data, 'service_cost', 0),//empty($return_data['service_charge']) ? "" : $return_data['service_charge'], //服务利率',
            'bank_rate' => ArrayHelper::getValue($return_data, 'bank_rate', 0), //empty($return_data['bank_rate']) ? "" : $return_data['bank_rate'], //银行利率',
            'bank' => ArrayHelper::getValue($return_data, 'bank', ''), //empty($return_data['bank']) ? "" : $return_data['bank'], //放款银行',
        ];
        //Logger::dayLog('wsm/runremits_1', 'content',$data_set);
        $wsm_bill_object = new WsmBill();
        //账单只记录一次
        if (!empty($return_data['shddh'])) {
            $bill_data = $wsm_bill_object->getBillData($return_data['shddh']);
            if ($bill_data) {
                return false;
            }
        }
        //记录账单
        $bill_state = $wsm_bill_object -> saveData($data_set);
        Logger::dayLog('wsm/runremits', 'save_data',$bill_state);
        if (!$bill_state){
            return false;
        }
        return true;
    }

    /**
     * 添加一条通知数据
     * @param $result
     * @param $orderInfo
     * @return array
     */
    private function addNotify($orderInfo, $result)
    {
        $oremitnotify = new RemitNotify();
        $wsm_remit_notify_info = $oremitnotify -> getNotify($orderInfo['client_id']);
        if ($wsm_remit_notify_info){
            return true;
        }
        $notify_data = [
            'aid' => ArrayHelper::getValue($orderInfo, 'aid', 1), //项目id',
            'req_id' => ArrayHelper::getValue($orderInfo, 'req_id', 1), //请求id',
            'client_id' => ArrayHelper::getValue($orderInfo, 'client_id', 1), //请求第三方订单号',
            'tip' => ArrayHelper::getValue($result, 'state', '账单拉取数据'), //通知内容',
            'rsp_status' => ArrayHelper::getValue($result, 'errorcode', ''), //状态码',
            'remit_status' => ($result['state' ] == 'success') ? 6 : 11, //出款状态:3:处理中(暂不存在);6:成功:11:失败;',
            'remit_time' =>  ArrayHelper::getValue($result, 'pay_time', '0000-00-00'),
            'reason' => ArrayHelper::getValue($result, 'state', '账单拉取数据'), //通知失败原因:例如没有回调地址',
            'callbackurl' => ArrayHelper::getValue($orderInfo, 'callbackurl', ''), //回调地址',
            'remit_type' => 2, //出款类型0出款路由1小诺2微神马',
            'channel_id' => 6, //',
            'settle_amount' => ArrayHelper::getValue($orderInfo, 'settle_amount', 0), //出款金额',
        ];

        $state = $oremitnotify->saveData($notify_data);
        if ($state){
            return true;
        }
        Logger::dayLog('wsm/notify', 'save_data',$notify_data);
        return false;
    }

    /**
     * 添加一条通知数据
     * @param $result
     * @param $orderInfo
     * @return array
     */
    public function InputNotify($result, $orderInfo)
    {
        $notify_data = [
            'aid' => ArrayHelper::getValue($orderInfo, 'aid', 1), //项目id',
            'req_id' => ArrayHelper::getValue($orderInfo, 'req_id', 0), //请求id',
            'client_id' => ArrayHelper::getValue($orderInfo, 'client_id', 0), //请求第三方订单号',
            'tip' => ArrayHelper::getValue($result, 'msg', ''), //通知内容',
            'rsp_status' => (string)ArrayHelper::getValue($result, 'errorcode', ''), //状态码',
            'remit_status' => ($result['state' ] == 'success') ? 6 : 11, //出款状态:3:处理中(暂不存在);6:成功:11:失败;',
            'remit_time' =>  ArrayHelper::getValue($result, 'pay_time', '0000-00-00'),
            'reason' => '', //通知失败原因:例如没有回调地址',
            'callbackurl' => ArrayHelper::getValue($orderInfo, 'callbackurl', ''), //回调地址',
            'channel_id' => 6, //',
            'remit_type' => 2, //出款类型0出款路由1小诺2微神马',
            'settle_amount' => ArrayHelper::getValue($orderInfo, 'settle_amount', 0), //出款金额',
        ];
        $oremitnotify = new RemitNotify();
        $state = $oremitnotify->saveData($notify_data);
        //回调时通知一亿元

        $oNotify = new CRemitNotify();
        $ret = $oNotify->synchroNotify($oremitnotify);

        if ($state){
            return ['code' => 200, 'msg' => ''];
        }
        Logger::dayLog('wsm/wsmback', 'notify_error',json_encode($notify_data));
        return ['code' => 1030020, 'msg' => '记录通知失败！'];
    }

    //==========================微神马--拉取订单 ===================
    public function runBill()
    {
        //2 一次性处理最大设置为10
        $initRet = ['total' => 0, 'success' => 0];

        $oRemit = new WsmRemit();
        $remitData = $oRemit->getLoanData(25);
        if (!$remitData) {
            return $initRet;
        }

        //3 锁定状态为查询中
        $ids = ArrayHelper::getColumn($remitData, 'id');
        $ups = $oRemit->lockExtractQuery($ids); // 锁定出款接口的请求
        if (!$ups) {
            return $initRet;
        }
        //4 逐条处理
        $total = count($remitData);
        $success = 0;

        $client_id_data = [];
        foreach ($remitData as $oRemit) {
            $client_id_data[] = $oRemit['client_id'];
        }
        $query_to_wsm = $this->QueryToWSM($client_id_data);
        if (!empty($query_to_wsm)){
            foreach ($query_to_wsm as $wsmRemit) {
                $result = $this->doQueryBill($wsmRemit);
                if ($result) {
                    $success++;
                }
            }
        }

        //5 返回结果
        $initRet = ['total' => $total, 'success' => $success];
        return $initRet;
    }

    private function QueryToWSM($oRemit)
    {
        //1.发送查询数据
        $send_query = $this->sendQueryToWSM($oRemit);
        //超时
        if (empty($send_query)){
            $this->overTimeStatus($oRemit);
            return false;
        }
        return $send_query;

    }

    /**
     * 超时修改
     * @param $client_id_data
     * @return bool]
     */
    private function overTimeStatus($client_id_data)
    {
        if (empty($client_id_data)){
            return false;
        }
        foreach($client_id_data as $client_id) {
            $oRemit = new WsmRemit();
            $oRemit = $oRemit->getWsmRemitOne($client_id);
            $oRemit->extractStatusFail();
        }
    }

    private function doQueryBill($wsmRemit)
    {
        if (empty($wsmRemit['shddh'])){
            return false;
        }
        $oRemit = new WsmRemit();
        $oRemit = $oRemit->getWsmRemitOne($wsmRemit['shddh']);
        if (empty($oRemit)){
            return false;
        }

        $oRemit->extractStatusQuery();
        //2.格式数据
        $save_state = $this->saveBillData($wsmRemit);

        //通知表
        if ($save_state) {
            $this->addNotify($oRemit, $wsmRemit);
        }
        return $save_state;
    }

    //==========================限制判断==========================

    /**
     * 查看用户是否存在订单
     * @param $req_id
     * @return array
     */
    public function isNoOrder($req_id)
    {
        if (empty($req_id)){
            return ['code'=>1030003, 'msg'=>"[".$req_id."]不存在"];
        }
        $wsm_remit_model = new WsmRemit();
        $wsm_remit_info = $wsm_remit_model->getWsmRemitReqIdOne($req_id);
        if (!empty($wsm_remit_info)){
            return ['code'=>1030004, 'msg'=>"[".$req_id."]已提交申请"];
        }
        return ['code'=>200, 'msg'=>''];
    }

    /**
     * 时间限制
     * @param $config_object
     * @param $cur_time
     * @return array
     */
    public function timeLimit($config_object, $cur_time)
    {
        //2.每天限制时间
        $start_time = strtotime(date("Y-m-d $config_object->start_time", time()));
        $end_time = strtotime(date("Y-m-d $config_object->end_time", time()));
        if ($config_object){
            if ($start_time < $cur_time && $cur_time > $end_time){
                $msg = '每天放款开始时间：'.$config_object->start_time.'，放款结束时间：'.$config_object->end_time;
                return ['code'=>1030005, 'msg'=>$msg];
            }
        }
        //3.节假日限制时间
        $section_limit = json_decode($config_object->date_config, true);
        if (!empty($section_limit)){
            foreach($section_limit as $value){
                if ($value['st'] <= $cur_time && $value['et'] >= $cur_time){
                    return ['code'=>1030005, 'msg'=>'节假日不接受推送用户'];
                }
            }
        }
        return ['code'=>200, 'msg'=>''];
    }

    /**
     * 当天最高金额限制
     * @param $config_object
     * @param $cur_money
     * @return array
     */
    public function moneyLimit($config_object, $cur_money)
    {
        $wsm_remit_model = new WsmRemit();
        $total_money = $wsm_remit_model->getDayTotalMoney();
        if ($total_money > 0){
            $total_money = $total_money / 100;
            $wsm_remit_sum = $total_money + $cur_money / 100;
            if ($wsm_remit_sum >= $config_object->dayTopMoney){
                return ['code'=>1030007, 'msg'=>'已超过日最高限额'];
            }
        }
        return ['code'=>200, 'msg'=>''];
    }

    /**
     * 总额限制
     * @param $config_object
     * @param $cur_money
     * @return array
     */
    public function totalMoneyLimit($config_object, $cur_money)
    {
        $wsm_remit_model = new WsmRemit();
        $total_money = $wsm_remit_model->getTotalMoney();
        if ($total_money > 0){
            $wsm_remit_sum = $total_money + $cur_money;
            if ($wsm_remit_sum >= $config_object->totalMoney){
                return ['code'=>1030005, 'msg'=>'已超过放款总限额'];
            }
        }
        return ['code'=>200, 'msg'=>''];
    }

    /**
     * 周六日限制
     * @return mixed
     */
    public function weekendLimit()
    {
        $weekend = date("w", time());
        if (in_array($weekend, [0, 6])){
            return ['code'=>1030007, 'msg'=>'周六日不接受推送用户'];
        }
        return ['code'=>200, 'msg'=>''];
    }


    /**
     * 1.格式微神马需要的数据
     * @param array $data_set 传入的值
     * @return array
     */
    public function formatData(array $data_set)
    {
        //1.验证不能为空的数据,并返回对应的值
        $not_null_data = $this->notNullData();
        $empty_not_null = $this->emptyNotNull($not_null_data, $data_set);
        if ($empty_not_null['code'] != 200){
            return $empty_not_null;
        }
        //2.格式为空的值
        $maybe_null_data = $this->maybeNullData();
        $empty_defaul_null = $this->emptyDefaulNull($maybe_null_data, $data_set);
        if ($empty_defaul_null['code'] != 200){
            return $empty_defaul_null;
        }

        //返回数据
        $return_data = array_merge($empty_not_null['msg'], $empty_defaul_null['msg']);
        return ['code'=>200, 'msg'=>$return_data];
    }

    /**
     * 2.格式微神马不能为空的数据
     * @return array
     */
    public function notNullData()
    {
        $check_data = [
            //'mid'       => '商户号', // 否
            //'shmyc'     => '商户密钥串', // 否
            //'shddh'     => '商户订单号', //	否
            'req_id'    => '请求ID(业务)',
            'xm'        => '借款人姓名', //	否
            'sfzh'      => '身份证号码', //	否
            'sjh'       => '手机号', //	否
            'sflx'      => '身份类型', //	否
            'dzxx'      => '地址信息', //	否
            'hyzk'      => '婚姻状况', //	否
            'jkzk'      => '健康状况', //	否
            'zgxl'      => '最高学历', //	否
            'gsmc'      => '公司名称', //	否
            'gsdh'      => '公司电话', //	否
            'lxrxm1'    => '联系人1姓名', //	否
            'lxrdh1'    => '联系人1电话', //	否
            'lxrgx1'    => '联系人1关系', //	否
            'dkyt'      => '贷款用途', //	否
            'yhklx'     => '银行卡类型', //	否
            'kh'        => '卡号', //	否
            'khh'       => '开户行', //	否
            'sqje'      => '申请金额（分）', //	否
            //'cpm'       => '产品名', //	否
            'qx'        => '期限', //	否
            'ts'        => '天数', //天数	是
            'qyszsheng' => '签约所在省', //	否
            'qyszshi'   => '约所在市', //签	否
            'fkxx'      => '风控信息', //	否
            'on_line'   => '线上标识', //	否
            //'timestamp' => '时间戳', //	否
            //'sign'      => '数字签名', //	否
            //'zfmx'      => '支付明细', //	否
        ];
        return $check_data;
    }

    /**
     * 3.格式微神马可以为空的数据
     * @return array
     */
    public function maybeNullData()
    {
        $check_data = [
            'sfzzpdz'       => '', //身份证照片	是
            'yx'            => '', //院校	是
            'zy'            => '', //专业	是
            'lxrxm2'        => '', //联系人2姓名	是
            'lxrdh2'        => '', //联系人2电话	是
            'lxrgx2'        => '', //联系人2关系	是
            'ylsjh'         => '', //预留手机号	是
            'cpmx'          => '', //产品明细	是
            'jjfwxyqysj'    => '', //居间服务协议签约时间	是
            'jjfwxyckdz'    => '', //居间服务协议查看地址	是
            'casqxyqysj'    => '', //CA授权协议签约时间	是
            'casqxyckdz'    => '', //CA授权协议查看地址	是
            'dkxyqysj'      => '', //代扣协议签约时间	是
            'dkxyckdz'      => '', //代扣协议查看地址	是
            'sfdf'          => '', //是否代付	是
            'sfdk'          => '', //是否代扣	是
            'zfje'          => '', //支付金额	是
            'ktje'          => '', //居间服务费金额	是
            'dgkhh'         => '', //对公开户行	是
            'dggsmc'        => '', //对公开户行公司名称	是
            'dgkhhbh'       => '', //对公开户行编号	是
            'dgkhhkh'       => '', //对公开户行卡号	是
            'dgkhhsheng'    => '', //对公开户行省	是
            'dgkhhshi'      => '', //对公开户行市	是
            'dsfzfjybh'     => '', //第三方支付交易编号	是
            'callbackurl'   => '',
        ];
        return $check_data;
    }

    /**
     * (2).格式数据插入表中
     * @param array $data
     * @return array
     */
    public function foramtWsmRemitData(array $data)
    {
        //去掉多余的字段
        $fileter_data = $data;
        $fileter_key = ['req_id', 'realname', 'realname', 'identityid', 'user_mobile', 'guest_account', 'settle_amount', 'risk_management', 'favorite_contacts', 'lxrxm1', 'lxrdh1', 'lxrgx1', 'callbackurl'];
        foreach ($fileter_key as $value) {
            if (isset($fileter_data[$value])) {
                unset($fileter_data[$value]);
            }
        }

        //联系人
        $favorite_contacts = [
            'lxrxm1' => ArrayHelper::getValue($data, 'lxrxm1', ''), //empty($data['lxrxm1']) ? "" : $data['lxrxm1'], //联系人1姓名
            'lxrdh1' => ArrayHelper::getValue($data, 'lxrdh1', ''), //empty($data['lxrdh1']) ? "" : $data['lxrdh1'], //联系人1电话
            'lxrgx1' => ArrayHelper::getValue($data, 'lxrgx1', ''), //empty($data['lxrgx1']) ? "" : $data['lxrgx1'], //联系人1关系
        ];


        //银行对应关系
        $khh_code = (string)ArrayHelper::getValue($data, 'khh', ''); //empty($data['khh']) ? '' : (string)$data['khh'];
        $bank_code = $this->bankCode();
        if (empty($bank_code[$khh_code])){
            return ['code' => 1030008, 'msg' => '暂不支持该银行卡'];
        }
        $khh_code = $bank_code[$khh_code];

        $fileter_data['khh'] = $khh_code;

        //支付名细
        $zfmx_data = [
            [
                'flag' => '1',//对公对私标志，1-对公 2-对私
                'khhbh' =>  '2005',//开户行编号 参照银行对应编号列表
                'xm' => "先花信息技术（北京）有限公司",//姓名
                //'xm' => "先花花",//姓名
                'zhmc' => "招商银行东直门支行", //中国银行星海支行",//支行名称
                'yhkh' => "110911880510301", // "621xxxx",//银行卡号
                'sheng' => "北京", // "辽宁",//开户行所在省
                'shi' => "北京", //大连",//开户行所在市
                'value' => ArrayHelper::getValue($data, 'sqje', '') *  0.1 * 100, //金额（单位：分）
                'jjfwf' => 1, //是否是居间服务费标识，1-是，2-否
                'ybdk' => 1, //是否一笔打款标识， 1-是，2-否],
            ],
            [
                "flag" =>  "2",//对公对私标志，1-对公 2-对私
                "khhbh" => $khh_code,//开户行编号 参照银行对应编号列表
                "xm" => ArrayHelper::getValue($data, 'xm', ''),//empty($data['xm']) ? '' : $data['xm'],//对公开户行公司名称
                "sfzh" => ArrayHelper::getValue($data, 'sfzh', ''),//empty($data['sfzh']) ? '' : $data['sfzh'],//身份证号
                "yhkh" => ArrayHelper::getValue($data, 'kh', ''),//empty($data['kh']) ? '' : $data['kh'],//银行卡号
                "value" => ArrayHelper::getValue($data, 'sqje', '') *  0.9  * 100,//empty($data['sqje']) ? '' : $data['sqje'],//金额
                "jjfwf" => "2",//是否是居间服务费标识，1-是，2-否
                "ybdk" => "2",//是否一笔打款标识，1-是，2-否
            ],
        ];

        $data_set = [
            'req_id' => ArrayHelper::getValue($data, 'req_id', ''), //请求ID(业务)',
            //'client_id' => $shddh, //[资产平台系统的商户订单]商户订单号
            'realname' => ArrayHelper::getValue($data, 'xm', ''),//借款人姓名
            'identityid' => ArrayHelper::getValue($data, 'sfzh', ''),//身份证号码
            'user_mobile' => ArrayHelper::getValue($data, 'sjh', ''), //手机号,
            'guest_account' => ArrayHelper::getValue($data, 'kh', ''),//卡号',
            'settle_amount' => ArrayHelper::getValue($data, 'sqje', ''), //申请金额（元）',
            'favorite_contacts' => json_encode($favorite_contacts), //[json]联系人1姓名:联系人1电话:联系人1关系',
            'risk_management' => ArrayHelper::getValue($data, 'fkxx', ''), //[json]风控信息',
            'payment_details' => json_encode($zfmx_data), //[json]支付明细',
            'tip' => json_encode($fileter_data), //附加字段',
            'callbackurl' => ArrayHelper::getValue($data, 'callbackurl', ''), //异步通知回调url',
        ];
        return ['code'=>200, 'msg'=>$data_set];
    }

    /**
     * (1)时间限制
     * @param array $data_set
     * @return array
     */
    public function timeLimitInfo(array $data_set)
    {
        //1.是否存在订单
        /*
        $is_no_order = $this->isNoOrder($data_set['req_id']);
        if ($is_no_order['code'] != 200){
            return $is_no_order;
        }
        */

        $cur_time = time();
        //2.获取配置信息
        $xn_limit_model = new ZFLimit();
        $time_limit_info = $xn_limit_model->getLimitInfo();
        //3.时间限制
        $time_limit = $this->timeLimit($time_limit_info, $cur_time);
        if ($time_limit['code'] != 200){
            return $time_limit;
        }
        //4.每日限额
        $money_limit = $this -> moneyLimit($time_limit_info, $data_set['sqje']);
        if ($money_limit['code'] != 200){
            return $money_limit;
        }
        /*
        //5.总额限额
        $total_money_limit = $this -> totalMoneyLimit($time_limit_info, $data_set['sqje']);
        if ($total_money_limit['code'] != 200){
            return $total_money_limit;
        }
        */
        //6.周六日限制
        $weekend_limit = $this -> weekendLimit();
        if ($weekend_limit['code'] != 200){
            return $weekend_limit;
        }
        return ['code'=>200, 'msg'=>''];
    }

    /**
     * 银行对应关系
     * @return array
     */
    public function bankCode()
    {
        $bank_data = [
            'BOC' => '1001', //中国银行
            'ABC' => '1002', //农业银行
            'ICBC' => '1003', //工商银行
            'CCB' => '1004', //建设银行
            'BCM' => '1005', //交通银行
            'POST' => '1006', //邮政储蓄银行
            'ECITIC' => '2001', //中信银行
            'HXB' => '2003', //华夏银行
            'CMBC' => '2004', //民生银行
            'CIB' => '2006', //兴业银行
            'PINGAN' => '2008', //平安银行
            'CEB' => '2002', //光大银行
            'BOB' => '3002', //北京银行
            'CMB' => '2005', //招商银行
            //'SPDB' => '', //浦发银行
            'SHB' => '3042', //上海银行
            // '' => '', //江苏省农村信用社联合社(暂时不加)、
        ];
        return $bank_data;
    }

    /**
     * 不能为空的参数
     * @param array $value_string
     * @param array $data_set
     * @return array
     */
    public function emptyNotNull(array $value_string, array $data_set)
    {
        if (empty($value_string) || empty($data_set)){
            return ['code'=>1030001, 'msg'=>'传入的值不能为空'];
        }
        $return_data = [];
        foreach($value_string as $key => $value){
            if (empty($data_set[$key])){
                $value = $value.'不能为空！';
                return ['code'=>1030002, 'msg'=>$value];
            }
            $return_data[$key] = $data_set[$key];
        }
        return ['code'=>200, 'msg'=>$return_data];
    }

    /**
     * 如果传入的值为空就设置为空
     * @param array $value_string
     * @param array $data_set
     * @return array
     */
    public function emptyDefaulNull(array $value_string, array $data_set)
    {
        if (empty($value_string) || empty($data_set)){
            return ['code'=>1030025, 'msg'=>'传入的值不能为空'];
        }
        $return_data = [];
        foreach($value_string as $key => $value){
            if (empty($data_set[$key])){
                $return_data[$key] = '';
            }else {
                $return_data[$key] = $data_set[$key];
            }
        }
        return ['code'=>200, 'msg'=>$return_data];
    }
}
