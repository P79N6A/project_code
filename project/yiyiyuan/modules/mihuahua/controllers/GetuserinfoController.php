<?php
namespace app\modules\mihuahua\controllers;

use app\models\news\Juxinli;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use app\modules\mihuahua\common\ApiController;
use Yii;

class GetuserinfoController extends ApiController
{
    public $enableCsrfValidation = false;
    private $user;
    private $account = '';
    private $living = '';
    private $contact = '';
    private $juxinli = '';
    private $bank = '';

    public function actionIndex()
    {
        $required = ['identity'];//必传参数
        $this->BeforeVerify($required, $this->data);

        //用户基本信息
        $userObj = (new User())->getUserinfoByIdentity($this->data['identity']);
        if (empty($userObj)) {
            $this->codeReback('10001');
        }
        $this->user = $userObj;

        //开户信息
        $this->getAccount();
        //活体信息
        $this->getLiving();
        //联系人信息
        $this->getContact();
        //运营商信息
        $this->getJuxinli();
        //银行卡信息
        $this->getBank();

        $result = $this->getDataArray();
        $this->codeReback('0000', $result);
    }

    //获取银行卡信息
    private function getBank()
    {
        $bankModel = new User_bank();
        $userBankObj = $bankModel->getBankByUserId($this->user->user_id, 0);
        if (!empty($userBankObj)) {
            $bank = [];
            foreach ($userBankObj as $key => $value) {
                $bank[$key]['card'] = $value->card;
                $bank[$key]['type'] = $value->type;
                $bank[$key]['bank_abbr'] = $value->bank_abbr;
                $bank[$key]['bank_name'] = $value->bank_name;
                $bank[$key]['bank_mobile'] = $value->bank_mobile;
                $bank[$key]['verify'] = $value->verify;
                $bank[$key]['last_modify_time'] = $value->last_modify_time;
            }
            $this->bank = $bank;
        }
    }

    //获取运营商信息
    private function getJuxinli()
    {
        $juxinliModel = new Juxinli();
        $isJuxinli = $juxinliModel->isAuthYunyingshang($this->user->user_id);
        $juxinli['requestid'] = '';
        $juxinli['status'] = '2';
        $juxinli['time'] = '';
        if ($isJuxinli) {
            $juxinliObj = $this->user->getJuxinli(1)->one();
            $juxinli['requestid'] = $juxinliObj->requestid;
            $juxinli['status'] = '1';
            $juxinli['time'] = $juxinliObj->last_modify_time;
        }
        $this->juxinli = $juxinli;
    }

    //获取联系人信息
    private function getContact()
    {
        $contact['relation_common'] = '';
        $contact['contacts_name'] = '';
        $contact['mobile'] = '';
        $contact['relation_family'] = '';
        $contact['relatives_name'] = '';
        $contact['phone'] = '';
        if (!empty($this->user->favorite)) {
            $contact['relation_common'] = !empty($this->user->favorite->relation_common) ? $this->user->favorite->relation_common : '';
            $contact['contacts_name'] = !empty($this->user->favorite->contacts_name) ? $this->user->favorite->contacts_name : '';
            $contact['mobile'] = !empty($this->user->favorite->mobile) ? $this->user->favorite->mobile : '';
            $contact['relation_family'] = !empty($this->user->favorite->relation_family) ? $this->user->favorite->relation_family : '';
            $contact['relatives_name'] = !empty($this->user->favorite->relatives_name) ? $this->user->favorite->relatives_name : '';
            $contact['phone'] = !empty($this->user->favorite->phone) ? $this->user->favorite->phone : '';
        }
        $this->contact = $contact;
    }

    //获取活体信息
    private function getLiving()
    {
        $living['score'] = '';
        $living['pic_url'] = '';
        $living['iden_url'] = '';
        $living['iden_address'] = '';
        if (!empty($this->user->password)) {
            $living['score'] = !empty($this->user->password->score) ? $this->user->password->score : '';
            $living['pic_url'] = !empty($this->user->password->pic_url) ? $this->user->password->pic_url : '';
            $living['iden_url'] = !empty($this->user->password->iden_url) ? $this->user->password->iden_url : '';
            $living['iden_address'] = !empty($this->user->password->iden_address) ? $this->user->password->iden_address : '';
        }
        $this->living = $living;
    }

    //获取开户信息
    private function getAccount()
    {
        $payAccountModel = new Payaccount();
        $OpenObj = $payAccountModel->getPaystatusByUserId($this->user->user_id, 2, 1);
        if (!empty($OpenObj)) {
            $account['open_time'] = $OpenObj->activate_time;
            $account['account_id'] = $OpenObj->accountId;
            $account['open_bank'] = '';
            if (!empty($OpenObj->card)) {
                $account['open_bank'] = !empty($OpenObj->bank) ? $OpenObj->bank->card : '';
            }
            $setPasswordObj = $payAccountModel->getPaystatusByUserId($this->user->user_id, 2, 2);
            $account['is_password'] = 0;
            $account['is_password_time'] = '';
            if (!empty($setPasswordObj)) {
                $account['is_password'] = 1;
                $account['is_password_time'] = $OpenObj->activate_time;
            }
            $this->account = $account;
        }
    }

    //获取返回数据
    private function getDataArray()
    {
        $data = [
            'mobile' => $this->user->mobile,
            'realname' => $this->user->realname,
            'identity' => $this->user->identity,
            'status' => $this->user->status,
            'verify_time' => $this->user->verify_time,
            'account' => $this->account,
            'living' => $this->living,
            'contact' => $this->contact,
            'juxinli' => $this->juxinli,
            'bank' => $this->bank
        ];
        return $data;
    }

    private function codeReback($code, $data = [])
    {
        $result = $this->errorreback($code);
        if (!empty($data)) {
            $result['data'] = $data;
        }
        exit(json_encode($result));
    }
}
