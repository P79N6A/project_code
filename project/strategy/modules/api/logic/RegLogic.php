<?php
namespace app\modules\api\logic;

use app\common\Logger;
use app\models\cloud\BlackIdcard;
use app\models\cloud\BlackPhone;
use app\models\cloud\DeviceUser;
use app\models\cloud\DcBaidurisk;
use app\modules\api\common\CloudApi;
use app\models\Stuser;
use app\models\yyy\RegisterEvent;
use app\models\yyy\User;
use app\models\yyy\UserPassword;
use app\models\yyy\UserExtend;
use Yii;
use yii\helpers\ArrayHelper;

class RegLogic extends BaseLogic
{
    public function getRegInfo($data)
    {
        $user_id = ArrayHelper::getValue($data, 'user_id');
        $request_id = ArrayHelper::getValue($data, 'request_id');
        $where = ['user_id' => $user_id];
        //获取注册决策数据
        $user_select = 'user_id,identity,mobile,create_time,realname';
        $reg_event_select = 'number_value,is_black';
        $id_black_select = 'bid_fm_sx,bid_fm_court_sx,bid_fm_court_enforce,bid_fm_lost,bid_y,bid_other,bid_br';
        $ph_black_select = 'bph_fm_sx,bph_y,bph_other,bph_fm_small,bph_fm_fack,bph_br';
        $user = new User;
        $blackIdcard = new BlackIdcard();
        $blackPhone = new BlackPhone();
        //获取用户基本信息
        $reg_info = $user->getInfo($where, $user_select);
        if (empty($reg_info)) {
            Logger::dayLog('regLogic','reg_info','用户不存在',$data);
            return $this->returnInfo(false, '用户不存在');
        }
        //获取注册决策信息
        $user_extend = new UserExtend();
        $device = $user_extend->getInfo($where,'uuid');
        $number_value = 0;
        if (!empty($device)) {
            $deviceUser = new DeviceUser(); 
            $number_value = $deviceUser->find()->where(['and',['event'=> 'reg'],['aid' => '1'],['device'=>$device['uuid']]])->groupBy('identity_id')->count();
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
        //百度金融评级
        $baiduRisk = new DcBaidurisk();
        $baidu_select = 'black_level';
        $reg_info += $baiduRisk->getBaiduRisk($reg_info,$baidu_select);
        $reg_info['reg_time'] = $reg_info['create_time'];
        unset($reg_info['create_time']);
        $reg_info['query_time'] = date('Y-m-d H:i:s');
        $reg_info['request_id'] = $request_id;
        $record_user = new Stuser;
        // $stuser_info = $record_user::findOne($where);
        // if (empty($stuser_info)) {
            //记录用户数据
            $res = $record_user->addUserInfo($reg_info);
            if (!$res) {
                Logger::dayLog('reg', $record_user->errors,$reg_info);
                return $this->returnInfo(false, '记录失败');
            }
        // } else {
        //     //更新数据
        //     $res = $stuser_info->updateUserInfo($reg_info);
        //     if (!$res) {
        //         Logger::dayLog('reg', $stuser_info->errors,$reg_info);
        //         return $this->returnInfo(false, '更新失败');
        //     }
        // }
        //数据类型转换
        $reg_info = $this->transType($reg_info);
        return $this->returnInfo(true, $reg_info);
    }

    public function transType($data)
    {
        foreach ($data as $k => $val) {
            if ($k != 'identity' && $k != 'mobile' && $k != 'reg_time' && $k != 'realname' && $k != 'query_time' && $k != 'black_level') {
                $data[$k] = (int)$data[$k];
            }
        }
        return $data;
    }
}