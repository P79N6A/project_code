<?php
namespace app\modules\api\controllers;

use app\models\Risk;
use app\modules\api\common\ApiController;
use app\modules\api\common\fraudmetrix\FraudmetrixApi;

/**
 * 短信服务
 * 内部错误码范围1000-1999
 */
class FraudmetrixController extends ApiController
{

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

        $result = $this->fraudmetrix->riskloan($type, $account_name, $account_mobile, $id_number, $seq_id, $ip_address, $token_id, $ext_school, $ext_diploma, $ext_start_year, $card_number, $pay_amount, $event_occur_time, $ext_birth_year, $organization, $ext_position);

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
            'result' => isset($result['result']) ? $result['result'] : $result['final_decision'],
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
}
