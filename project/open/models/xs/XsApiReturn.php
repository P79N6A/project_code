<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;
/**
 * 基本数据的保存
 * 用于计算注册,借款事件的结果
 */
class XsApiReturn{
    /**
     * 根据basic_id计算
     * @param  int $basic_id 
     * @return  null | []
     */
    public function get($basic_id){
        //1. 获取数据
        $oBasic = XsBasic::findOne($basic_id);
        if(!$oBasic){
            return null;
        }
        
        //2. 保存数据
        Switch($oBasic->event){
            case "reg":
                $res = $this->getReg($oBasic);
                break;
            case "loan":
                $res = $this->getLoan($oBasic);
                break;
            default:
                $res = null;
        }
        return $res;
    }
    /**
     * 获取注册计算结果
     */
    public function getReg($oBasic){
        //1 基本数据
        if(!$oBasic){
            return null;
        }

        //2 获取通用数据
        $data = $this->getCommon($oBasic);
        if(empty($data)){
            return null;
        }
        return $data;
    }
    /**
     * 获取借款计算结果
     */
    public function getLoan($oBasic,$type = 1){
        //1 基本数据
        if(!$oBasic){
            return null;
        }

        //2 获取通用数据
        $data = $this->getCommon($oBasic,$type);
        if(empty($data)){
            return null;
        }

        //3 获取高频借款情况
        $loan_nums = $this->getMultiLoan($oBasic['identity_id'],$oBasic['aid'], $oBasic['id']);
        $data = array_merge($data, $loan_nums);

        //4 获取当月借款
        //$data['device_loan_users'] = $this->getDeviceLoanUsers($oBasic['device']);
        $data['device_loan_month'] = $this->getDeviceLoanMonth($oBasic['device']);
        
        return $data;
    }
    /**
     * 获取通用数据
     * @param  int $basic_id 
     * @return []
     */
    private function getCommon($oBasic,$type = 1){
        //1. 获取基本数据
        $basic = $this->getBasic($oBasic);
     
        //2. 获取ip, 设备限制
        // $relation = $this->getRelation($oBasic['device'], $oBasic['ip']);

        // //3. 获取黑名单
        // $black = $this->getBlack($oBasic['phone'],$oBasic['idcard']);

        // //4. 多投
        // $multi = $this->getMulti($oBasic['phone'],$oBasic['idcard']);

        // //5. 拼接以上各数据结果
        $oMap = new YArray;
        $oMap -> add($basic);
        // $oMap -> add($relation);
        // $oMap -> add($black);
        // $oMap -> add($multi);
        $data = $oMap -> get();

        //6. 获取同盾数据;同盾单独键值存放
        $fraudMetrix = $this->getFraudMetrix($oBasic['phone'],$oBasic['idcard'],$oBasic['event'],$type);
        $data['fm'] = $fraudMetrix;
        //7. 获取百度数据；百度数据单独存放
        $bdrisk = $this->getBaiduRiskInfo($oBasic);
        $data['bd'] = $bdrisk;
        //8. 获取百度信用分数据
        $xsBaiduApi = new XsBaiduApi();
        $data['bd_prea'] = $xsBaiduApi->runBaidu($oBasic,'prea');
        //9. 获取百度多头查询数据
        $data['bd_multi'] = $xsBaiduApi->runBaidu($oBasic,'multi');
        return $data;
    }
    /**
     * 获取基本数据
     * @param  obj $oBasic
     * @return [] 返回的数据
     */
    private function getBasic($oBasic){
        $basic = (new YArray) -> getByKeys($oBasic,[
            'identity_id',
            'aid',
            'event',
            'idcard',
            'phone',
        //     'birth',
        //     'gender',
        //     'province',
        ],'');
        $basic['basic_id'] = $oBasic['id'];
        // $basic['age'] = date('Y') - date('Y', strtotime($basic['birth']));
        return $basic;
    }
    /**
     * 计算注册参数
     */
    private function getRelation($device, $ip){
        $oDeviceIp = new XsDeviceIp;
        // 同一ip对应设备数
        $ip_devices = $oDeviceIp -> sameIpDevices($ip);
        // 同一设备对应ip数
        $device_ips = $oDeviceIp -> sameDeviceIps($device);

        //同一ip用户数
        $oIpUser = new XsIpUser;
        $ip_users = $oIpUser -> sameIpUsers($ip);
        
        //同一设备用户数
        $oDeviceUser = new XsDeviceUser;
        $device_users = $oDeviceUser -> sameDeviceUsers($device);

        return [
            'ip_devices' => $ip_devices,
            'device_ips' => $device_ips,
            'ip_users' => $ip_users,
            'device_users' => $device_users,
        ];
    }
    /**
     * 获取同一设备借款数
     * @param   $device 
     * @return  
     */
    /*private function getDeviceLoanUsers($device){
        $where = [
            'device'=>$device,
            'event' => 'loan',
        ];
        $total = XsDeviceUser::find() -> where($where) -> count();
        return $total;
    }*/
    /**
     * 当月单一设备数
     * @param   $device 
     * @return  
     */
    private function getDeviceLoanMonth($device){
        if(!$device){
            return 0;
        }
        $where = [
            'AND',
            ['device'=>$device],
            ['event' => 'loan'],
            ['>=', 'modify_time', date('Y-m-01')],
        ];
        $total = XsDeviceUser::find() -> where($where) -> count();
        return $total;
    }
    /**
     * 获取是否黑名单
     */
    private function getBlack($phone,$idcard){
        //1. 获取手机号黑名单
        $oYArray = new YArray;
        $oBlack = new XsBlackIdcard;
        $idcard_black = $oBlack -> getByIdcard($idcard);
        $idcard_black = $oYArray -> getByKeys($idcard_black,[
            'bid_y', 
            'bid_fm_sx', 
            'bid_fm_court_sx', 
            'bid_fm_court_enforce', 
            'bid_fm_lost', 
            'bid_other', 
            //'br_b',         
        ],0);

        //2. 获取身份证黑名单情况
        $oBlack = new XsBlackPhone;
        $phone_black = $oBlack -> getByPhone($phone);
        $phone_black = $oYArray -> getByKeys($phone_black,[
            'bph_y', 
            'bph_fm_fack', 
            'bph_fm_small', 
            'bph_fm_sx', 
            'bph_other', 
            //'br_b',            
        ],0);
       
        $oYArray -> add($phone_black);
        $oYArray -> add($idcard_black);
        $data = $oYArray ->get();
        $data['is_black'] = $this->chkExists($data);
        return $data;
    }
    /**
     * 获取是否黑名单
     */
    private function getMulti($phone,$idcard){
        //1. 获取手机号黑名单
        $oYArray = new YArray;
        $oMulti = new XsMultiIdcard;
        $idcard_black = $oMulti -> getByIdcard($idcard);
        $idcard_black = $oYArray -> getByKeys($idcard_black,[
            'mid_y', 
            'mid_fm', 
            'mid_other', 
            'mid_br',    
        ],0);

        //2. 获取身份证黑名单情况
        $oMulti = new XsMultiPhone;
        $phone_black = $oMulti -> getByPhone($phone);
        $phone_black = $oYArray -> getByKeys($phone_black,[
            'mph_y', 
            'mph_fm', 
            'mph_other', 
            'mph_br',       
        ],0);
       
        $oYArray -> add($phone_black);
        $oYArray -> add($idcard_black);
        $data = $oYArray ->get();
        $data['is_multi'] = $this->chkExists($data);
        return $data;
    }
    /**
     * 检测是否存在1
     * @param  [] $data
     * @param  [] $keys
     * @return bool
     */
    private function chkExists(&$data) {
        $num = 0;
        foreach ($data as $v) {
            if ($v > 0) {
                $num = 1;
                break;
            }
        }
        return $num;
    }
    /**
     * 获取同盾信息
     */
    private function getFraudMetrix($phone,$idcard,$event,$type){
        $map = ['reg'=>'register_web', 'loan'=>'loan_web'];
        $event = isset($map[$event]) ? $map[$event] : '';
        if(!$event){
            return [];
        }

        $fm_data = [];
        $oFm = (new XsFraudmetrix) -> getResult($phone,$idcard,$event,$type);
        if($oFm){
            $fm_data = (new YArray) -> getByKeys($oFm,[
                'seq_id',
                'decision',
                'score',
                'is_black',
                'is_multi',
            ],0);
        }

        return $fm_data;
    }
    /**
     * 获取百度金融信息
     */
    private function getBaiduRiskInfo($oBasic)
    {
        $bd_data = [];
        $oBd = (new XsBaidurisk) -> getResult($oBasic['phone'],$oBasic['idcard']);
        //请求百度金融接口是否成功
        if($oBd){
            $bd_data = (new YArray) -> getByKeys($oBd,[
                'retCode',
                'retMsg',
                'black_level',
            ],0);
        }
        return $bd_data;
    }

    /**
     * 获取高频借款
     * @param  str $identity_id 
     * @param  int $aid         
     * @return []
     */
    private function getMultiLoan($identity_id, $aid){
        $start_time = date('Y-m-d');
        $oLoan = new XsLoan;
        $loan_num_1 = $oLoan -> getMultiLoan($identity_id, $aid, $start_time);

        $start_time = date('Y-m-d', strtotime('-6 days'));
        $loan_num_7 = $oLoan -> getMultiLoan($identity_id, $aid, $start_time);

        return [
            'loan_num_1' => $loan_num_1,
            'loan_num_7' => $loan_num_7,
        ];
    }
    /**
     * 获取一段时间内同盾信息
     */
    public function getFmInfo($phone,$idcard,$event,$time){
        $map = ['reg'=>'register_web', 'loan'=>'loan_web'];
        $event = isset($map[$event]) ? $map[$event] : '';
        if(!$event){
            return [];
        }

        $fm_data = [];
        $oFm = (new XsFraudmetrix) -> getFmData($phone,$idcard,$event,$time);
        if($oFm){
            $fm_data = (new YArray) -> getByKeys($oFm,[
                'decision',
                'score',
                'is_black',
                'is_multi',
            ],0);
        }

        return $fm_data;
    }
}
