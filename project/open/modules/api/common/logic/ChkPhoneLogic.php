<?php
namespace app\modules\api\common\logic;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

use app\models\xs\XsBlackPhone;
use app\models\service\DcServicePhone;
class ChkPhoneLogic extends BaseLogic {
    private $service_id;
    public function __construct() {

    }
    public function ChkPhoneOne($data) {
        $phone = ArrayHelper::getValue($data,'phone','');
        # set default array
        $chk_res = ['phone'=>$phone,'is_black'=> 0];
        if (empty($phone)) {
            return $chk_res;
        }
        // check black_phone
        $black_res = (new XsBlackPhone)->getByPhone($phone);
        if (!empty($black_res)) {
            $chk_res['is_black'] = 1;
        }
        // record check phone
        $save_arr = [
            'phone' => $phone,
            'service_id' => ArrayHelper::getValue($data,'service_id',''),
            'is_black' => ArrayHelper::getValue($chk_res,'is_black',0),
        ];
        $record_res = (new DcServicePhone)->savePhone($save_arr);
        return $chk_res;
    }

    public function ChkPhoneBatch($data) {
        $phone_list = ArrayHelper::getValue($data,'phone_list',[]);
        $this->service_id = ArrayHelper::getValue($data,'service_id','');
        # set default array
        $chk_res = ['black_list'=>[],'normal_list'=> $phone_list];
        // check black_phone
        $black_list = $this->getBlackList($phone_list);
        if (!empty($black_list)) {
            $chk_res['black_list'] = $black_list;
            $normal_list = array_values(array_diff($phone_list, $black_list));
            $chk_res['normal_list'] = $normal_list;
        }
        // record check phone
        $record_res = $this->recordPhoneList($chk_res,$phone_list);
        return $chk_res;
    }

    private function getBlackList($phone_list) {
        $black_res = (new XsBlackPhone)->getByPhoneList($phone_list);
        if (empty($black_res)) {
            return [];
        }
        $black_list = ArrayHelper::getColumn($black_res,'phone');
        return $black_list;
    }

    private function recordPhoneList($chk_res,$phone_list) {
        $record_list = (new DcServicePhone)->getByPhoneList($phone_list);
        $update_list = ArrayHelper::getColumn($record_list,'phone');
        $insert_list = array_diff($phone_list, $update_list);
        $update_black_list = [];
        $insert_black_list = [];
        if (!empty($chk_res['black_list'])) {
            if (!empty($update_list)) {
                $update_black_list = array_intersect($update_list, $chk_res['black_list']);
            }
            if (!empty($insert_list)) {
                $insert_black_list = array_intersect($insert_list, $chk_res['black_list']);
            }
        }
        $update_normal_list = [];
        $insert_normal_list = [];
        if (!empty($chk_res['normal_list'])) {
            if (!empty($update_list)) {
                $update_normal_list = array_intersect($update_list, $chk_res['normal_list']);
            }
            if (!empty($insert_list)) {
                $insert_normal_list = array_intersect($insert_list, $chk_res['normal_list']);
            }
        }
        $insert_num = $this->batchInsert($insert_normal_list,$insert_black_list);
        $update_num = $this->batchUpdate($update_normal_list,$update_black_list);
        return true;
    }

    private function batchInsert($normal_list,$black_list) {
        $time = date("Y-m-d H:i:s");
        $default_list = [
            'service_id' => $this->service_id,
            'create_time' => $time,
            'last_query_time' => $time,
            'is_black' => 0,
        ];
        $insert_list = [];
        if (!empty($normal_list)) {
            foreach ($normal_list as $normal_phone) {
                $default_list['phone'] = $normal_phone;
                $insert_list[] = $default_list;
            }
        }
        if (!empty($black_list)) {
            foreach ($black_list as $black_phone) {
                $default_list['phone'] = $black_phone;
                $default_list['is_black'] = 1;
                $insert_list[] = $default_list;
            }
        }
        $insert_num = (new DcServicePhone)->savePhoneBatch($insert_list);
        Logger::dayLog('chkphonelogic/batchInsert','in num is :',$insert_num);
        return $insert_num;
    }

    private function batchUpdate($normal_list,$black_list) {
        $time = date("Y-m-d H:i:s");
        $default_list = [
            'service_id' => $this->service_id,
            'last_query_time' => $time,
            'is_black' => 0,
        ];
        $up_normal_num = 0;
        if (!empty($normal_list)) {
            $up_normal_num = DcServicePhone::updateAll($default_list,['phone' => $normal_list]);
        }
        $up_black_num = 0;
        if (!empty($black_list)) {
            $default_list['is_black'] = 1;
            $up_black_num = DcServicePhone::updateAll($default_list,['phone' => $black_list]);
        }
        Logger::dayLog('chkphonelogic/batchUpdate','up num is :',$up_black_num,$up_normal_num);
        return $up_black_num + $up_normal_num;
    }

}