<?php
namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apidepository;
use app\commonapi\Apihttp;
use app\commonapi\Bank;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\news\Areas;
use app\models\news\Card_bin;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class EditbankController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $userId = Yii::$app->request->post('user_id');
        $editType = Yii::$app->request->post('edit_type');

        if (empty($version) || empty($userId) || empty($editType)) {
            exit($this->returnBack('99994'));
        }
        $userObj = (new User())->getById($userId);
        if (empty($userObj)) {
            exit($this->returnBack('10001'));
        }
        if ($editType == 1) {
            $result = $this->addBank($userObj);
            exit($this->returnBack($result));
        } else {
            $result = $this->delBank($userId,$editType);
            if(is_array($result)){
                $result_data['del_bank_url'] = $result['data'] ;
                exit($this->returnBack($result['code'], $result_data));
            }
            exit($this->returnBack($result));
        }
    }

    private function addBank($userObj)
    {
        $card = Yii::$app->request->post('card');//银行卡号
        $bankType = Yii::$app->request->post('bank_type');//0所有 1储蓄卡 2信用卡
        $bankMobile = Yii::$app->request->post('bank_mobile', '');//预留手机号，可选（可选：用户注册手机号）
        $bankMobile = !empty($bankMobile) ? $bankMobile : $userObj->mobile;
        if (empty($card) || (empty($bankType) && $bankType != 0) || empty($bankMobile)) {
            exit($this->returnBack('99994'));
        }

        //十张卡限制
        $bank = new User_bank();
        $userBankObj = $bank->getBankByUserId($userObj->user_id);
        $count = count($userBankObj);
        if ($count >= 10) {
            exit($this->returnBack('10088'));
        }
        //检测卡片类型
        $cardbin = (new Card_bin())->getCardBinByCard($card, "prefix_length desc");
        $supportCard = $this->chkSupportCard($cardbin);
        if ($supportCard) {
            exit($this->returnBack($supportCard));
        }
        //判断绑卡类型
        if ($bankType == 1) {
            if ($cardbin['card_type'] != 0) {
                exit($this->returnBack('10091'));
            }
        }
        if ($bankType == 2) {
            if ($cardbin['card_type'] != 1) {
                exit($this->returnBack('10092'));
            }
        }
        //四要素鉴权
        $result = $this->chkBank($userObj, $bankMobile, $card, $cardbin['card_type']);
        //四要素鉴权通道
        $verify = 1;
        if (!empty($result) && isset($result['res_data']['channel_id'])) {
            $verify = !empty($result['res_data']['channel_id']) ? $result['res_data']['channel_id'] : 1;
        }

        $result = $this->saveBank($userObj->user_id, $card, $bankMobile, $verify);
        exit($this->returnBack($result));
    }

    //四要素鉴权
    private function chkBank($userObj, $bankMobile, $card, $bankType)
    {
        $postData = array(
            'username' => $userObj->realname,
            'idcard' => $userObj->identity,
            'cardno' => $card,
            'phone' => $bankMobile,
            'identityid' => $userObj->user_id,
            'card_type' => $bankType,//1信用卡 非1储蓄卡
        );
        $openApi = new Apihttp;
        $result = $openApi->bankInfoValids($postData);
        Logger::dayLog('app/creditbank', "四要素鉴权", $userObj->user_id, $postData, $result);
        if ($result['res_code'] != '0000') {
            $resMsg = '';
            switch ($result['res_msg']) {
                case 'DIFFERENT':
                    $resMsg = '请优先确认您输入的手机号码与办理银行卡时预留手机号码一致<br>请确认您的银行卡号是否填写正确';
                    break;
                case 'ACCOUNTNO_INVALID':
                    $resMsg = '请核实您的银行卡状态是否有效';
                    break;
                case 'ACCOUNTNO_NOT_SUPPORT':
                    $resMsg = '暂不支持此银行，请更换您的银行卡';
                    break;
                default:
                    $resMsg = $result['res_msg'];
            }
            exit($this->returnBack($result['res_code'], [], $resMsg));
        }
        return $result;
    }

    private function delBank($userId,$editType)
    {
        $bank_id = Yii::$app->request->post('bank_id');
        if (empty($bank_id)) {
            return '99994';
        }
        $userbank = User_bank::findOne($bank_id);
        if (empty($userbank) || $userbank->status == 0) {
            return '10043';
        }
        if ($userbank->user_id != $userId) {
            return '10044';
        }
        $bankModel = new User_bank();
        if ($userbank->type == 0) {
            $userbanks = $bankModel->getBankByUserId($userId, 0);
            if (count($userbanks) <= 1) {
                return '10045';
            }
        }
        $loan = new User_loan();
        $loanlist = $loan->getUserLoan($userId, array('1', '2', '5', '6', '9', '10', '11', '12', '13'), array('1', '4'));
        $userloan = $loanlist[0];
        if (!empty($loanlist) && $userloan->bank_id == $bank_id) {
            return '10240';
        }

        //判断此卡是否是存管开户的银行卡
        $payaccount = new Payaccount();
        $isOpen = $payaccount->getPaysuccessByUserId($userbank->user_id, 2, 1);
        if ($isOpen && $isOpen->card == $userbank->id) {
            $isSetpass = $payaccount->getPaysuccessByUserId($userbank->user_id, 2, 2);
            if (empty($isSetpass) || $isSetpass->activate_result != 1) {
                return '10213';
            }
            //解除存管内的卡
            $userInfo = User::findOne($userId);
            $de_result = $this->depositoryoverbind($userInfo, $isOpen, $userbank , $editType);
            if (!$de_result) {
                return '10046';
            }
            if( !empty($de_result) && $de_result['res_code'] == 0 &&  !empty($de_result['res_data']) ){
                 return ['code' => '0000','data' => $de_result['res_data']];
            }
            
        }
        $result = $userbank->updateUserBank(array('status' => 0, 'default_bank' => 0));
        if (!$result) {
            return '10046';
        }

        return '0000';
    }

    private function saveBank($userId, $card, $bankMobile, $verify)
    {
        $bank_num = User_bank::find()->where(['card' => $card, 'status' => 1])->count();
        if ($bank_num > 0) {
            return '10040';
        }
        $cardbinModel = new Card_bin();
        $card_bin = $cardbinModel->getCardBinByCard($card);
        if (empty($card_bin)) {
            return '10041';
        }

        $areaId = (new Areas())->getAreaOrSubBank(1);
        $subBank = (new Areas())->getAreaOrSubBank(2);
        $condition = array(
            'user_id' => $userId,
            'type' => $card_bin['card_type'],
            'bank_abbr' => $card_bin['bank_abbr'],
            'bank_name' => $card_bin['bank_name'],
            'card' => $card,
            'bank_mobile' => $bankMobile,
            'default_bank' => 0,
            'status' => 1,
            'verify' => $verify,
            'province' => (string)$areaId['province'],
            'city' => (string)$areaId['city'],
            'area' => (string)$areaId['province'],
            'sub_bank' => $subBank,
            'is_new' => 1,
        );

        $bank = User_bank::find()->where(['card' => $card, 'user_id' => $userId, 'default_bank' => 0])->one();
        if (empty($bank)) {
            $result = (new User_bank())->addUserBank($condition);
        } else {
            $result = (new User_bank())->updateUserBank($condition);
        }
        if ($result) {
            return '0000';
        } else {
            return '10042';
        }
    }

    //检测是否支持银行卡
    private function chkSupportCard($cardbin)
    {
        //只支持借记卡，信用卡
        $card_type_arr = [0, 1];
        if (empty($cardbin) || !in_array($cardbin['card_type'], $card_type_arr)) {
            return 10093;
        }

        $bank_array = Keywords::getBankAbbr();
        //卡片不支持
        if (!in_array($cardbin['bank_abbr'], $bank_array[$cardbin['card_type']])) {
            return 10093;
        }
    }

    private function depositoryoverbind($userInfo, $payaccount, $userbank, $editType)
    {
        $notifyUrl = Yii::$app->request->hostInfo . '/new/getunbindcardnotify';
        if($editType==3){
            $come_from = 13; //立即提现弹窗解卡流程
            $notifyUrl = Yii::$app->request->hostInfo . '/new/getunbindcardnotify?type=1';
        }else{
            $come_from = 11; //解卡
        }

        $condition = [
            'channel' => '000002',
            'from' => 1,
            'isUrl' => 1 ,
            'idType' => '01',
            'order_id' => date('YmdHis') . rand(1000, 9999),  //订单号 唯一标识
            'accountId' => $payaccount->accountId,
            'idNo' => $userInfo->identity,
            'name' => $userInfo->realname,
            'mobile' => $userInfo->mobile,
            'cardNo' => $userbank->card,
            'retUrl' => Yii::$app->request->hostInfo . '/borrow/custody/waiting?type=' . $come_from . '&user_id=' . $userInfo->user_id, //前台跳转链接
            'forgotPwdUrl' => Yii::$app->request->hostInfo . '/borrow/custody/setpwdnew?userid=' . $userInfo->user_id . '&type=9', //忘记密码跳转
            'notifyUrl' => $notifyUrl, //后台通知链接
            'acqRes' => "$userInfo->user_id",
        ];
        $deposiApi = new Apidepository();
        $result = json_decode( $deposiApi->cgOvercard($condition),true );
         if ( $result['res_code'] != 0 ) {
            if( isset($result['rsp_data'])){
                Logger::dayLog('app/creditbank', $result['rsp_data'], 'user_id->' . $userInfo->user_id);
            } 
            if( isset($result['rsp_msg'])){
                Logger::dayLog('app/creditbank', $result['rsp_msg'], 'user_id->' . $userInfo->user_id);
            }
            return false;
        }
        return $result;
    }
}
