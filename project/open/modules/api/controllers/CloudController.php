<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/10
 * Time: 10:03
 */
namespace app\modules\api\controllers;

//use app\common\Fmdown;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\xs\XsApi;
use app\models\org\OrgApi;
use app\models\txsk\TxskServer;
//use Yii;
//use yii\web\Controller;


class CloudController extends CloudApiController{
    //private $test_data1;
//    private $test_data2;
//    public function init()
//    {
//     $this->test_data1 = [
//         'loan_id' =>  '223726494',
//         'loan_no' =>  '201710011708083450825921285',
//         'amount' =>  '1000.0000',
//         'source' =>  '1',
//         'days' =>  '28',
//         'business_type' =>  '1',
//         'create_time' =>  '2017-10-01 20:03:05',
//         'user_id' =>  '6132748',
//         'identity' =>  '52213019970708047X',
//         'mobile' =>  '18311554412',
//         'telephone' =>  '01082536986',
//         'come_from' =>  '1028' ,
//         'realname' =>  '王光富',
//         'aid' =>  '1',
//         'event' =>  'loan',
//     ];

//         $this->test_data2 = [
//             'identity_id' => mt_rand(1000000,9999999),
//             'idcard' => "150422199302021522",
//             'phone' => "13920628172",
//             'name' => "雷学浩",
//             'ip' => "222.171.242.250", //ip地址
//             'device' => "IOS100VERSION2", // 设备号
//             'source' => "1", //来源 ios,android,web,....
//             'token_id' => 'AqbqoitEQKjs-IwocoHAlys1zW-1tsRSykZcxOiEkCMh',
//             'aid' => '8',
//             'req_id' => date('YmdHis').mt_rand(10000,99999),
//             'reg_time' =>date('Y-m-d H:i:s'),//注册时间
//             'come_from' => '234',
//             // 公司与学校信息
//             'company_name' => "先花花科技(北京)有限公司", 'company_industry' => "金融行业", // 选填 行业
//             'company_position' => "程序猿", // 选填 职位
//             'company_phone' => "010-65998888", // 选填 公司电话
//             'company_address' => "海淀硅谷", // 选填 公司地址
//             'school_name' => "哈佛大学", // 选填 学校名称
//             'school_time' => "", // 选填 入学时间
//             'edu' => "本科", // 选填 本科,研究生
//
//             // gps
//             'latitude' => "122.32", 'longtitude' => "100.00", 'accuracy' => "a", 'speed' => "100", 'location' => "美国纽约州36街58号1室",
//
//             // 借款必填参数
//             'loan_id' => "9".mt_rand(100000,999999), // 借款loan_id
//             'amount' => "0", // 借款金额
//             'type'=>1,
//             'loan_days' => "0", // 借款日期
//             'cardno' => "", // 银行卡号
//             'business_type' => '',
//             'reason' => "", // 借款原因
//             'loan_time' =>'0000-00-00 00:00:00',//借款时间
//             'yy_request_id' =>'34'.mt_rand(10000,99999),
//         ];
//     }

    public function behaviors() {
        return [];
    }
    public function actionIndex() {
        echo "access forbiden";
    }

    //注册同盾数据接口
    public function actionReg()
    {
//        if (SYSTEM_PROD) {
            $postdata = $this->postdata;
//        } else {
//            $postdata = $this->test_data1;
//        }
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', $postdata);
            return $this->resp('20001', '数据异常', $postdata);
        }
        $postdata['event'] = 'reg';
        $oApi = new XsApi;
        $res = $oApi->runReg($postdata);
        if (isset($res['res_code']) && $res['res_code'] != '0') {
            Logger::dayLog('reg/runReg', '结果异常', $postdata, $res);
            return $this->resp($res['res_code'],$res['res_data'],$postdata);
        }
        $res['req_id'] = isset($postdata['req_id']) ? $postdata['req_id'] : 0;
        return $this->resp('0000', '', $res);
    }

    //借款同盾数据接口
    public function actionLoan()
    {
        // if (SYSTEM_PROD) {
        $postdata = $this->postdata;
        // } else {
        // $postdata = $this->test_data2;
        // }
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', $postdata);
            return $this->resp('20001', '数据异常', $postdata);
        }
        $postdata['event'] = 'loan';
        $oApi = new XsApi;
        $res = $oApi->runLoan($postdata);
        if (isset($res['res_code']) && $res['res_code'] != '0') {
            Logger::dayLog('loan/runLoan', '结果异常', $postdata, $res);
            return $this->resp($res['res_code'],$res['res_data'],$postdata);
        }
        $res['req_id'] = isset($postdata['req_id']) ? $postdata['req_id'] : 0;
        return $this->resp('0000', '', $res);
    }

    /**
     * 设置黑名单方法
     */
    public function actionSetblack()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('setblack', 'postdata', '数据异常', $postdata);
            return $this->resp('20001', '数据异常', $postdata);
        }
        $phone =  ArrayHelper::getValue($postdata, 'phone', '');
        $idcard =  ArrayHelper::getValue($postdata, 'idcard', '');
        if (empty($phone) && empty($idcard)) {
            Logger::dayLog('setblack', 'postdata', '数据异常', $postdata);
            return $this->resp('20002', '数据缺失', $postdata);
        }
        $oApi = new XsApi;
        $res = $oApi->setBlack($phone, $idcard);
        if (!$res) {
            return $this->resp('20010', '拉黑失败', $postdata);
        }
        return $this->resp('0000', '拉黑成功', $postdata);
    }
    /**
     * 设置黑名单方法
     */
    public function actionUnsetblack()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('unSetBlack', 'postdata', '数据异常', $postdata);
            return $this->resp('20001', '数据异常', $postdata);
        }
        $phone =  ArrayHelper::getValue($postdata, 'phone', '');
        $idcard =  ArrayHelper::getValue($postdata, 'idcard', '');
        if (empty($phone) && empty($idcard)) {
            Logger::dayLog('unSetBlack', 'postdata', '数据异常', $postdata);
            return $this->resp('20002', '数据缺失', $postdata);
        }
        $oApi = new XsApi;
        $res = $oApi->unSetBlack($phone, $idcard);
        if (!$res) {
            return $this->resp('20010', '取消失败', $postdata);
        }
        return $this->resp('0000', '取消成功', $postdata);
    }

    /**
     * [actionOrigin 天启接口]
     * @return [type] [description]
     */
    public function actionOrigin()
    {
        $postdata = $this->postdata;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('origin', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常', $postdata);
        }

        if (!isset($postdata['phone']) || !isset($postdata['idcard']) || !isset($postdata['user_id']) || !isset($postdata['aid'])) {
            Logger::dayLog('origin', 'postdata', '参数缺失', $postdata);
            return $this->error('20002', '参数缺失', $postdata);
        }
        $api = new OrgApi();
        $res = $api->runOrg($postdata);
        if (empty($res)) {
            Logger::dayLog('origin', 'res', '接口异常', $postdata);
            return $this->error('20003', '接口异常', $postdata);
        }
        return $this->success($res, $postdata);
    }

    /**
     * [actionOrigin 同盾接口]
     * @return [type] [description]
     */
    public function actionFraudmetrix()
    {
        $postdata = $this->postdata;
        // $postdata = $this->test_data;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('origin', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常', $postdata);
        }

        if (!isset($postdata['mobile']) || !isset($postdata['identity']) || !isset($postdata['user_id']) || !isset($postdata['aid'])) {
            Logger::dayLog('origin', 'postdata', '参数缺失', $postdata);
            return $this->error('20002', '参数缺失', $postdata);
        }
        $api = new XsApi();
        $res = $api->getFraudmetrix($postdata);
        if (isset($res['res_code']) && $res['res_code'] != '0') {
            return $this->error($res['res_code'], $res['res_data'], $postdata);
        }

        if (empty($res)) {
            Logger::dayLog('origin', 'res', '接口异常', $postdata);
            return $this->error('20003', '接口异常', $postdata);
        }
        return $this->success($res, $postdata);
    }

    /**
     * [actionOrigin 百度LBS接口]
     * @return [type] [description]
     */
    public function actionBaidulbs()
    {
        $postdata = $this->postdata;
        // $postdata = [
        //     'user_id'=>'7489767',
        //     'identity'=>'110105199002058411',
        //     'mobile'=>'18801263476',
        //     'telephone'=>null,
        //     'come_from'=>'6',
        //     'realname'=>'苏航',
        //     'from'=>2,
        //     'query_time'=>'2017-12-1409:42:42',
        //     'loan_no'=>date('YmdHis').mt_rand(1000000,9999999),
        //     'loan_no' => '201712151754245849441',
        //     'loan_id' => mt_rand(1000000,9999999),
        //     'business_type'=>1,
        //     'amount'=>2000,
        //     'days'=>84,
        //     'aid' => 1,
        //     'source'=>'1',
        //     'uuid'=>'123',
        // ];
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('origin', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常', $postdata);
        }

        if (!isset($postdata['mobile']) || !isset($postdata['identity']) || !isset($postdata['user_id']) || !isset($postdata['aid'])) {
            Logger::dayLog('origin', 'postdata', '参数缺失', $postdata);
            return $this->error('20002', '参数缺失', $postdata);
        }
        $api = new XsApi();
        $res = $api->getBaiduLbs($postdata);
        if (isset($res['res_code']) && $res['res_code'] != '0') {
            return $this->error($res['res_code'], $res['res_data'], $postdata);
        }

        if (empty($res)) {
            Logger::dayLog('origin', 'res', '接口异常', $postdata);
            return $this->error('20003', '接口异常', $postdata);
        }
        return $this->success($res, $postdata);
    }

    /**
     * [actionOrigin 天行学信网查询接口]
     * @return [type] [description]
     */
    public function actionTxskedu()
    {
        $postdata = $this->postdata;
        // $postdata = [
        //     'user_id'=>'7489767',
        //     'identity'=>'110105199002058411',
        //     'realname'=>'苏航',
        //     'aid'=>1,
        //     'query_time'=>'2017-12-14 09:42:42',
        // ];
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('origin', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常', $postdata);
        }

        if (!isset($postdata['realname']) || !isset($postdata['identity']) || !isset($postdata['user_id']) || !isset($postdata['aid'])) {
            Logger::dayLog('origin', 'postdata', '参数缺失', $postdata);
            return $this->error('20002', '参数缺失', $postdata);
        }
        $server = new TxskServer();
        $res = $server->getXxwEdu($postdata);
        if (isset($res['res_code']) && $res['res_code'] != '0') {
            return $this->error($res['res_code'], $res['res_data'], $postdata);
        }
        return $this->success($res, $postdata);
    }
}