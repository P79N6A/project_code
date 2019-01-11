<?php

/**
 * 7-14黑名单设置
 */
namespace app\modules\sfapi\controllers;

use Yii;
use yii\helpers\ArrayHelper;

use app\common\Logger;
use app\modules\sfapi\logic\CreditLogic;
use app\modules\sfapi\common\JavaCrif;
class CreditController extends ApiController
{
    private $test_data;
    // public function init()
    // {
    //     $this->test_data = [
    //     'identity_id' => '2599989',// 一亿元 user_id 
    //     'idcard' => "330124198005222315",
    //     'phone' => "18368007878", 
    //     'name' => "周平", 
    //     'ip' => "192.168.1.2", //ip地址
    //     'device' => "IOS100VERSION2", // 设备号
    //     'source' => "1", //来源 ios,android,web,....
    //     'token_id' => 'AqbqoitEQKjs-IwocoHAlys1zW-1tsRSykZcxOiEkCMh',
    //     'aid' => '10',
    //     'req_id' => date('YmdHis').mt_rand(10000,99999),
    //     'reg_time' =>date('Y-m-d H:i:s'),//注册时间
    //     'come_from' => '2',
    //     // 公司与学校信息
    //     'company_name' => "先花花科技(北京)有限公司", 
    //     'company_industry' => "金融行业", // 选填 行业
    //     'company_position' => "程序猿", // 选填 职位
    //     // 'company_phone' => "010-65998888", // 选填 公司电话
    //     'company_address' => "海淀硅谷", // 选填 公司地址
    //     'school_name' => "哈佛大学", // 选填 学校名称
    //     'school_time' => "", // 选填 入学时间
    //     'edu' => "本科", // 选填 本科,研究生
    //     // gps
    //     'latitude' => "122.32", // 维度
    //     'longtitude' => "100.00", // 经度
    //     'accuracy' => "a", // 精度
    //     'speed' => "100", //速度 
    //     'location' => "美国纽约州36街58号1室", //地址
    //     'yy_request_id' =>'10707369',//运营商报告请求ID
    //     'relation' =>'[{"name":"123","mobile":"18810719875","relation":1},{"name":"234","mobile":"15311160882","relation":4}]', //用户常用联系人
    //     //借款必填参数
    //     // 'loan_id' => "9".mt_rand(100000,999999), // 借款loan_id
    //     // 'amount' => "100", // 借款金额
    //     // 'type'=>1,
    //     // 'add_url'=>'123',
    //     // 'loan_days' => "7", // 借款日期
    //     // 'cardno' => "6225880106057653", // 银行卡号
    //     // 'business_type' => '123',
    //     // 'reason' => "买车买房", // 借款原因
    //     // 'loan_time' =>date('Y-m-d H:i:s'),//借款时间
    //     ];
    // }

    //智融钥匙申请注册决策
    public function actionReg()
    {
        $postdata = $this->postdata;
        // $postdata = $this->test_data;
        if (!is_array($postdata) || empty($postdata)) {
            Logger::dayLog('postdata', 'postdata', '数据异常', $postdata);
            return $this->error('20001', '数据异常', $postdata, 1);
        }
        //获取数据
        $reg_Logic = new CreditLogic();
        $res = $reg_Logic->applyreg($postdata);
        $res_data = $reg_Logic->info;
        if (!$res) {
            return $this->error('20002', $res_data, $postdata, 1);
        }
        //请求反欺诈决策系统
        $process_code = JavaCrif::PRO_CODE_REG;
        $res_data['aid'] = $postdata['aid'];
        $res = $reg_Logic->queryCrif($process_code,$res_data);
        $res_status = $reg_Logic->info;
        if (!$res) {
            return $this->error('20003', $res_status, $postdata, 1);
        }
        return $this->success($postdata, '', $res_status);
    }
}