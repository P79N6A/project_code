<?php

namespace app\modules\sysloanguide\controllers;

use app\commonapi\Common;
use app\commonapi\Http;
use app\models\day\User_guide;
use app\models\dev\Score;
use app\models\news\Address;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\models\own\Address_list;
use app\modules\sysloan\common\ApiController;

class UserController extends ApiController {

    public $enableCsrfValidation = false;
    /**
     * 获取通讯录
     */
    public function actionGetaddresslist() {
        $required = ['user_id'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify = $this->BeforeVerify($required, $httpParams);
		$selfUser = User_guide::findOne($httpParams['user_id']);
		//获取一亿元用户ID
		$yyyUser = User::find()->where(['identity'=>$selfUser['identity']])->one();
        $mobileInfo = (new Address_list)->findAllMobile($yyyUser['user_id']);
        $array = $this->result('0000', $mobileInfo);
        exit(json_encode($array));
    }

    /**
     * 获取用户信息
     */
    public function actionGetuserinformation() {
        $required = ['user_id'];  //必传参数
        $httpParams = $this->post(); //获取参数
        $verify = $this->BeforeVerify($required, $httpParams);
		$selfUser = User_guide::findOne($httpParams['user_id']);
		//获取一亿元用户ID
		$yyyUser = User::find()->where(['identity'=>$selfUser['identity']])->one();
		
        $fuser = (new User())->getUserinfoByUserId($yyyUser['user_id']);
        if (empty($fuser)) {
            $array = $this->errorreback('10001');
            exit(json_encode($array));
        }
        //获取用户贷款次数
        $loanCount = (new User_loan())->getVerifyUserLoanCount($fuser->user_id);
        $array = $this->userresult('0000', $fuser, $loanCount);
        exit(json_encode($array));
    }

    private function userresult($code, $object, $loanCount = 0) {
        $array = $this->errorreback($code);
        $array['user_information'] = [];
        if (empty($object)) {
            return $array;
        }

        $default = ['nation', 'iden_address', 'marriage', 'income', 'edu', 'email', 'company_name', 'company_phone', 'company_address', 'industry', 'profession', 'position', 'home_address', 'relatives_name', 'relatives_phone', 'contacts_name', 'contacts_phone', 'pic_url', 'pic_identity'];
        foreach ($default as $k => $v) {
            $data[$v] = '';
        }
        $data['loan_count'] = $loanCount;
        $data['user_name'] = $object['realname'];
        $data['user_id'] = $object['user_id'];
        $data['status'] = $object['status'];
        $data['identity'] = $object['identity'];
        $data['mobile'] = $object['mobile'];
        $data['from_code'] = $object['from_code'];
        $data['owner'] = $this->gethome($object['identity']);
        $data['moblehome'] = $this->getmobile($object['mobile']);

        if (isset($object->password) && !empty($object->password)) {
            $data['pic_identity'] = $object->password->iden_url;
            $data['nation'] = $object->password->nation;
            $data['pic_url'] = $object->password->pic_url;
            $data['iden_address'] = $object->password->iden_address;
        }
        if (isset($object->extend) && !empty($object->extend)) {
            $data['marriage'] = $object->extend->marriage;
            $data['income'] = $object->extend->income;
            $data['edu'] = $object->extend->edu;
            $data['email'] = $object->extend->email;
            $data['company_name'] = $object->extend->company;
            $data['company_phone'] = $object->extend->telephone;
            $data['company_address'] = $object->extend->company_address;
            $data['industry'] = $object->extend->industry;
            $data['profession'] = $object->extend->profession;
            $data['position'] = $object->extend->position;
            $data['home_address'] = $object->extend->home_address;
        }

        $juxinli = $object->getJuxinli(1)->one();
        $data['juxinli_create_time'] = !empty($juxinli) ? $juxinli->create_time : '';
        $data['juxinli_modify_time'] = !empty($juxinli) ? $juxinli->last_modify_time : '';
        $data['source'] = !empty($juxinli) ? $juxinli->source : '';

        if (isset($object->favorite) && !empty($object->favorite)) {
            $data['relatives_name'] = $object->favorite->relatives_name;
            $data['relatives_phone'] = $object->favorite->phone;
            $data['contacts_name'] = $object->favorite->contacts_name;
            $data['contacts_phone'] = $object->favorite->mobile;
        }

        $data['address_information']['gps_list'] = $this->getGpsList($object['user_id']);
        $data['bank_information'] = $this->getBankList($object['user_id'], $object['realname']);
        $array['user_information'] = $data;
        return $array;
    }

    private function getBankList($userId, $name) {
        $where = ['user_id' => $userId];
        $bankList = (new User_bank)->getBankByConditions($where);
        $data = [];
        if (!empty($bankList)) {
            foreach ($bankList as $k => $v) {
                $data[$k]['open_user_name'] = $name;
                $data[$k]['open_account'] = $v['card'];
                $data[$k]['open_bank'] = $v['bank_name'];
                $data[$k]['bank_mobile'] = $v['bank_mobile'];
                $data[$k]['card_type'] = $v['type'];
                $data[$k]['card_status'] = $v['status'];
                $data[$k]['bank_id'] = $v['id'];
                $data[$k]['verify'] = $v['verify'];
            }
        }

        return $data;
    }

    private function getGpsList($userId) {
        $gpsList = (new Address)->getAddressByUserId($userId);
        $data = [];
        if (!empty($gpsList)) {
            foreach ($gpsList as $k => $v) {
                $data[$k]['gps_address'] = $v['address'];
                $data[$k]['gpr_create_time'] = $v['create_time'];
            }
        }
        return $data;
    }

    private function getmobile($mobile) {
        //手机归属地
        $mobilehome = Http::mobileHome($mobile, 'txt');
        if (empty($mobilehome)) {
            $mobilehome = '北京';
        } else {
            $mobilehome = $mobilehome['province'];
        }
        return $mobilehome;
    }

    private function gethome($identity) {
        //身份归属地
        $owner = Score::find()->andWhere(['type' => 'city', 'number' => substr($identity, 0, 4)])->one();
        $owner = !empty($owner) ? $owner->name : '';
        return $owner;
    }

    private function result($code, $object) {
        $array = $this->errorreback($code);
        $array['address_list'] = [];
        if (empty($object)) {
            return $array;
        }
        foreach ($object as $key => $val) {
            $data[$key]['address_name'] = $val['name'];
            $data[$key]['addresss_mobile'] = $val['phone'];
        }
        $array['address_list'] = $data;
        return $array;
    }

}
