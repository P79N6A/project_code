<?php

/**
 * 出款处理模型
 */

namespace app\models\remit;

use app\commonapi\apiInterface\Jiufuremit;
use app\commonapi\ImageHandler;
use app\commonapi\Logger;
use app\models\news\Areas;
use app\models\news\Favorite_contacts;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_extend;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\User_submit_list;
use Yii;
use yii\helpers\ArrayHelper;

class FundJf implements CapitalInterface {

    private $desc = [
        '1' => '购买原材料',
        '2' => '进货',
        '3' => '购买设备',
        '4' => '购买家具或家电',
        '5' => '学习',
        '6' => '个人或家庭消费',
        '7' => '资金周转',
        '8' => '租房',
        '9' => '物流运输',
        '10' => '其他',
        '11' => '个人或家庭消费资金周转',
    ];
    private $purpose = [
        '1' => 'F1103',
        '2' => 'F1105',
        '3' => 'F1108',
        '4' => 'F1109',
        '5' => 'F1111',
        '6' => 'F1112',
        '7' => 'F1113',
        '8' => 'F1114',
        '9' => 'F1115',
        '10' => 'F1199',
        '11' => 'F1112',
    ];

    /**
     * 明确错误码
     * @return []
     */
    public function getFails() {
        return [
            '160001',
            '160002',
            '160003',
            '160004',
            '160005',
            '160006',
            '160007',
            '160008',
            '160009',
            '160010',
            '160011',
            '160013',
            '160021',
            '160022',
            '160023',
            'invaild_error',
        ];
    }

    //命令行入口
    public function pay($oRemit) {
        $error_code = $this->getFails();
        $res = $this->setBalance($oRemit->loan_id, $oRemit->user_id, $oRemit->order_id);
        $res_code = ArrayHelper::getValue($res, 'res_code');
        $res_msg = ArrayHelper::getValue($res, 'res_msg');
        if ($res['res_code'] == '0000') {
            $res_msg = '';
            $req_id = isset($res['res_msg']['req_id']) ? $res['res_msg']['req_id'] : '';
            $client_id = isset($res['res_msg']['client_id']) ? $res['res_msg']['client_id'] : '';
            $res_code = isset($res['res_code']) ? trim($res['res_code']) : '';
            $result = $this->updateData($req_id, $client_id, $res_code);
            if (!$result) {
                Logger::dayLog("jiufusbumit", $oRemit->loan_id, "更改提交状态失败");
                $status = NULL;
            } else {
                $status = 'DOREMIT';
            }
        } else if (in_array($res['res_code'], $error_code)) {
            $status = 'FAIL';
        } else {
            $status = NULL;
        }
        $array['status'] = $status;
        $array['res_code'] = $res_code;
        $array['res_msg'] = $res_msg;
        return $array;
    }

    /**
     * 向玖富通道提交出款
     * @param [type] $order_id  [description]
     * @param [type] $amount    [description]
     * @param [type] $user      [description]
     * @param [type] $bank      [description]
     * @param string $loan_days [description]
     * @param string $loan_desc [description]
     */
    private function setBalance($loan_id, $user_id, $order_id) {
        $loan_info = $this->getLoaninfo($loan_id);
        $user_info = $this->getUserinfo($user_id);
        $bank_info = $this->getBankinfo($loan_info->bank_id);
        $settle_amount = number_format($loan_info->amount, 2, '.', '');

        $result = $this->saveData($order_id, $loan_id, $settle_amount, $loan_info->bank_id, $user_id);
        if (!$result) {
            return false;
        }

        $res = $this->jiufuPay($order_id, $user_info, $bank_info, $settle_amount, $loan_info->days, $loan_info->desc);
        return $res;
    }

    private function saveData($order_id, $loan_id, $settle_amount, $bank_id, $user_id) {
        $postData = [
            'order_id' => $order_id,
            'loan_id' => $loan_id,
            'settle_request_id' => '',
            'settle_amount' => $settle_amount,
            'bank_id' => $bank_id,
            'user_id' => $user_id,
            'type' => 1
        ];

        $user_submit = new User_submit_list();
        $result = $user_submit->saveSubmit($postData);

        return $result;
    }

    private function updateData($order_id, $settle_request_id, $rsp_code) {
        $user_submit = new User_submit_list();
        $result = $user_submit->updateSubmit($order_id, $settle_request_id, $rsp_code);

        return $result;
    }

    /**
     * 玖富提交接口
     * @param  [type] $order_id  [description]
     * @param  [type] $user      [description]
     * @param  [type] $bank      [description]
     * @param  [type] $amount    [description]
     * @param  [type] $loan_days [description]
     * @param  [type] $loan_desc [description]
     */
    private function jiufuPay($order_id, $user, $bank, $amount, $loan_days, $loan_desc) {
        $user_extend = User_extend::find()->where(['user_id' => $user->user_id])->one();
        $img_url = $third_answer = ImageHandler::getUrl($user->pic_identity);

        if (empty($bank->province) || empty($bank->city) || empty($bank->area)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '银行省份失败'];
        }
        $area_province = Areas::find()->where(['id' => $bank->province])->one();

        if (empty($area_province)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '省份失败'];
        }
        $area_city = Areas::find()->where(['id' => $bank->city])->one();
        if (empty($area_city)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '城市失败'];
        }

        $area_county = Areas::find()->where(['id' => $bank->area])->one();


        if (empty($area_county)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '县市失败'];
        }
        $customer_sex = $this->get_sex($user->identity);
        $search = array_search($loan_desc, $this->desc);
        if (!$search) {
            $search = 1;
        }
        $loan_purpose = $this->purpose[$search];
        if (empty($user_extend->home_area)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '家乡失败'];
        }
        $area_home_county = Areas::find()->where(['code' => $user_extend->home_area])->one();

        if (empty($area_home_county) || empty($area_home_county->pID)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '家乡县市失败'];
        }
        $area_home_city = Areas::find()->where(['id' => $area_home_county->pID])->one();

        if (empty($area_home_city) || empty($area_home_city->pID)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '家乡城市失败'];
        }
        $area_home_province = Areas::find()->where(['id' => $area_home_city->pID])->one();

        if (empty($user_extend->company_area)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '公司区域失败'];
        }
        $area_company_county = Areas::find()->where(['code' => $user_extend->company_area])->one();

        if (empty($area_company_county) || empty($area_company_county->pID)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '公司县市失败'];
        }
        $area_company_city = Areas::find()->where(['id' => $area_company_county->pID])->one();

        if (empty($area_company_city) || empty($area_company_city->pID)) {
            return ['res_code' => 'invaild_error', 'res_msg' => '公司城市失败'];
        }
        $area_company_province = Areas::find()->where(['id' => $area_company_city->pID])->one();


        $favorite_contacts = Favorite_contacts::find()->where(['user_id' => $user->user_id])->one();
        if (empty($favorite_contacts)) {
            return ['res_code' => 'invaild_error', 'res_msg' => 'l联系人失败'];
        }

        $params = [
            'req_id' => $order_id, //订单ID
            'name' => $user->realname, //姓名
            'idcard' => $user->identity, //身份证号
            'phone' => $user->mobile, //
            'email' => $user_extend->email,
            'cardno' => $bank->card,
            'guest_account_bank' => $bank->bank_name,
            'guest_account_bank_branch' => $bank->sub_bank,
            'img_url' => $img_url,
            'guest_account_province' => isset($area_province->name) ? $area_province->name : '',
            'guest_account_city' => isset($area_city->name) ? $area_city->name : '',
            'province_id' => isset($area_province->code) ? $area_province->code : '',
            'city_id' => isset($area_city->code) ? $area_city->code : '',
            'county_id' => isset($area_county->code) ? $area_county->code : '',
            'settle_amount' => $amount,
            'customer_sex' => $customer_sex,
            'time_limit' => $loan_days,
            'loan_purpose' => $loan_purpose,
            'callbackurl' => Yii::$app->params['jiufu_remit'],
            'liveaddressProvince' => $area_home_province->name,
            'liveaddressCity' => $area_home_city->name,
            'liveaddressDistinct' => $area_home_county->name,
            'liveaddressRoad' => $user_extend->home_address,
            'contactName' => $favorite_contacts->contacts_name,
            'contactPhone' => $favorite_contacts->mobile,
            'contractCode' => "XHHCE" . date('Ymd') . (string) time(),
            'phonePassword' => rand(100000, 999999),
            'company' => $user_extend->company,
            'companyPhone' => $user->mobile,
            'companyAdressprovince' => $area_company_province->name,
            'companyAdressCity' => $area_company_city->name,
            'companyAdressDist' => $area_company_county->name,
            'companyAdressRoad' => $user_extend->company_address,
            'companyType' => 'B0908',
            'beginCompanyDate' => '',
            'product_id' => '215'
        ];
        if (SYSTEM_ENV != 'prod') {
            return ['res_code' => '0000', 'res_msg' => [
                    'req_id' => $order_id,
                    'client_id' => 'L1NJ20171010040507791329',
                    'order_id' => '11137021',
                    'settle_amount' => '1500.00',
                    'remit_status' => '0',
                    'rsp_status' => '0',
                    'rsp_status_text' => 'SUBMITOK'
                ]
            ];
            return ['res_code' => '160004', 'res_msg' => '失败了!'];
            return ['res_code' => '2222', 'res_msg' => '中断了!'];
        }
        $apihttp = new Jiufuremit();
        $res = $apihttp->outBlance($params);
        return $res;
    }

    /**
     * 根据身份证号获取性别
     * @param  [type] $identity [description]
     * @return [type]           [description]
     */
    private function get_sex($identity) {
        if (empty($identity))
            return '';
        $sexint = (int) substr($identity, 16, 1);
        //0 女；1 男
        return $sexint % 2 === 0 ? 0 : 1;
    }

    /**
     * 获取借款信息
     */
    private function getLoaninfo($loan_id) {
        return User_loan::findOne($loan_id);
    }

    /**
     * 获取用户信息
     */
    private function getUserinfo($user_id) {
        return User::findOne($user_id);
    }

    /**
     * 获取用户银行卡信息
     */
    private function getBankinfo($bank_id) {
        return User_bank::findOne($bank_id);
    }

    public function hitRule() {
        // TODO: Implement hitRule() method.
    }

    public function isSupport($oLoan) {
        $loan = $oLoan->loan;
        if (!$loan) {
            return false;
        }
        if (!empty($oLoan->remit)) {
            $fundIds = array_map(function($record) {
                return $record->attributes['fund'];
            }, $oLoan->remit);
            if (in_array(CapitalInterface::JF, $fundIds)) {
                return false;
            }
        }
        //周六周日不推单
        if (date("w") == 6 || date("w") == 0) {
            return false;
        }
        if ($loan->amount != $loan->withdraw_fee * 10) {
            return false;
        }
        if ($loan->days != 28) {
            return false;
        }
        if ($loan->is_calculation == 0) {
            return false;
        }
        return true;
    }

}
