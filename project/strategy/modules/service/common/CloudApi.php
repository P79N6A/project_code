<?php
/**
 * 请求同盾类
 */
namespace app\modules\service\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Curl;
use app\common\ApiSign;

use app\models\StCreditRequest;
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
            $res = (new StCreditRequest)->bindCreditRequest($data,$rsp_data);
        }
        //获取用户cloud数据
        $cloud_info = $this->getBlackInfo($data);
        //获取借款多投数据
        $cloud_info += $this->getMultiInfo($data);
        //获取借款申请数据
        $cloud_info += $this->getCreditLoan($data);
        //获取借款分位值数据
        $cloud_info += $this->getMultiSplit($data);
        //获取借款设备历史申请数据
        $cloud_info += $this->getDeviceLoan($data);
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
     * [normalLoanData 标准化cloudApi参数]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function normalCloudParams($data)
    {
        $params = [
            'identity_id' => ArrayHelper::getValue($data,'user_id',0), 
            'idcard' => ArrayHelper::getValue($data,'identity','0'),
            'phone' => ArrayHelper::getValue($data,'mobile','0'),
            'name' =>  ArrayHelper::getValue($data,'realname','0'),
            'ip' =>  ArrayHelper::getValue($data,'ip_address','0'), //ip地址
            'device' =>  ArrayHelper::getValue($data,'device','0'), // 设备号
            'source' =>  ArrayHelper::getValue($data,'source','0000'), //来源 ios,android,web,....
            'token_id' =>  ArrayHelper::getValue($data,'token_id','0'),
            'aid' =>  ArrayHelper::getValue($data,'aid','0'),
            // 公司与学校信息
            'company_name' => ArrayHelper::getValue($data,'company_name',''),
            'company_industry' => ArrayHelper::getValue($data,'company_industry',''), // 选填 行业
            'company_position' => ArrayHelper::getValue($data,'company_position',''),
            'company_phone' => ArrayHelper::getValue($data,'telephone',''),// 选填 公司电话
            'company_address' =>  ArrayHelper::getValue($data,'company_address',''), // 选填 公司地址
            'school_name' =>  ArrayHelper::getValue($data,'school_name',''), // 选填 学校名称
            'school_time' => ArrayHelper::getValue($data,'school_time',''), // 选填 入学时间
            'edu' => ArrayHelper::getValue($data,'edu',''), // 选填 本科,研究生

            // gps
            'latitude' => ArrayHelper::getValue($data,'latitude',''),
            'longtitude' => ArrayHelper::getValue($data,'longtitude',''),
            'accuracy' => ArrayHelper::getValue($data,'accuracy',''),
            'speed' => ArrayHelper::getValue($data,'speed',''),
            'location' => ArrayHelper::getValue($data,'location',''),
            'cardno' => ArrayHelper::getValue($data,'cardno',''),
            'reason' =>  ArrayHelper::getValue($data,'reason',''),
        ];
        $params = array_merge($data,$params);
        return $params;
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
     * [getCreditLoan 获取多频数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function getCreditLoan($data)
    {
        $phone = ArrayHelper::getValue($data, 'mobile', 0);

        // $aid = ArrayHelper::getValue($data, 'aid', 0);
        $one_time = date('Y-m-d');
        $oDcBasic = new DcBasic;
        $loan_num_1 = $oDcBasic -> getCreditLoanByPhoneWithtime($phone, $one_time);

        $seven_time = date('Y-m-d', strtotime('-6 days'));
        $loan_num_7 = $oDcBasic -> getCreditLoanByPhoneWithtime($phone, $seven_time);

        $credit_total = $oDcBasic -> getCreditLoanByPhoneWithtime($phone);
        return [
            'one_more_loan_value' => $loan_num_1,
            'seven_more_loan_value' => $loan_num_7,
            'credit_total' => $credit_total,
        ];
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
     * 该设备借款账户数
     */
    public function getDeviceLoan($data)
    {
        $device = ArrayHelper::getValue($data, 'device', '');
        if (empty($device)) {
            return 0;
        }
        $start_time = date('Y-m-d 00:00:00', strtotime("-1 month"));
        $one_mouth_where = ['and', ['device' => $device], ['>=', 'create_time', $start_time], ['event' => 'loan']];
        $oDcBasic = new DcBasic();
        $one_mouth_count = $oDcBasic->getPhoneByDevice($one_mouth_where);

        $all_where = ['device' => $device, 'event' => 'loan'];
        $oDcBasic = new DcBasic();
        $all_count = $oDcBasic->getPhoneByDevice($all_where);
        $device_loan = [
            'number_value' => (int)$all_count,
            'one_number_account_value' => (int)$one_mouth_count,
        ];
        return $device_loan;
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
}