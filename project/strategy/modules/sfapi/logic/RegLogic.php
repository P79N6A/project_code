<?php
namespace app\modules\sfapi\logic;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\Stuser;
use app\models\Request;
use app\models\yyy\RegisterEvent;
use app\models\yyy\User;
use app\models\yyy\UserPassword;
use app\models\yyy\UserExtend;
use app\models\cloud\BlackIdcard;
use app\models\cloud\BlackPhone;
use app\models\cloud\DeviceUser;
use app\models\cloud\DcBaidurisk;
use app\modules\sfapi\common\CloudApi;

class RegLogic extends BaseLogic
{
    public function getRegInfo($data)
    {
        //请求cloud
        $api = new \app\modules\sfapi\common\BaseApi();
        $res = $api->queryCloud($data,'cloud/reg');
        if (!$res) {
            return $this->returnInfo(false, $api->info);
        }
        $rsp_data = $api->info; 
        //关联request表
        $res = (new request)->bindRequest($data,$rsp_data);
        $data = array_merge($data,$rsp_data);
        //标准化决策参数
        $reg_info = $this->normalData($data);
        //获取注册决策数据
        $id_black_select = 'bid_fm_sx,bid_fm_court_sx,bid_fm_court_enforce,bid_fm_lost,bid_y,bid_other,bid_br';
        $ph_black_select = 'bph_fm_sx,bph_y,bph_other,bph_fm_small,bph_fm_fack,bph_br';
        $blackIdcard = new BlackIdcard();
        $blackPhone = new BlackPhone();
        //获取注册决策信息
        $number_value = 0;
        $uuid = isset($data['device']) ? $data['device']:'';
        if (!empty($uuid)) {
            $deviceUser = new DeviceUser(); 
            $number_value = $deviceUser->find()->where(['and',['event'=> 'reg'],['aid'=>$data['aid']],['device'=>$uuid]])->groupBy('identity_id')->count();
        }
        $reg_info['number_value'] = $number_value;
        //身份证号黑名单明细
        $reg_info += $blackIdcard->getIdBlackInfo($reg_info['identity'], $id_black_select);
        //手机号黑名单明细
        $reg_info += $blackPhone->getPhBlackInfo($reg_info['mobile'], $ph_black_select);
        //活体验证分数
        // $reg_info += $userPassword->getScoreInfo($where, 'device_tokens');
        // 催收黑名单
        $cloud_api = new CloudApi();
        $reg_info['id_collection_black'] = $cloud_api->getForeignBlackIdcard($reg_info['identity']);
        $reg_info['ph_collection_black'] = $cloud_api->getForeignBlackPhone($reg_info['mobile']);
        $record_user = new Stuser;
        //百度金融评级
        $baiduRisk = new DcBaidurisk();
        $baidu_select = 'black_level';
        $reg_info += $baiduRisk->getBaiduRisk($reg_info,$baidu_select);
        //记录用户数据
        $res = $record_user->addUserInfo($reg_info);
        if (!$res) {
            Logger::dayLog('reg', $record_user->errors,$reg_info);
            return $this->returnInfo(false, '记录失败');
        }
        //数据类型转换
        $reg_info = $this->transType($reg_info);
        return $this->returnInfo(true, $reg_info);
    }

    public function transType($data)
    {
        foreach ($data as $k => $val) {
            if ($k != 'identity' && $k != 'mobile' && $k != 'reg_time' && $k != 'realname' && $k != 'query_time' && $k != 'rsp_code' && $k != 'rsp_msg' && $k != 'black_level') {
                $data[$k] = (int)$data[$k];
            }
        }
        return $data;
    }

    //标准化决策参数
    private function normalData($data)
    {
        $ret_info = [
            'user_id'=>isset($data['identity_id']) ? $data['identity_id'] : 0,
            'identity'=>isset($data['idcard']) ? $data['idcard'] : '',
            'mobile'=>isset($data['phone']) ? $data['phone'] : '',
            'realname'=>isset($data['name']) ? $data['name'] : '',
            'reg_time'=>isset($data['reg_time']) ? $data['reg_time'] : '',
            'request_id'=>isset($data['request_id']) ? $data['request_id'] : 0,
            'query_time'=>date('Y-m-d H:i:s'),
            'basic_id'=>isset($data['basic_id']) ? $data['basic_id'] : 0,
            'rsp_code'=>isset($data['rsp_code']) ? $data['rsp_code'] : 0,
            'rsp_msg'=>isset($data['rsp_msg']) ? $data['rsp_msg'] : 0,
            'prd_type'=>isset($data['aid']) ? $data['aid'] : 1,
        ];
        return $ret_info;
    }
}