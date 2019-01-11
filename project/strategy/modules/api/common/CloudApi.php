<?php
/**
 * 请求同盾类
 */
namespace app\modules\api\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Curl;
use app\common\ApiSign;

use app\models\Request;
use app\models\Loan;
use app\models\cloud\DcBasic;
use app\models\cloud\DcLoan;
use app\models\cloud\BlackIdcard;
use app\models\cloud\BlackPhone;
use app\models\cloud\MultiIdcard;
use app\models\cloud\MultiPhone;
use app\models\cloud\DeviceUser;
use app\models\cloud\DcOrigin;
use app\models\cloud\DcTxskedu;
use app\models\cloud\DcBaiduprea;
use app\models\cloud\DcBaidurisk;
use app\models\cloud\DcForeignBlackIdcard;
use app\models\cloud\DcForeignBlackPhone;
use app\models\cloud\DcMultiSplit;

class CloudApi {
    private $cloud_url;

    function __construct()
    {
        if (SYSTEM_PROD) {
            $this->cloud_url = "http://100.112.35.139:8082/api/cloud/";
        } else {
            $this->cloud_url = "http://182.92.80.211:8082/api/cloud/";
        }
    }
    //请求同盾数据(对外)
    public function cloudApi($data,$url)
    {
        //请求cloud获取同盾数据
        $rsp_data = $this->queryCloud($data,$url);
        if (empty($rsp_data)) {
            Logger::dayLog('api/queryCloud','queryCloud is failed',$data,$url);
        }
        //关联request表
        if ($rsp_data) {
            $res = (new request)->bindRequest($data,$rsp_data);
        }
        //获取用户cloud数据
        $cloud_info = $this->getBlackInfo($data);
        // if ($url == 'loan') {
            //获取借款多投数据
            $cloud_info += $this->getMultiInfo($data);
            //获取借款高频数据
            $cloud_info += $this->getMultiLoan($data);
        // } elseif ( $url == 'reg') {
            $cloud_info += $this->getDeviceNum($data);
        // }
        $cloud_info += $this->getMultiSplit($data);
        return $cloud_info;
    }

    //请求cloud系统（对内）
    public function queryCloud($postdata,$url)
    {
        $c_url = $this->cloud_url.$url;
        $curl = new Curl();
        $sign_data = (new ApiSign)->signData($postdata,1);
        $ret = $curl->post($c_url,$sign_data);
        $res = json_decode($ret,true);
        if (empty($res)) {
            Logger::dayLog('api/queryCloud','获取数据失败',$ret,$c_url,$postdata);
            return [];
        }
        $isVerify = (new ApiSign)->verifyCloud($res['data'], $res['_sign']);
        if (!$isVerify) {
            Logger::dayLog('api/queryCloud','验签失败',$res,$c_url,$postdata);
            return [];
        }
        $data = json_decode($res['data'],true);
        if (isset($data) && $data['rsp_code'] != '0') {
            Logger::dayLog('api/queryCloud',$data,$c_url);
            return [];
        }
        return $data;
    }
    private function getMultiSplit($data){
        $phone = ArrayHelper::getValue($data,'phone','');
        $idcard = ArrayHelper::getValue($data,'idcard','');
        $where = ['idcard' => $idcard,'phone' => $phone];
        $oDcMultiSplit = new DcMultiSplit();
        $data = $oDcMultiSplit->getOne($where);
        $ret_data = [
            'multi_small_p_class_7' => ArrayHelper::getValue($data,'7_multi_small_p_class',0),
            'multi_small_p_class_30' => ArrayHelper::getValue($data,'30_multi_small_p_class',0),
            'multi_p2p_p_class_7' => ArrayHelper::getValue($data,'7_multi_p2p_p_class',0),
            'multi_p2p_p_class_30' => ArrayHelper::getValue($data,'30_multi_p2p_p_class',0),
            'multi_common_p_class_7' => ArrayHelper::getValue($data,'7_multi_common_p_class',0),
            'multi_common_p_class_30' => ArrayHelper::getValue($data,'30_multi_common_p_class',0),
            'multi_big_p_class_7' => ArrayHelper::getValue($data,'7_multi_big_p_class',0),
            'multi_big_p_class_30' => ArrayHelper::getValue($data,'30_multi_big_p_class',0),
            'multi_all_p_class_7' => ArrayHelper::getValue($data,'7_multi_all_p_class',0),
            'multi_all_p_class_30' => ArrayHelper::getValue($data,'30_multi_all_p_class',0),
        ];
        return $ret_data;
    }
    /**
     * [getMultiInfo 获取多投数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function getMultiInfo($data)
    {
        $multiPhone = new MultiPhone();
        $ph_multi_select = 'mph_y,mph_fm,mph_other,mph_br,mph_fm_seven_d,mph_fm_one_m,mph_fm_three_m';
        $MultiInfo = $multiPhone->getPhMultiInfo($data['phone'], $ph_multi_select);

        $multiIdcard = new MultiIdcard();
        $id_multi_select = 'mid_y,mid_fm,mid_other,mid_br,mid_fm_seven_d,mid_fm_one_m,mid_fm_three_m';
        $MultiInfo += $multiIdcard->getIdMultiInfo($data['idcard'], $id_multi_select);
        return $MultiInfo;
    }
    /**
     * [getBlackInfo 获取黑名单数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function getBlackInfo($data)
    {
        $blackIdcard = new BlackIdcard();
        $id_black_select = 'bid_fm_sx,bid_fm_court_sx,bid_fm_court_enforce,bid_fm_lost,bid_y,bid_other,bid_br';
        $BlackInfo = $blackIdcard->getIdBlackInfo($data['idcard'], $id_black_select);

        $blackPhone = new BlackPhone();
        $ph_black_select = 'bph_fm_sx,bph_y,bph_other,bph_fm_small,bph_fm_fack,bph_br';
        $BlackInfo += $blackPhone->getPhBlackInfo($data['phone'], $ph_black_select);
        $BlackInfo['is_black'] = 0;
        foreach ($BlackInfo as $val) {
            if ($val != 0) {
                $BlackInfo['is_black'] = 1;
                break;
            }
        }
        return $BlackInfo;
    }

    /**
     * [getMultiLoan 获取多频数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function getMultiLoan($data)
    {
        $identity_id = ArrayHelper::getValue($data, 'identity_id', 0);
        $aid = ArrayHelper::getValue($data, 'aid', 0);
        $one_time = date('Y-m-d'); 
        $oLoan = new DcLoan;
        $loan_num_1 = $oLoan -> getMultiLoan($identity_id, $aid, $one_time);

        $seven_time = date('Y-m-d', strtotime('-6 days'));
        $loan_num_7 = $oLoan -> getMultiLoan($identity_id, $aid,  $seven_time);

        $loan_total = $oLoan -> getMultiLoan($identity_id, $aid);
        return [
            'one_more_loan_value' => $loan_num_1 > 0 ? $loan_num_1 : 0,
            'seven_more_loan_value' => $loan_num_7 > 0 ? $loan_num_7 : 0,
            'loan_total' => $loan_total < 0 ? 0 : $loan_total+1,
        ];
    }

    /**
     * 合规调用
     * [getMultiLoan 获取多频数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function getMultiLoanHg($data)
    {
        $identity_id = ArrayHelper::getValue($data, 'identity_id', 0);
        $create_time = ArrayHelper::getValue($data, 'create_time', date("Y-m-d H:i:s", time()));
        $create_time = date("Y-m-d", strtotime($create_time));

        // $aid = ArrayHelper::getValue($data, 'aid', 0);
        $one_time = $create_time;
        $oLoan = new DcLoan;
        $loan_num_1 = $oLoan -> getMultiLoan($identity_id, $one_time);

        $seven_time = date('Y-m-d', strtotime('-6 days', strtotime($create_time)));
        $loan_num_7 = $oLoan -> getMultiLoan($identity_id, $seven_time);

        $loan_total = $oLoan -> getMultiLoan($identity_id);
        return [
            'one_more_loan_value' => $loan_num_1 > 0 ? $loan_num_1 : 0,
            'seven_more_loan_value' => $loan_num_7 > 0 ? $loan_num_7 : 0,
            'loan_total' => $loan_total < 0 ? 0 : $loan_total+1,
        ];
    }

    /**
     * [getDeviceNum 获取多频数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    private function getDeviceNum($data)
    {
        $number_value = 0;
        $uuid = isset($data['device']) ? $data['device']:'';
        if (!empty($uuid)) {
            $deviceUser = new DeviceUser(); 
            $number_value = $deviceUser->find()->where(['and',['event'=> 'reg'],['aid'=>$data['aid']],['device'=>$uuid]])->groupBy('identity_id')->count();
        }
        $device_num['number_value'] = $number_value;
        return $device_num;
    }
    
    /**
     * [getOrigin 获取用户天启数据]
     * @param  [type] $data [description]
     * @return [array]       [description]
     */
    public function getOrigin($data)
    {
        $origin = new DcOrigin();
        $org_data = $origin->getResult($data);
        if (empty($org_data)) {
            return [
                'credit_score' => 0,
                'model_score_v2' => 0,
                'tianqi_score_v2' => -111,
                'last_create_time_tq' => '',
                'is_black' => 0,
            ];
        }
        $allData = [
            'credit_score' => (int)ArrayHelper::getValue($org_data,'credit_score',0),
            'model_score_v2' => (int)ArrayHelper::getValue($org_data,'model_score_v2',0),
            'tianqi_score_v2' => (int)ArrayHelper::getValue($org_data,'tianqi_score_v2',0),
            'last_create_time_tq' => ArrayHelper::getValue($org_data,'create_time',''),
            'is_black' => (int)ArrayHelper::getValue($org_data,'is_black',0),
        ];
        return $allData;
    }

    /**
     * 获取百度金融Prea信息
     */
    public function getBaiduPreaInfo($data)
    {
        $bd_data = ['baidu_score' => 0];
        $oBd = (new DcBaiduprea) -> getResult($data['mobile'],$data['identity']);
        //拼接百度prea信息
        if($oBd){
            $bd_data['baidu_score'] = isset($oBd->score) ? $oBd->score : 0;
        }
        return $bd_data;
    }
    /**
     * 获取百度金融Risk
     */
    public function getBaiduRiskInfo($data)
    {
        $baiduRisk = new DcBaidurisk();
        $baidu_select = 'black_level';
        $risk_info = $baiduRisk->getBaiduRisk($data,$baidu_select);
        return $risk_info;
    }

    /**
     * 一个月内该设备借款账户数
     */
    public function getOneMouthDeviceAccount($device)
    {
        if (empty($device)) {
            return 0;
        }
        $start_time = date('Y-m-d 00:00:00', strtotime("-1 month"));
        $where = ['and', ['device' => $device], ['>=', 'create_time', $start_time], ['event' => 'loan']];
        $oDcBasic = new DcBasic();
        $count = $oDcBasic->getAccountByDevice($where);
        return $count;
    }

    /**
     * 外催身份证黑名单命中
     */
    public function getForeignBlackIdcard($idcard)
    {
        if (empty($idcard)) {
            return 0;
        }
        $where = ['idcard'=>$idcard,'match_status'=>1];
        $oDcBasic = new DcForeignBlackIdcard();
        $count = $oDcBasic->getBlack($where);
        return $count;
    }

    /**
     * 外催手机号黑名单命中
     */
    public function getForeignBlackPhone($phone)
    {
        if (empty($phone)) {
            return 0;
        }
        $where = ['phone'=>$phone,'match_status'=>1];
        $oDcBasic = new DcForeignBlackPhone();
        $count = $oDcBasic->getBlack($where);
        return $count;
    }

    /**
     * 获取本地学信网信息
     */
    public function getTxskedu($data)
    {
        $edu_info = [
                'educationBackground' => '',
                'educationType' => '',
            ];
        $oEdu = (new DcTxskedu) -> getOne($data['user_id'],$data['identity']);
        //请求百度金融接口是否成功
        if (empty($oEdu)){
            return $edu_info;
        }

        if (isset($oEdu['result_info']) && empty($oEdu['result_info'])){
            return $edu_info;
        }

        $res = ArrayHelper::getValue($oEdu,'result_info','');
        if (isset($res['queryResult']) && $res['queryResult'] == 'NO_DATA'){
            return $edu_info;
        }

        $edu_info = json_decode($res,true);
        return $edu_info;
    }
}