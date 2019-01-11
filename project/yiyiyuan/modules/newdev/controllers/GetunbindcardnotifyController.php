<?php

namespace app\modules\newdev\controllers;

use app\models\news\Areas;
use app\models\news\Card_bin;
use app\models\news\Payaccount;
use app\models\news\PayAccountError;
use app\commonapi\Logger;
use app\models\news\User_bank;  

class GetunbindcardnotifyController extends NewdevController {
    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }
    
    
    /*
     * 接收存管解绑卡结果
     */
    public function actionIndex() {
        $postData = $this->post();
        $type = $this->get('type', 0); //1:提现-解绑卡
        Logger::errorLog(print_r($postData, true), 'UnbindcardpenNotify', 'debt');
        if(!is_array($postData) || !isset($postData['data'])){
            exit('error1');
        }
        
//        $postData['data'] = '{"res_code":0,"res_data":"{\"accountId\":\"6212462040000011363\",\"txState\":\"0\",\"retCode\":\"00000000\",\"retMsg\":\"交易成功\",\"order_id\":\"6212462040000011363c204220\",\"cardNo\":\"0\",\"acqRes\":\"204220\"}"}';
        
        $result = json_decode($postData['data'], true);
        Logger::errorLog(print_r($result, true), 'UnbindcardpenNotify', 'debt');
        if (!$result) {
            exit('error2');
        }
        $res_data = $result['res_data'];
        $res_arr = json_decode($res_data, true);
        if (!is_array($res_arr) || !isset($res_arr['retCode']) || !isset($res_arr['accountId']) || !isset($res_arr['acqRes']) ) {
            exit('error3');
        }
        $user_id = $res_arr['acqRes'];
        $res_json = $postData['data'];
        //将接收的结果记录到pay_account_error数据库
        $this->saveErrorPayaccount($user_id,$res_arr['retCode'],$res_arr['retMsg'],$res_json);  
   
        $accountId = $res_arr['accountId'];
        $payaccount = new Payaccount();
   

        //解卡
        $pass_res = false;
        if ($result['res_code'] == 0 && $res_arr['retCode'] == '00000000') {
            
            $isOpen = $payaccount->getPaysuccessByUserId($user_id, 2, 1);
            if( empty($isOpen) || ( $isOpen->accountId != $accountId ) ){
                 Logger::dayLog('cunguan/notify/getunbindcardnotify', $isOpen->accountId ,$accountId,'accountId不一致');
                 exit('error4');
            }
            $bank_id = $isOpen->card;
            $userbank = User_bank::findOne($bank_id);
            if ( empty($userbank) ) {
                 //该银行卡不存在
                Logger::dayLog('cunguan/notify/getunbindcardnotify', $bank_id ,'银行卡不存在');
                exit('userbank error');
            }
            $set_re = $isOpen->setCard('');
            $del_res = $userbank->delUserBank();
            if($set_re  && $del_res){
               $pass_res = true;
            }
        }else{
           if( $type == 1 ){
                //将提现时存管卡无法提现情况下解绑卡时无法解除的 错误记录下来 
                $this->saveErrorPayaccount($user_id,'txcgfail001',$res_arr['retMsg'],$res_json,$status = 0,$type=6); 
           }
        } 
       
        if (!$pass_res) {
            exit('error');
        }
        return "SUCCESS";
    }
    
    /**
     * 
     * @param type $user_id
     * @param type $code 错误码
     * @param type $msg  错误提示信息
     * @param type $type 1:开户 2：密码重置 3：四合一授权 4：解绑卡  5：密码修改
     */
    private function saveErrorPayaccount( $user_id,$code,$msg,$res_json,$status = 0,$type=4){
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
                if ($cardInfo->status == 1) {
                    return true;
                }
                $def_res = $cardInfo->updateDefaultBank($cardInfo->user_id, $cardInfo->id);
                $up_des = $cardInfo->updateUserBank(['status' => 1]);
                if (!$def_res || !$up_des) {
                    Logger::dayLog('cunguan/notify', $cardInfo->user_id . '--' . $cardNo, '更新用户status=1失败或设置默认卡失败');
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

    /**
     * 修改银行卡回调
     */
    public function actionEditbank(){
        $postData = $this->post();
        Logger::errorLog(print_r($postData, true), 'EditPwdNotify', 'debt');
        if(empty($postData)){
            exit();
        }
        echo 'SUCCESS';

    }

}
