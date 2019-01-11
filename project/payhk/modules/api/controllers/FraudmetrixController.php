<?php
namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\MfRisk;
use app\models\Risk;
use app\modules\api\common\ApiController;
use app\modules\api\common\fraudmetrix\FraudmetrixApi;
use yii\helpers\ArrayHelper;

/**
 * 短信服务
 * 内部错误码范围1000-1999
 */
class FraudmetrixController extends ApiController
{
    const TYPE_REG = 1; //注册
    const TYPE_LOG = 2; //登录

    /**
     * 服务id号
     */
    protected $server_id = 12;

    /**
     * 同盾接口文档
     */
    private $fraudmetrix;

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $env = 'prod';
        $this->fraudmetrix = new FraudmetrixApi($env);
    }

    /**
     * 借款同盾接口调用
     */
    public function actionIndex()
    {
        //1 字段检测
        $data = $this->reqData;
        if (!isset($data['account_name'])) {
            return $this->resp(10001, "用户姓名不能为空");
        }
        if (!isset($data['mobile'])) {
            return $this->resp(10002, "手机号不能为空");
        }
        if (!isset($data['id_number'])) {
            return $this->resp(10003, "身份证号不能为空");
        }
        if (!isset($data['seq_id'])) {
            return $this->resp(10004, "业务订单号不能为空");
        }
        if (!isset($data['ip_address'])) {
            return $this->resp(10005, "IP地址不能为空");
        }
        if (!isset($data['type'])) {
            return $this->resp(10005, "同盾类型");
        }

        //用户姓名
        $account_name = $data['account_name'];
        //手机号码
        $account_mobile = $data['mobile'];
        //身份证号
        $id_number = $data['id_number'];
        //业务订单号
        $seq_id = $data['seq_id'];
        //IP地址
        $ip_address = $data['ip_address'];
        //同盾类型
        $type = $data['type'];
        //设备信息的会话标识
        $token_id = isset($data['token_id']) ? $data['token_id'] : '';
        //学校
        $ext_school = isset($data['ext_school']) ? $data['ext_school'] : '';
        //学历
        $ext_diploma = isset($data['ext_diploma']) ? $data['ext_diploma'] : '';
        //入学年份
        $ext_start_year = isset($data['ext_start_year']) ? $data['ext_start_year'] : '';
        //银行卡号
        $card_number = isset($data['card_number']) ? $data['card_number'] : '';
        //申请提现金额
        $pay_amount = isset($data['pay_amount']) ? $data['pay_amount'] : '';
        //申请提现时间
        $event_occur_time = isset($data['event_occur_time']) ? $data['event_occur_time'] : '';
        //出生年
        $ext_birth_year = isset($data['ext_birth_year']) ? $data['ext_birth_year'] : '';
        //公司
        $organization = isset($data['organization']) ? $data['organization'] : '';
        //职位
        $ext_position = isset($data['ext_position']) ? $data['ext_position'] : '';
        //用于关联设备指纹（前端sdk获取到的值）
        $black_box = ArrayHelper::getValue($data,'black_box','');
        //同盾应用类型 web、android、ios
        $xhh_apps = ArrayHelper::getValue($data,'xhh_apps','web');
        

        $result = $this->fraudmetrix->riskloan($type, $account_name, $account_mobile, $id_number, $seq_id, $ip_address, $token_id, $ext_school, $ext_diploma, $ext_start_year, $card_number, $pay_amount, $event_occur_time, $ext_birth_year, $organization, $ext_position,$black_box,strtolower($xhh_apps));

        $event_type = ($type == 1) ? 'loan_web' : 'register_web';
        $url = '/ofiles/fraud/' . date('Ym/d/') . $account_mobile . '-' . $seq_id . '.json';

        $condition = array(
            'event_type' => $event_type,
            'request_id' => $seq_id,
            'account_name' => $account_name,
            'account_mobile' => $account_mobile,
            'id_number' => $id_number,
            'ext_birth_year' => $ext_birth_year,
            'ext_school' => $ext_school,
            'ext_diploma' => $ext_diploma,
            'ext_start_year' => $ext_start_year,
            'ext_industry' => '',
            'ext_position' => $ext_position,
            'organization' => $organization,
            'card_number' => $card_number,
            'pay_amount' => $pay_amount,
            'event_occur_time' => $event_occur_time,
            'final_decision' => isset($result['final_decision']) ? $result['final_decision'] : '',
            'final_score' => isset($result['final_score']) ? $result['final_score'] : 0,
            'seq_id' => isset($result['seq_id']) ? $result['seq_id'] : '',
            'url' => $url,
        );
        $risk = new Risk;
        $result_risk = $risk->addRisk($condition);

        $returnData = array(
            'rsp_code' => '0000',
            'result' => isset($result['result']) ? $result['result'] : '',
            'hit_rules' => isset($result['hit_rules']) ? $result['hit_rules'] : '',
            'geoip_info' => isset($result['geoip_info']) ? $result['geoip_info'] : '',
            'final_decision' => isset($result['final_decision']) ? $result['final_decision'] : '',
            'finalScore' => isset($result['final_score']) ? $result['final_score'] : '',
            'policy_set_name' => isset($result['policy_set_name']) ? $result['policy_set_name'] : '',
            'policy_set' => isset($result['policy_set']) ? $result['policy_set'] : '',
            'device_info' => isset($result['device_info']) ? $result['device_info'] : '',
            'rules' => isset($result['rules']) ? $result['rules'] : '',
            'seq_id' => isset($result['seq_id']) ? $result['seq_id'] : '',
            'spend_time' => isset($result['spend_time']) ? $result['spend_time'] : '',
            'success' => isset($result['success']) ? $result['success'] : '',
        );

        return json_encode($returnData);
    }

    /**
     *  花生米富_网页
     */
    public function actionHsmfweb(){
        $data = $this->reqData;
        Logger::dayLog("hsmfweb/params", "请求参数：", json_encode($data));
        $type = ArrayHelper::getValue($data, "type", 2);
        //1.验证为空的参数
        $must_parma = [];
        //注册
        if (self::TYPE_REG == $type){
            $must_parma = [
                'account_login'         => '20001',
                'account_mobile'        => '20002',
                'ip_address'            => '20003',
                'refer_cust'            => '20004',
            ];
        }
        //登录
        if (self::TYPE_LOG == $type){
            $must_parma = [
                'account_login'         => '20005',
                'ip_address'            => '20006', 
                'state'                 => '20007',
                'refer_cust'            => '20008' ,
            ];
        }
        $empty_params = $this->emptyParams($must_parma, $data);
        if (0 != $empty_params){
            return $this->respMsg($empty_params, $this->errorCode($empty_params));
        }
        //公共参数
        $public_param = ['token_id' => '20011', 'seq_id' => '20012'];
        $pub_empty_params = $this->emptyParams($public_param, $data);
        if (0 != $pub_empty_params){
            return $this->respMsg($pub_empty_params, $this->errorCode($pub_empty_params));
        }

        //2.格式数据
        $format_data = self::TYPE_REG == $type ? $this->formatRegData($data) : $this->formatSignData($data);
        $pub_format_data = $this->formatPubData($data);
        if (!is_array($pub_format_data)){
            return $this->respMsg($pub_format_data, $this->errorCode($pub_format_data));
        }

        //3.请求API
        $format_data = array_merge($format_data, $pub_format_data);
        $result = $this->fraudmetrix->regAndLog($format_data);
        if (!$result){
            return $this->respMsg("20013", $this->errorCode("20013"));
        }
        //4. 保存数据
        $event_type= (1 == $type) ? 'Register_web_20180604' : 'Login_web_20180604';
        $condition = $this->saveRisk($event_type, $data, $result);
        if (!is_array($condition)){
            return $this->respMsg($condition, $this->errorCode($condition));
        }

        $oMfRisk = new MfRisk();
        $result_risk = $oMfRisk->addRisk($condition);
        if (!$result_risk){
            return $this->respMsg("20014", $this->errorCode("20014"));
        }

        //返回数据
        $returnData = array(
            'rsp_code'              => '0000',
            'success'               => ArrayHelper::getValue($result, 'success', ''), //提交是否成功
            'rules'                 => ArrayHelper::getValue($result, 'rules', ''), //规则详情
            'seq_id'                => ArrayHelper::getValue($result, 'seq_id', ''), //最长32个字，只有英文字母和数字	本次调用产生的唯一性SequenceID
            'spend_time'            => ArrayHelper::getValue($result, 'spend_time', ''), //本次调用在服务端的执行时间
            'final_decision'        => ArrayHelper::getValue($result, 'final_decision', ''), //Accept、Review、Reject三种值 风险决策结果
            'final_score'           => ArrayHelper::getValue($result, 'final_score', ''), //风险决策分数
            'policy_set_name'       => ArrayHelper::getValue($result, 'policy_set_name', ''), //策略集名称
            'application_id'        => ArrayHelper::getValue($result, 'application_id', ''), //借款事件申请编号,若用户传入resp_detail_type字段包含application_id，则返回结果包含该信息，否则无。示例：160812002121C031C3
            'policy_name'           => ArrayHelper::getValue($result, 'policy_name', ''), //与policy_set中的policy_name内容相同，为了向前兼容
            'risk_type'             => ArrayHelper::getValue($result, 'risk_type', ''), //只含英文字符特殊符号	风险类型
            'hit_rules'             => ArrayHelper::getValue($result, 'hit_rules', ''), //包含所有的命中规则详见后面hit_rules字段
            'policy_set'            => ArrayHelper::getValue($result, 'policy_set', ''), //包含每个策略以及策略的命中规则详见后面policy_set字段
            'geoip_info'            => ArrayHelper::getValue($result, 'geoip_info', ''), //地理位置信息，若用户传入resp_detail_type字段包含geoip，则返回结果包含该信息，否则无
            'attribution'           => ArrayHelper::getValue($result, 'attribution', ''), //身份证、手机号归属地信息，若用户传入resp_detail_type字段包含attribution，则返回结果包含该信息，否则无
            'device_info'           => ArrayHelper::getValue($result, 'device_info', ''), //设备详情，若用户传入resp_detail_type字段包含device，则返回结果包含该信息，否则无
            'credit_score'          => ArrayHelper::getValue($result, 'credit_score', ''), //信用分，若用户传入resp_detail_type字段包含credit_score，则返回结果包含该信息，否则无
            'output_fields'         => ArrayHelper::getValue($result, 'output_fields', ''), //决策结果自定义输出字段,若没有使用决策结果自定义,则该项不返回

        );
        return json_encode($returnData);

    }

    /**
     * 开始查询数据
     * 使用手机号
     *
     */
    public function actionQuery()
    {
        //1 验证
        $seq_id = $this->reqData['seq_id'];
        if (!$seq_id) {
            return $this->resp(10013, "seq_id不能为空");
        }        

        //2 查找文件
        $oRisk = new Risk;
        $data = $oRisk->getBySeq($seq_id);
        if (empty($data)) {
            return $this->resp(10015, "数据为空");
        }

        //4 返回结果
        return $this->resp(0, $data);
    }


    /**
     * 格式保存数据
     * @param $event_type
     * @param array $params
     * @param array $result
     * @return array|bool
     */
    private function saveRisk($event_type, array $params, array $result){
        if (empty($params) || empty($event_type) || empty($result)){
            return "20010";
        }
        return [
                'event_type'                => $event_type, //事件类型',
                'request_id'                => ArrayHelper::getValue($params, 'seq_id', ''), //请求ID',
                'token_id'                  => ArrayHelper::getValue($params, 'token_id', ''), //JS方式对接，用于关联设备指纹',
                'black_box'                 => ArrayHelper::getValue($params, 'black_box', ''), //sdk方式对接，用于关联设备指纹',
                'resp_detail_type'          => ArrayHelper::getValue($params, 'resp_detail_type', ''), //可支持API实时返回设备或解析信息',
                'event_occur_time'          => date("Y-m-d H:i:s", time()), //事件时间',
                'account_login'             => ArrayHelper::getValue($params, 'account_login', ''), //注册账户(如昵称等默认账户名)',
                'account_mobile'            => ArrayHelper::getValue($params, 'account_mobile', ''), //注册手机',
                'account_email'             => ArrayHelper::getValue($params, 'account_email', ''), //注册邮箱',
                'id_number'                 => ArrayHelper::getValue($params, 'id_number', ''), //注册身份证',
                'account_password'          => ArrayHelper::getValue($params, 'account_password', ''), //注册密码摘要：建议先哈希加密后再提供（保证相同密码Hash值一致即可）',
                'rem_code'                  => ArrayHelper::getValue($params, 'rem_code', ''), //注册邀请码',
                'ip_address'                => ArrayHelper::getValue($params, 'ip_address', ''), //注册IP地址',
                'state'                     => ArrayHelper::getValue($params, 'state', -1), //状态校验结果（密码校验结果：0表示账户及密码一致性校验成功，1表示账户及密码一致性校验失败）',
                'refer_cust'                => ArrayHelper::getValue($params, 'refer_cust', ''), //网页端请求来源，即用户HTTP请求的refer值（JS方式对接）',
                'success'                   => (int)ArrayHelper::getValue($result, 'success', 0), //提交是否成功',
                'reason_code'               => ArrayHelper::getValue($result, 'reason_code', ''), //错误代码',
                'seq_id'                    => ArrayHelper::getValue($result, 'seq_id', ''), //本次调用的请求id，用于事后反查事件',
                'spend_time'                => ArrayHelper::getValue($result, 'spend_time', 0), //本次调用在服务端的执行时间',
                'final_decision'            => ArrayHelper::getValue($result, 'final_decision', ''), //风险评估结果（Accept无风险，通过；Review低风险，审查；Reject高风险，拒绝）',
                'final_score'               => ArrayHelper::getValue($result, 'final_score', 0), //风险系数',
                'policy_set_name'           => ArrayHelper::getValue($result, 'policy_set_name', ''), //策略集名称',
                'url'                       => ArrayHelper::getValue($result, 'url', ''), //日志URL',
                //'version'                   => '1', //版本号',
                //'create_time'               => date("Y-m-d H:i:s", time()), //保存时间',
        ];
    }

    /**
     * 验证参数
     * @param $must_data   必传参数
     * @param $verfy_data  验证参数
     * @return bool
     */
    private function emptyParams($must_data, $verfy_data){
        if (empty($must_data) || empty($verfy_data)){
            return empty($must_data) ? 20009 : 20010;
        }
        foreach($must_data as $key=>$value){
            if (empty($verfy_data[$key])){
                return $value;
            }
        }

        return 0;
    }

    /**
     * 定义错误
     * @param $code
     * @return string
     */
    private function errorCode($code){
        $data = [
            //注册
            '20001'                 => '注册账户(如昵称等默认账户名)不能为空！', //account_login
            '20002'                 => '注册手机不能为空！', //account_mobile
            '20003'                 => '注册IP地址不能为空！', //ip_address
            '20004'                 => '网页端请求来源不能为空！', //refer_cust
            //登录
            '20005'                 => '登录账户名不能为空！', //account_login
            '20006'                 => '登录IP地址不能为空！', //ip_address
            '20007'                 => '状态校验结果不能为空！', //state
            '20008'                 => '网页端请求来源不能为空！', //refer_cust
            //补充错误
            '20009'                 => '同盾类型不存在！',
            '20010'                 => '验证参数不能为空！',
            '20011'                 => '设备指纹不能为空!',
            '20012'                 => '业务订单号不能为空！',
            '20013'                 => '同盾请求失败！',
            '20014'                 => '记录数据失败',
        ];
        return $data[$code] ?: "未定义错误 ！";
    }

    /**
     * 格式注册数据
     * @param $data_set
     * @return array|string
     */
    private function formatRegData($data_set){
        if (empty($data_set)){
            return "20010";
        }

        return array_filter([
            'account_login'         => ArrayHelper::getValue($data_set, 'account_login'), //注册账户(如昵称等默认账户名)	推荐
            'account_mobile'        => ArrayHelper::getValue($data_set, 'account_mobile'), //注册手机	推荐
            'account_email'         => ArrayHelper::getValue($data_set, 'account_email'), //注册邮箱	可选
            'id_number'             => ArrayHelper::getValue($data_set, 'id_number'), //注册身份证	可选
            'account_password'      => ArrayHelper::getValue($data_set, 'account_password'), //注册密码摘要：建议先哈希加密后再提供（保证相同密码Hash值一致即可）	可选
            'rem_code'              => ArrayHelper::getValue($data_set, 'rem_code'), //注册邀请码	可选
            'ip_address'            => ArrayHelper::getValue($data_set, 'ip_address'), //注册IP地址	推荐
            'state'                 => ArrayHelper::getValue($data_set, 'state'), //状态校验结果	可选
            'refer_cust'            => ArrayHelper::getValue($data_set, 'refer_cust'), //网页端请求来源，即用户HTTP请求的refer值（JS方式对接）	推荐
            'type'                  => 1, 
        ]);
    }

    /**
     * 格式登录数据
     * @param $data_set
     * @return array|string
     */
    private function formatSignData($data_set){
        if (empty($data_set)){
            return "20010";
        }

        return array_filter([
            'account_login'     => ArrayHelper::getValue($data_set, 'account_login'), //登录账户名(推荐)
            'account_password'  => ArrayHelper::getValue($data_set, 'account_password'), //登录密码摘要：建议先哈希加密后再提供（保证相同密码Hash值一致即可）	可选
            'ip_address'        => ArrayHelper::getValue($data_set, 'ip_address'), //登录IP地址	推荐
            'state'             => ArrayHelper::getValue($data_set, 'state'), //状态校验结果（密码校验结果：0表示账户及密码一致性校验成功，1表示账户及密码一致性校验失败）	推荐
            'refer_cust'        => ArrayHelper::getValue($data_set, 'refer_cust'), //网页端请求来源，即用户HTTP请求的refer值（JS方式对接）	推荐
            'type'              => 2,
        ]);
    }

    /**
     * 公共参数
     * @param $data_set
     * @return string
     */
    private function formatPubData($data_set){
        if (empty($data_set)){
            return "20010";
        }
        return array_filter([
            'token_id'              => ArrayHelper::getValue($data_set, 'token_id'), //String	JS方式对接，用于关联设备指纹
			'black_box'             => ArrayHelper::getValue($data_set, 'black_box'), //String	sdk方式对接，用于关联设备指纹
			'resp_detail_type'      => ArrayHelper::getValue($data_set, 'resp_detail_type'), //String
			'refer_cust'            => ArrayHelper::getValue($data_set, 'refer_cust'), //String	网页端请求来源
			'event_occur_time'      => ArrayHelper::getValue($data_set, 'event_occur_time'), //日期类型	事件时间
            'seq_id'                => ArrayHelper::getValue($data_set, 'seq_id'),
        ]);
    }

    private function respMsg($res_code, $res_data, $return=false){
        // 纪录日志
        $result = $this->logInfo($res_code, $res_data);
        $returnData = array(
            'rsp_code' => $res_code,
            'rsp_data' => $res_data,
        );

        if($return){
            return $returnData;
        }

        // 若成功返回则需要加密，失败的话就不用了
        if( $res_code === 0 && $this->appData){
            $returnData['rsp_code'] = $this->apiServerCrypt->buildData($returnData['rsp_data'], $this->appData['auth_key']);
        }
        echo json_encode($returnData,JSON_UNESCAPED_UNICODE);
        exit;
    }
}
