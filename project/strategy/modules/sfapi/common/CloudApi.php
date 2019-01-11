<?php
/**
 * 请求同盾类
 */
namespace app\modules\sfapi\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\common\Curl;
use app\common\ApiSign;

use app\models\Request;
use app\models\Loan;
use app\models\cloud\DcLoan;
use app\models\cloud\BlackIdcard;
use app\models\cloud\BlackPhone;
use app\models\cloud\MultiIdcard;
use app\models\cloud\MultiPhone;
use app\models\cloud\DeviceUser;
use app\models\cloud\DcOrigin;
use app\models\cloud\DcBaiduprea;
use app\models\cloud\DcBaidurisk;
use app\models\cloud\DcForeignBlackIdcard;
use app\models\cloud\DcForeignBlackPhone;

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
            return [];
        }
        //关联request表
        $res = (new request)->bindRequest($data,$rsp_data);
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
            Logger::dayLog('api/queryCloud',$data,$c_url,$postdata);
            return [];
        }
        return $data;
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
    private function getMultiLoan($data)
    {
        $identity_id = ArrayHelper::getValue($data, 'identity_id', 0);
        $aid = ArrayHelper::getValue($data, 'aid', 0);
        $start_time = date('Y-m-d');
        $oLoan = new DcLoan;
        $loan_num_1 = $oLoan -> getMultiLoan($identity_id, $aid, $start_time);

        $start_time = date('Y-m-d', strtotime('-6 days'));
        $loan_num_7 = $oLoan -> getMultiLoan($identity_id, $aid, $start_time);

        return [
            'one_more_loan_value' => $loan_num_1,
            'seven_more_loan_value' => $loan_num_7,
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
            return [];
        }
        $allData = [
            'credit_score' => (int)ArrayHelper::getValue($org_data,'credit_score',0),
            'model_score_v2' => (int)ArrayHelper::getValue($org_data,'model_score_v2',0),
            'tianqi_score_v2' => (int)ArrayHelper::getValue($org_data,'tianqi_score_v2',0),
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