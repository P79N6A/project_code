<?php
namespace app\models\xs;

use app\common\Logger;

/**
 * set仅负责保存数据
 * 基本数据的保存
 * 用于保存注册,借款事件的数据
 */
class XsApiSetBasic {
    private $basic;
    /**
     * 保存事件的基本数据
     */
    public function setBasic($event, $post_data) {
        //1. 获取请求参数
        $data = $this->request($post_data);
        $data['event'] = $event;

        // $where = [
        //     'AND',
        //     ['identity_id' => $data['identity_id']],
        //     ['event' => $data['event']],
        //     ['>', 'create_time', date('Y-m-d H:i:s', strtotime('-1 hour'))],
        // ];
        // $this->basic = XsBasic::find()->where($where)->orderBy('id DESC')->limit(1)->one();
        // if ($this->basic) {
        //     return $this->basic;
        // }

        //2. 保存数据
        Switch ($event) {
        case "reg":
            $result = $this->save($data);
            break;
        case "loan":
            $result = $this->save($data);
            if ($result) {
                $result = $this->saveLoan($this->basic->id, $data);
            }
            break;
        default:
            $result = false;
        }

        return $result ? $this->basic : null;
    }
    /**
     * 获取事件要求的请求参数
     */
    private function request(&$data) {
        $keys = [
            'aid',
            'identity_id',
            'event',
            'idcard',
            'birth',
            'gender',
            'area',
            'phone',
            'name',
            'ip',
            'device',
            'source',

            'company_name',
            'company_industry',
            'company_position',
            'company_phone',
            'company_address',
            'school_name',
            'school_time',
            'edu',

            'latitude',
            'longtitude',
            'accuracy',
            'speed',
            'location',

            'loan_id',
            'amount',
            'loan_days',
            'cardno',
            'reason',
            'loan_time',
        ];
        $arr = (new YArray)->getByKeys($data, $keys, '');
        //默认是1
        $arr['identity_id'] = (string) $arr['identity_id'];
        $arr['loan_id'] = (string) $arr['loan_id'];
        $arr['aid'] = $arr['aid'] ? $arr['aid'] : 1;
        return $arr;
    }
    /**
     * 保存基本数据
     */
    private function save(&$data) {
        $res = $this->saveBasic($data);
        if (!$res) {
            return false;
        }
        $result = $this->saveRelation(
            $data['identity_id'],
            $data['device'],
            $data['ip'],
            $data['event'],
            $data['aid'],
            $data['phone']
        );
        if (!$result) {
            return false;
        }
        return true;
    }
    /*
     * 保存基本数据
     */
    private function saveBasic($data) {
        // 提交基本资料
        $basic_keys = [
            'aid',
            'identity_id',
            'event',
            'idcard',
            'birth',
            'gender',
            'area',
            'phone',
            'name',
            'ip',
            'device',
            'source',
        ];
        $basic = (new YArray)->getByKeys($data, $basic_keys, '');
        $oBasic = new XsBasic;
        $result = $oBasic->saveData($basic);
        if (!$result) {
            Logger::dayLog("xsapi", $basic, $oBasic->errors);
            return false;
        }
        $data['basic_id'] = $oBasic->id;

        // 提交扩展信息j
        $extend_keys = [
            'basic_id',
            'company_name',
            'company_industry',
            'company_position',
            'company_phone',
            'company_address',
            'school_name',
            'school_time',
            'edu',
        ];
        $extend = (new YArray)->getByKeys($data, $extend_keys, '');
        if ($data['company_name'] || $data['school_name']) {
            $oExtend = new XsExtend;
            $result = $oExtend->saveData($extend);
            if (!$result) {
                Logger::dayLog("xsapi", $extend, $oExtend->errors);
                return false;
            }
        }
        // 提交gps
        $gps_keys = [
            'basic_id',
            'latitude',
            'longtitude',
            'accuracy',
            'speed',
            'location',
        ];
        $gps = (new YArray)->getByKeys($data, $gps_keys, '');
        if ($data['latitude'] && $data['longtitude']) {
            $oGps = new XsGps;
            $result = $oGps->saveData($gps);
            if (!$result) {
                Logger::dayLog("xsapi", $gps, $oGps->errors);
                return false;
            }
        }
        //保存到成员变量中
        $this->basic = $oBasic;
        return true;
    }
    /**
     * 保存ip,device,user,对应关系
     */
    public function saveRelation($identity_id, $device, $ip, $event, $aid, $phone) {
        $oDeviceIp = new XsDeviceIp;
        if ($device && $ip) {
            $result = $oDeviceIp->chkAndSave($device, $ip, $event, $aid);
            if (!$result) {
                Logger::dayLog("xsapi", $oDeviceIp->attributes, $oDeviceIp->errors);
                return false;
            }
        }

        if ($device && ($identity_id || $phone)) {
            $oDeviceUser = new XsDeviceUser;
            $result = $oDeviceUser->chkAndSave($device, $identity_id, $event, $aid, $phone);
            if (!$result) {
                Logger::dayLog("xsapi", $oDeviceUser->attributes, $oDeviceUser->errors);
                return false;
            }
        }

        if ($ip && ($identity_id || $phone)) {
            $oIpUser = new XsIpUser;
            $result = $oIpUser->chkAndSave($ip, $identity_id, $event, $aid, $phone);
            if (!$result) {
                Logger::dayLog("xsapi", $oIpUser->attributes, $oIpUser->errors);
                return false;
            }
        }
        return true;
    }
    /**
     * 保存到借款表中
     */
    private function saveLoan($basic_id, $data) {
        // 提交资料
        $loan_keys = [
            'aid',
            'identity_id',
            'loan_id',
            'amount',
            'loan_days',
            'cardno',
            'reason',
            'loan_time',
        ];
        $loan = (new YArray)->getByKeys($data, $loan_keys, '');
        $loan['basic_id'] = $basic_id;
        $oLoan = new XsLoan;
        $result = $oLoan->saveData($loan);
        if (!$result) {
            Logger::dayLog("xsapi", $oLoan->attributes, $oLoan->errors);
            return false;
        }
        return true;
    }

}
