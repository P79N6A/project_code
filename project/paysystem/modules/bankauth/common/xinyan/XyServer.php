<?php
/**
 *
 * 新颜 - 银行卡信息认证 API 规范 -V2.0.4
 * 版本 ： V2.0.4
 * 网址 ： http://www.xinyan.com
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/18
 * Time: 16:25
 */
namespace app\modules\bankauth\common\xinyan;
use app\common\Func;
use app\common\Logger;
use app\models\AuthbankOrder;
use app\models\xinyan\PayXyBindbank;
use app\modules\bankauth\common\AuthBankInterface;
use app\modules\bankauth\common\xinyan\library\utils\BFRSA;
use app\modules\bankauth\common\xinyan\library\utils\HttpClient;
use yii\helpers\ArrayHelper;

class XyServer implements  AuthBankInterface
{
    /**
     * 服务id号
     */
    protected $server_id = 207;

    private $oXyApi;
    public function __construct()
    {
        $this->oXyApi = new XyApi();
    }

    /**
     * 鉴权请求
     * @param $params
     * @return array
     */
    public function requestAuth($params)
    {
        //1.验证参数是否为空
        $checkParams = $this->checkParams($params);
        if ($checkParams !== true){
            return $checkParams;
        }
        //2.获取配置文件
        $config_data = $this->getConfig();
        if (!empty($save_bind_card['code'])){
            return $config_data;
        }

        //3.查看是否存在记录
        $view_the_record = $this->viewTheRecord($params);
        if ($view_the_record !== false){
            return $view_the_record;
        }

        //查找一分钟内是否有记录
        $oneMinute = $this->oneMinute($params);
        if ($oneMinute !==true){
            return $oneMinute;
        }


        //4.记录一条数据
        $save_bind_card = $this->saveBindCard($params);
        if (!empty($save_bind_card['code'])){
            return $save_bind_card;
        }

        //5.绑卡
        $call_bind_card = $this->callBindCard($save_bind_card, $config_data, $params);
        if (!empty($call_bind_card['code'])){
            return $call_bind_card;
        }

        //6.更新卡信息
        $update_card = $this->updateCard($save_bind_card, $call_bind_card);
        if ($update_card !== true){
            return $update_card;
        }
        //7.更新主表
        $update_bank_order = $this->updateBankOrder($params);
        if ($update_bank_order !== true){
            return $update_bank_order;
        }
        //8.返回数据
        return $this->returnSuccessData($save_bind_card);
    }

    /**
     * 解除绑卡
     * @param $params1
     * @param $params2
     */
    public function overAuth($params1, $params2)
    {

    }

    /**
     * 验证参数是否为空
     * @param $params
     * @return array|bool
     */
    private function checkParams($params)
    {
        $checkparams = ['username', 'channelId', 'phone', 'cardno', 'idcard'];
        $checkEmpey = $this->oXyApi->checkEmpey($params, $checkparams);
        if (!$checkEmpey){
            Logger::dayLog("xinyan/xyserver", '参数为空', json_encode($params));
            return $this->returnError('0024');
        }
        return true;
    }

    /**
     * 获取此通道对应的配置
     * @return array|bool
     * @throws \Exception
     */
    private function getConfig()
    {
        $is_prod = SYSTEM_PROD ? true : false;
        $cfg = $is_prod ? "prod" : 'dev';
        $cfg = 'prod';
        $config_data = $this->oXyApi->getConfig($cfg);
        if (!is_array($config_data) || empty($config_data)){
            Logger::dayLog("xinyan/xyserver", '配置文件为空：', json_encode($config_data));
            return $this->returnError("0025");
        }
        return $config_data;
    }

    /**
     * 查看是否存在记录
     * @param $params
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    private function viewTheRecord($params)
    {
        $oPayXyBindbank = new PayXyBindbank();
        $channel_id = ArrayHelper::getValue($params, 'channelId', '');
        $identityid = ArrayHelper::getValue($params, 'identityid', '');
        $cardno = ArrayHelper::getValue($params, 'cardno', '');
        $objCard = $oPayXyBindbank->getSameUserCard($channel_id, $identityid, $cardno);
        if (!empty($objCard)){
            return $this->returnSuccessData($objCard);
        }
        return false;
    }

    /**
     * 返回数据
     * @param $data_set
     * @return array|bool
     */
    private function returnSuccessData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $finalRes = [
            'code' => 200,
            'data' => [
                'cardno'        => ArrayHelper::getValue($data_set, 'cardno'),
                'idcard'        => ArrayHelper::getValue($data_set, 'idcard'),
                'username'      => ArrayHelper::getValue($data_set, 'name'),
                'phone'         => ArrayHelper::getValue($data_set, 'phone'),
                'status'        => AuthbankOrder::STATUS_SUCC,
                'channel_id'    => intval(ArrayHelper::getValue($data_set, 'channel_id')),
                'from'          => 'baofu',
            ]];
        return $finalRes;

    }

    /**
     * 格式发送数据
     * @param $data_set
     * @param $config_data
     * @param $params
     * @return array
     */
    private function formatHtppData($data_set, $config_data, $params)
    {
        //区分信用卡和借记卡
        $card_type = ArrayHelper::getValue($params, 'card_type', 0);
        $card_type = $card_type == 1 ? 102 : 101;
        $data_content = [
            'member_id'         => ArrayHelper::getValue($config_data, 'member_id'), // 是string 商户号 新颜提供给商户的唯一编号
            'terminal_id'       => ArrayHelper::getValue($config_data, 'terminal_id'), // 是string 终端号 新颜提供给商户的唯一终端编号
            'trans_id'          => ArrayHelper::getValue($data_set, 'requestid'), // 是string(50)商户订单号 商户订单号
            'trade_date'        => date("YmdHis", time()), // 是string(14) 交易时间 格式： yyyyMMddHHmmss
            'acc_no'            => ArrayHelper::getValue($data_set, 'cardno'), // 是string 银行卡号
            'id_card'           => ArrayHelper::getValue($data_set, 'idcard'), // 是string 证件号
            'id_type'           => 0, // 是string 证件类型 0: 身份证； 1: 军官证； 2: 护照
            'id_holder'         => ArrayHelper::getValue($data_set, 'name'), // 是string 证件姓名
            'card_type'         => $card_type, // 否string 借贷标示 借记卡： 101 ；信用卡： 102
            'mobile'            => ArrayHelper::getValue($data_set, 'phone'), // 否string 银行预留手机号
            'valid_date'        => '', // 否string 卡有效期 信用卡有效期，如： 0117 （月 + 年）
            'valid_no'          => '', // 否string CVV2 码 信用卡背面 3 位数字检验码，如： 123
            'verify_element'    => '1234', // 是 string 验证类型 12 ：两要素（银行卡号 +  姓名） 123 ：三要素（银行卡号 +  姓名 +  身份证号） 1234 ：四要素（银行卡号 +  姓名 +  身份证号 +  银行卡预留手机号）
            'product_type'      => '0', // 否 string 产品类型 0 ：默认产品类型
            'industry_type'     => 'D13', // 是 string 行业类型 详见附录： 行业类别
        ];
        $pfxpath = ArrayHelper::getValue($config_data, 'pfxpath');
        $cerpath = ArrayHelper::getValue($config_data, 'cerpath');
        $pfx_pwd = ArrayHelper::getValue($config_data, 'pfx_pwd');
        $data_type = ArrayHelper::getValue($config_data, 'data_type');
        //转换数据类型
        if($data_type == "json"){
            $data_content = str_replace("\\/", "/",json_encode($data_content));//转JSON
        }
        $oBFRSA = new BFRSA($pfxpath, $cerpath, $pfx_pwd,TRUE);
        $data_content = $oBFRSA->encryptedByPrivateKey($data_content);
        return [
            'member_id'         => ArrayHelper::getValue($config_data, 'member_id'),
            'terminal_id'       => ArrayHelper::getValue($config_data, 'terminal_id'),
            'data_type'         => $data_type,
            'data_content'      => $data_content,
        ];
    }

    /**
     * 格式保存数据
     * @param $data
     * @return array
     */
    private function formatSave($data)
    {
        if (empty($data)){
            return false;
        }
        $channel_id = ArrayHelper::getValue($data, 'channelId');
        $identityid = ArrayHelper::getValue($data, 'identityid');
        $cli_identityid  = $channel_id.'_'.$identityid;
        $userip = Func::get_client_ip();

        if (!$userip) {
            $userip = '127.0.0.1';
        }
        $request_id = "p" . $channel_id . '_' . time() . '_' . rand(10000, 99999);
        return [
            'aid'               => ArrayHelper::getValue($data, 'aid'),
            'channel_id'        => $channel_id,
            'identityid'        => $identityid,
            'cli_identityid'    => $cli_identityid,
            'requestid'         => $request_id,
            'cardno'            => ArrayHelper::getValue($data, 'cardno'),
            'bankname'          => ArrayHelper::getValue($data, 'bankName'),
            'bankcode'          => ArrayHelper::getValue($data, 'bankCode'),
            'idcardtype'        => ArrayHelper::getValue($data, 'cardType'),
            'idcard'            => ArrayHelper::getValue($data, 'idcard'),
            'name'              => ArrayHelper::getValue($data, 'username'),
            'phone'             => ArrayHelper::getValue($data, 'phone'),
            'userip'            => $userip,
        ];
    }

    /**
     * 记录一条子表的卡记录
     * @param $params
     * @return PayXyBindbank|array
     */
    private function saveBindCard($params)
    {
        $save_data = $this->formatSave($params);
        $oPayXyBindbank = new PayXyBindbank();
        $result = $oPayXyBindbank -> saveCard($save_data);
        $objCard = $oPayXyBindbank;
        if (!$result){
            Logger::dayLog('xinyan/xyserver', '保数据失败', json_encode($params));
            return $this->returnError("0023");
        }
        return $objCard;
    }

    /**
     * 调用绑卡接口
     * @param $objCard
     * @param $config_data
     * @return array\
     */
    private function callBindCard($objCard, $config_data, $params)
    {
        $formatHtppData = $this->formatHtppData($objCard, $config_data, $params);
        $authConfirmUrl = ArrayHelper::getValue($config_data, 'bankCardAuthUrl');
        Logger::dayLog("xinyan/xyserver", 'return', "绑卡请求数据：",json_encode($authConfirmUrl));
        $return = HttpClient::Post($formatHtppData, $authConfirmUrl);  //发送请求到服务器，并输出返回结果。
        Logger::dayLog("xinyan/xyserver", 'return', "绑卡返回数据：",$return);
        //$return = '{"success":true,"data":{"code":"1","desc":"亲，认证信息不一致","trans_id":"p_1513663610_87679","trade_no":"201712191523003684265256","org_code":"0001","org_desc":"持卡人身份信息有误","fee":"Y","bank_id":"ICBC","bank_description":"中国工商银行"},"errorCode":null,"errorMsg":null}';
        //$return = '{"success":true,"data":{"code":"0","desc":"亲，认证成功","trans_id":"p_1513668911_19511","trade_no":"201712191535310714265262","org_code":null,"org_desc":null,"fee":"Y","bank_id":"ICBC","bank_description":"中国工商银行"},"errorCode":null,"errorMsg":null}';
        //$return = '{"success":false,"data":null,"errorCode":"S1003","errorMsg":"订单不能重复提交"}';
        $return = json_decode($return, true);
        if (empty($return)){
            return $this->returnError("0019");
        }
        return $return;
    }

    /**
     * 更新卡表状态
     * @param $objCard
     * @param $params
     * @return array|bool
     */
    private function updateCard($objCard, $params)
    {
        $result = $objCard -> saveRspStatus($params);
        if(is_array($params) && isset($params['errorCode'])){
            Logger::dayLog("xinyan/xyserver", '绑卡错误', json_encode($params));
            return $this->returnError(ArrayHelper::getValue($params, 'errorCode'), ArrayHelper::getValue($params, 'errorMsg'));
        }
        if (!$result) {
            $errorCode =$objCard->org_code ? $objCard->org_code : 0106;
            $errormsg = $objCard->org_desc ? $objCard->org_desc : '绑卡成功状态更新失败';
            Logger::dayLog("xinyan/xyserver", '绑卡状态失败', json_encode($params));
            return $this->returnError($errorCode, $errormsg);
        }
        return true;
    }

    /**
     * 更新主表
     * @param $params
     * @return array|bool
     */
    private function updateBankOrder($params)
    {
        $oAborder = new AuthbankOrder();
        $authObj = $oAborder->getByCardno(ArrayHelper::getValue($params, 'cardno'));
        if (!$authObj) {
            $mainRes = $oAborder->savaData($params);
            if (!$mainRes) {
                Logger::dayLog("xinyan/xyserver", '记录主表失败', json_encode($params));
                return $this->returnError('0024', $oAborder->errinfo);
            }
        } else {
            $mainRes = $authObj->updateData($params, AuthbankOrder::STATUS_SUCC);
            if (!$mainRes) {
                Logger::dayLog("xinyan/xyserver", '更新主表失败', json_encode($params));
                return $this->returnError('0024');
            }
        }
        return true;
    }

    private function returnError($code, $msg='')
    {
        $data = $msg;
        if (empty($msg)){
            $responseCode = $this->oXyApi->responseCode();
            $data = empty($responseCode[$code]) ? "请求失败" : $responseCode[$code];
        }

        return ['code'=>$this->server_id.$code, 'data'=>$data];
    }

    /**
     * 判断一分钟内是否的提交过
     * @param $params
     * @return array|bool
     */
    private function oneMinute($params)
    {
        $oPayXyBindbank = new PayXyBindbank();
        $channel_id = ArrayHelper::getValue($params, 'channelId', '');
        $identityid = ArrayHelper::getValue($params, 'identityid', '');
        $cardno = ArrayHelper::getValue($params, 'cardno', '');
        $objCard = $oPayXyBindbank->getSameUserCardOne($channel_id, $identityid, $cardno);
        //时间判断
        $create_time = ArrayHelper::getValue($objCard, 'create_time', 0);
        $limit_time = time() - strtotime($create_time);
        if ($limit_time <= 60){
            return $this->returnError("0026");
        }
        return true;
    }
}