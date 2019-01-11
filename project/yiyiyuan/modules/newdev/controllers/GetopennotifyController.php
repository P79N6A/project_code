<?php

namespace app\modules\newdev\controllers;

use app\models\news\Areas;
use app\models\news\Card_bin;
use app\models\news\Payaccount;
use app\models\news\PayAccountError;
use app\commonapi\Logger;
use app\commonapi\Apidepository;
use app\models\news\User;
use app\models\news\User_bank;

class GetopennotifyController extends NewdevController {
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    /*
     * 接收存管开户结果
     */
    public function actionIndex() {
        $postData = $this->post();
        Logger::errorLog(print_r($postData, true), 'OpenNotify', 'debt');
        if(!is_array($postData) || !isset($postData['data'])){
            exit('error1');
        }
       
//        $postData['data'] = '{"res_code":1,"res_data":"{\"bankCode\":\"30050000\",\"idType\":\"\",\"seqNo\":\"309352\",\"txTime\":\"181826\",\"channel\":\"000002\",\"retCode\":\"CA110861\",\"version\":\"10\",\"retMsg\":\"同一客户只能开立一个身份角色\",\"cardNo\":\"6222021715003756009\",\"idNo\":\"\",\"accountId\":\"6212462040000141277\",\"name\":\"360726199102158322\",\"instCode\":\"01090001\",\"txCode\":\"accountOpenEncryptPage\",\"acqRes\":\"204220\",\"txDate\":\"20180917\",\"status\":\"0\"}"}';
//        $postData['data'] = '{"res_code":0,"res_data":"{\"bankCode\":\"30050000\",\"idType\":\"01\",\"seqNo\":\"546082\",\"txTime\":\"182201\",\"channel\":\"000002\",\"retCode\":\"00000000\",\"version\":\"10\",\"retMsg\":\"成功\",\"cardNo\":\"6222021715003756008\",\"idNo\":\"441581198105083389\",\"accountId\":\"6212462040000171274\",\"name\":\"360726199102158322\",\"instCode\":\"01090001\",\"txCode\":\"accountOpenEncryptPage\",\"acqRes\":\"204220\",\"txDate\":\"20180917\",\"status\":\"1\"}"}';
//        $postData['data'] = '{"res_code":0,"res_data":"{\"bankCode\":\"30050000\",\"idType\":\"01\",\"seqNo\":\"546082\",\"txTime\":\"182201\",\"channel\":\"000002\",\"retCode\":\"00000000\",\"version\":\"10\",\"retMsg\":\"成功\",\"cardNo\":\"6222021715003756008\",\"idNo\":\"441581********3389\",\"accountId\":\"6212462040000171274\",\"name\":\"360726199102158322\",\"instCode\":\"01090001\",\"txCode\":\"accountOpenEncryptPage\",\"acqRes\":\"204220\",\"txDate\":\"20180917\",\"status\":\"1\"}"}';

        $result = json_decode($postData['data'], true);
        $res_json = $postData['data'];
        Logger::errorLog(print_r($result, true), 'OpenNotify', 'debt');
        if (!$result) {
            exit('error2');
        }
        $res_data = $result['res_data'];
        $res_arr = json_decode($res_data, true);
        if (!is_array($res_arr)  || !isset($res_arr['acqRes']) ||  !isset($res_arr['retCode']) || !isset($res_arr['accountId']) || !isset($res_arr['idNo']) || !isset($res_arr['cardNo'])) {
            exit('error3');
        }
        $accountId = $res_arr['accountId'];
        $res = $res_arr['retCode'];
        $cardNo = $res_arr['cardNo'];
        $userInfo = User::findOne($res_arr['acqRes']);
        $idNo = $res_arr['idNo'];
        if( strstr($idNo, '*') ){ //若是脱敏身份证号，则根据接口获取完整身份证号
            $idNo = $this -> getcard($accountId);
            if(!$idNo){
                Logger::dayLog('cunguan/notify', '脱敏身份证号请求获取完整身份证号信息出错', 'user_id->' . $userInfo->user_id,$idNo);
                exit('idNo error');
            }
        }
        $isOpen = Payaccount::find()->where(['user_id' => $userInfo->user_id, 'type' => 2, 'step' => 1])->orderBy('activate_time desc')->one();
        if (!$isOpen) {
            exit('Payaccount error');
        }
        if ($isOpen->activate_result == 1) {
            return "SUCCESS";
        }
        $pass_res = false;
        if ($result['res_code'] == 0 && $res == '00000000' && $res_arr['status']==1 ) {
            $user = (new User())->getUserinfoByIdentity($idNo);
            if (empty($user) || ($idNo != $userInfo->identity ) ) {
                //存入pay_account_error数据库
                $this->saveErrorPayaccount($res_arr['acqRes'],'1','身份证号不一致',$res_json);
                exit('userInfo error');
            }
            //将接收的成功结果记录到pay_account_error数据库
            $this->saveErrorPayaccount($userInfo->user_id,$res_arr['retCode'],$res_arr['retMsg'],$res_json);
            
            //绑卡
            $bankRes = $this->bindBank($userInfo, $cardNo);
            if (!$bankRes) {
                exit('bindBank error');
            }
            
            $bankInfo = (new User_bank)->getByCard($cardNo);
            $condition['activate_result'] = 1;
            $condition['activate_time'] = date('Y-m-d H:i:s');
            $condition['accountId'] = $accountId;
            $condition['card'] = (string)$bankInfo->id;
            $open_res = $isOpen->update_list($condition);
           
            $condition_setpwd = [
                "user_id" => $userInfo->user_id,
                'type' => 2,
                'step' => 2,
                'activate_result' => 1,
                'accountId' => $accountId,
            ];
            $addRes = (new Payaccount() )->add_list($condition_setpwd);
            
            if ($addRes && $open_res ) {
                $pass_res = true;
            }else{
                 Logger::dayLog('cunguan/notify', 'pay_account表操作失败', 'user_id->' . $userInfo->user_id,$addRes,$open_res);
            }
        } elseif ($result['res_code'] == 1 || ( $res != '00000000' && in_array($res_arr['status'], [0,2]) )) {
            $pass_res = $isOpen->update_list(['activate_result' => 2]);
            if( $res != '00000000' ){
                //存入pay_account_error数据库
                $this->saveErrorPayaccount($userInfo->user_id,$res_arr['retCode'],$res_arr['retMsg'],$res_json);
            }
        }

        if (!$pass_res) {
            exit('error');
        }
        return "SUCCESS";
    }
    
    /**
     * 根据接口获取脱敏身份证号对应的完全身份证号
     * @param type $accountId
     */
    private function getcard($accountId){
        $apiDep = new Apidepository();
        $params = [
            'accountId' => $accountId, //交易渠道
            'state' => '1',
        ];
        $ret_query = json_decode($apiDep->cgCardQuery($params), true);
        if ($ret_query['res_code'] != 0) {
            if (isset($ret_query['rsp_data'])) {
                Logger::dayLog('getcard', $ret_query['rsp_data'], $accountId);
            }
            if (isset($ret_query['rsp_msg'])) {
                Logger::dayLog('getcard', $ret_query['rsp_msg'], $accountId);
            }
            return false;
        }
        $res = $ret_query['res_data'];
        $result = json_decode($res['subPacks'],true);
        
        if(empty($result)  || !isset($result['subPacks'][0]['cardNo']) ){
            return false;
        }
        $idcard = $result['subPacks']['cardNo'];
        return $idcard;
    }

    /**
     * 
     * @param type $user_id
     * @param type $code 错误码
     * @param type $msg  错误提示信息
     * @param type $type 1:开户
     */
    private function saveErrorPayaccount( $user_id,$code,$msg,$res_json,$status = 0,$type=1){
            $condition = [
                'user_id' => $user_id,
                'type' => $type,
                'res_code' => $code,
                'res_msg' => $msg,
                'status' => $status,
                'res_json' => $res_json,
            ];
            $result_error = (new PayAccountError())->save_error($condition);
            if( !$result_error ){
                Logger::dayLog('cunguan/notify', $user_id . $msg.'，存入pay_account_error数据库失败');
            }
    }

    private function bindBank($userInfo, $cardNo) {
        if (empty($cardNo)) {
            Logger::dayLog('cunguan/notify', $userInfo->user_id . '--' . $cardNo, 'cardNo为空');
            return false;
        }
        $cardInfo = (new User_bank)->getByCard($cardNo);
        if ($cardInfo) {
            if ($cardInfo->user_id == $userInfo->user_id) {
                $def_res = $cardInfo->updateDefaultBank($cardInfo->user_id, $cardInfo->id);
                if (!$def_res) {
                    Logger::dayLog('cunguan/notify', $cardInfo->user_id . '--' . $cardNo, '更新用户设置默认卡失败');
                    return false;
                }
                if ($cardInfo->status == 1) {
                    return true;
                }
                $up_des = $cardInfo->updateUserBank(['status' => 1]);
                if ( !$up_des) {
                    Logger::dayLog('cunguan/notify', $cardInfo->user_id . '--' . $cardNo, '更新用户status=1失败');
                    return false;
                }
                return true;
            }
            Logger::dayLog('cunguan/notify', $cardInfo->user_id . '--' . $cardNo, '存管返回的卡号已经被绑定');
            return false;
        }
        //获取卡片信息
        $cardbin = (new Card_bin())->getCardBinByCard($cardNo, "prefix_length desc");
        $area = (new Areas())->getAreaOrSubBank(1);
        $save_res = $this->saveUserBank($userInfo, $cardbin, $area, $cardNo);
        if (!$save_res) {
            Logger::dayLog('cunguan/notify', $cardInfo->user_id . '--' . $cardNo, '添加银行卡失败');
            return false;
        }
        $newCardInfo = (new User_bank)->getByCard($cardNo);
        if (!$newCardInfo) {
            Logger::dayLog('cunguan/notify', $cardInfo->user_id . '--' . $cardNo, '重新获取绑卡信息失败');
            return false;
        }
        $def = $newCardInfo->updateDefaultBank($newCardInfo->user_id, $newCardInfo->id);
        if (!$def) {
            Logger::dayLog('cunguan/notify', $cardInfo->user_id . '--' . $cardNo, '重新设置默认卡失败');
            return false;
        }
        return true;
    }

    private function saveUserBank($user, $cardbin, $area, $cardNo) {
        $condition['user_id'] = $user->user_id;
        $condition['type'] = empty($cardbin) ? '0' : $cardbin['card_type'];
        $condition['bank_abbr'] = empty($cardbin) ? '' : $cardbin['bank_abbr'];
        $condition['bank_name'] = empty($cardbin) ? '' : $cardbin['bank_name'];
        $condition['sub_bank'] = '';
        $condition['city'] = strval($area['city']);
        $condition['area'] = strval($area['area']);
        $condition['province'] = strval($area['province']);
        $condition['card'] = $cardNo;
        $condition['bank_mobile'] = $user->mobile;
        $condition['verify'] = 3;
        $ret_userbank = (new User_bank())->addUserbank($condition);
        return $ret_userbank;
    }

}
