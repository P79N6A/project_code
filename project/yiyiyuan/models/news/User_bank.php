<?php

namespace app\models\news;

use app\commonapi\Apihttp;
use app\commonapi\Keywords;
use app\commonapi\Logger;
use app\models\BaseModel;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "yi_user_bank".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $type
 * @property string $bank_abbr
 * @property string $bank_name
 * @property string $province
 * @property string $city
 * @property string $area
 * @property string $sub_bank
 * @property string $card
 * @property string $bank_mobile
 * @property integer $default_bank
 * @property string $validate
 * @property string $cvv2
 * @property integer $status
 * @property integer $verify
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $is_new
 */
class User_bank extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'type', 'default_bank', 'status', 'verify', 'is_new'], 'integer'],
            [['bank_mobile'], 'required'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['bank_abbr', 'bank_name', 'province', 'city', 'area'], 'string', 'max' => 20],
            [['sub_bank', 'card'], 'string', 'max' => 64],
            [['bank_mobile'], 'string', 'max' => 12],
            [['validate'], 'string', 'max' => 6],
            [['cvv2'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'bank_abbr' => 'Bank Abbr',
            'bank_name' => 'Bank Name',
            'province' => 'Province',
            'city' => 'City',
            'area' => 'Area',
            'sub_bank' => 'Sub Bank',
            'card' => 'Card',
            'bank_mobile' => 'Bank Mobile',
            'default_bank' => 'Default Bank',
            'validate' => 'Validate',
            'cvv2' => 'Cvv2',
            'status' => 'Status',
            'verify' => 'Verify',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'is_new' => 'Is New',
        ];
    }

    public function getBankById($id) {
        $id = intval($id);
        if (empty($id)) {
            return null;
        }

        return self::findOne($id);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getBankByConditions($conditions) {
        return self::find()->where($conditions)->all();
    }

    /**
     * 获取用户银行卡信息
     * @param $user_id
     * @param int $type 0、借记卡，1、信用卡，2、借记卡+信用卡
     * @param string $order
     * @param array $condition
     * @return array|null|ActiveRecord[]|static
     */
    public function getBankByUserId($user_id, $type = 2, $order = '', $condition = array()) {
        if (empty($user_id)) {
            return null;
        }
        $bank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1]);
        if ($type != 2) {
            $bank = $bank->andWhere(['type' => $type]);
        }
        if (!empty($order)) {
            $bank = $bank->orderBy($order);
        }
        if (!empty($condition)) {
            $bank = $bank->andWhere($condition);
        }
        $bank = $bank->all();
        return $bank;
    }

    /**
     * 获取用户银行卡信息 数组
     * @param $user_id
     * @param int $type 0、借记卡，1、信用卡，2、借记卡+信用卡
     * @param string $order
     * @param array $condition
     * @return array|null|ActiveRecord[]|static
     */
    public function getBankArr($user_id, $type = 2, $order = '', $condition = array()) {
        if (empty($user_id)) {
            return null;
        }
        $bank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1]);
        if ($type != 2) {
            $bank = $bank->andWhere(['type' => $type]);
        }
        if (!empty($order)) {
            $bank = $bank->orderBy($order);
        }
        if (!empty($condition)) {
            $bank = $bank->andWhere($condition);
        }
        $bank = $bank->asArray()->all();
        return $bank;
    }



    /**
     * 银行卡限制规则排序
     * @param array $userid  用户id
     * @param int $type 0:出款,1:还款，2全部
     * @param int $loan_type
     * @param int $mark 299 新标识 1：299
     * @return array 新增sign键 1为限制卡 2为可用卡
     */
    public function limitCardsSort($userid, $type = 2, $loan_type = 0, $mark = 0) {
        if (empty($userid)) {
            return false;
        }

        $limit_arr = array();
        $where = [
            'AND',
            ['user_id' => $userid],
            ['status' => 1],
        ];
        $bankType = 0;// 0储蓄卡 1信用卡 2全部
        if ($type == 0) {
            switch ($loan_type) {
                case 0:
                    $payaccount = Payaccount::find()->where(['user_id' => $userid, 'type' => 2, 'step' => 1, 'activate_result' => 1])->one();
                    if (!empty($payaccount) && !empty($payaccount->card)) {
                        $card = self::find()->where(['user_id' => $userid, 'id' => $payaccount->card, 'status' => 1])->asArray()->one();
                        if (!empty($card)) {
                            $cards_allow[0] = $card;
                            $cards_allow[0]['sign'] = 2;
                            return $cards_allow;
                        }
                    }
                    $bankType = 0;
                    break;
                case 1:
                    $bankType = 1;
                    break;
            }
        }
        if($type == 2 && in_array($loan_type,[0,1])){
            $bankType = $loan_type;
        }
        if($bankType != 2){
            $where[] = ['type' => $bankType];
        }

        $cards = self::find()->where($where)->orderBy('default_bank desc,last_modify_time desc')->asArray()->all();

        //如果用户没有设置默认卡，则优先取存管支持卡
        $hasDefault = $this->hasDefault($userid);
        if (!$hasDefault) {
            usort($cards, [$this, 'myBanksort']);
        }
        //都不支持存管卡的排序
        if (isset($cards[0]) && !empty($cards[0])) {
            $BankAbbrDe = Keywords::getOutBankAbbrDe();
            $BankAbbr = $BankAbbrDe[0];
            if (!in_array($cards[0]['bank_abbr'], $BankAbbr)) {
                usort($cards, [$this, 'myBanksortByTime']);
            }
        }

        if ($type != 2) {
            $cards_limit = array();
            $cards_allow = array();
            $limit_cards = CardLimit::find()->where(['type' => ($type + 1), 'status' => 1])->asArray()->all();
            if (!empty($limit_cards)) {
                foreach ($limit_cards as $val) {
                    $limit_arr[] = $val['bank_name'] . "_" . $val['card_type'];
                }
            }
            foreach ($cards as $k => $vol) {
                $str = $vol['bank_abbr'] . "_" . $vol['type'];
                if (in_array($str, $limit_arr)) {
                    $cards_limit[$k] = $cards[$k];
                    $cards_limit[$k]['sign'] = 1;
                } else {
                    $cards_allow[$k] = $cards[$k];
                    $cards_allow[$k]['sign'] = 2;
                }
            }
            $bank_list = array_merge($cards_allow, $cards_limit);
            if ($type == 0 && $loan_type == 0 && !empty($bank_list) && $mark = 0) {
                $loan_card[0] = $bank_list[0];
                return $loan_card;
            }
            return $bank_list;
        } else {
            foreach ($cards as $k => $value) {
                $cards[$k]['sign'] = 2;
            }
            return $cards;
        }
    }

    public function myBanksort($o1) {
        $BankAbbrDe = Keywords::getOutBankAbbrDe();
        $BankAbbr = $BankAbbrDe[0];
        if (in_array($o1['bank_abbr'], $BankAbbr)) {
            return -1;
        } else {
            return 1;
        }
    }

    public function myBanksortByTime($o1, $o2) {
        return $o1['last_modify_time'] > $o2['last_modify_time'] ? -1 : 1;
    }

    /*
     * 更新用户银行卡为默认卡
     * */

    public function updateDefaultBank($user_id, $bank_id) {
        $sql_bank = $this->updateAll(['default_bank' => 0], ['user_id' => $user_id]);
        $upModel = self::findOne($bank_id);
        $condition = [
            'default_bank' => 1,
        ];
        return $upModel->updateUserBank($condition);
    }

    /**
     * 监测，银行卡与用户是否一致
     * @param $user_id
     * @param $bank_id
     * @return array|null|\yii\db\ActiveRecord
     */
    public function isUserCard($user_id, $bank_id)
    {
        if (empty($user_id) || empty($bank_id)) {
            return null;
        }
        return self::find()->where(['id' => $bank_id, 'user_id' => $user_id])->one();
    }

    public function addUserbank($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['is_new'] = 1;
        $data['status'] = 1;
        $data['default_bank'] = 0;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateUserBank($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 解除银行卡绑定
     * @param $condition
     * @return bool
     */
    public function delUserBank() {
        $condition['status'] = 0;
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $condition['default_bank'] = 0;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取用户最新一张信用卡
     * @param $user_id
     * @return array|null|ActiveRecord
     */
    public function getCreditCardInfo($user_id) {
        if (empty($user_id))
            return array();
        $credit_info = self::find()
                        ->where([
                            'user_id' => $user_id,
                            'type' => 1,
                            'status' => 1
                        ])
                        ->orderBy("last_modify_time desc")->one();
        if (empty($credit_info)) {
            return array();
        }
        return $credit_info;
    }

    /**
     * 获取用户默认出款储蓄卡
     * @param $user_id
     * @return array|null|ActiveRecord
     */
    public function getDepositCardInfo($user_id) {
        if (empty($user_id))
            return array();
        $deposit_info = self::find()
                ->where([
                    'user_id' => $user_id,
                    'type' => 0,
                    'status' => 1,
                    'default_bank' => 1])
                ->one();
        if (empty($deposit_info)) {
            return array();
        }
        return $deposit_info;
    }

    /**
     * 判断是否为默认银行卡;
     * @param $bank_id
     * @return bool
     */
    public function saveu_Uerbankid($user_id, $bank_id) {
        $sql_bank = $this->updateAll(['default_bank' => 0], ['user_id' => $user_id]);
        $upModel = self::findOne($bank_id);
        $condition = [
            'default_bank' => 1,
        ];
        return $upModel->updateUserBank($condition);
    }

    /*
     * 银行卡四要素认证---目前适用于导流标准api
     */

    public function bankFourElements($userInfo, $postData) {
        //绑卡之前先做银行卡四要素验证
        //调用银行卡验证接口
        $postinfo = array(
            'identityid' => $userInfo->user_id,
            'username' => $userInfo->realname,
            'idcard' => $userInfo->identity,
            'cardno' => $postData['cardno'],
            'phone' => $postData['phone'],
        );
        $openApi = new Apihttp;
        Logger::dayLog('bindbank', "bank_postinfo", $userInfo->user_id, $postinfo);
        $result = $openApi->bankInfoValids($postinfo);
        if ($result['res_code'] != '0000') {
            switch ($result['res_msg']) {
                case 'DIFFERENT':
                    $result['res_msg'] = '请优先确认您输入的手机号码与办理银行卡时预留手机号码一致<br>请确认您的银行卡号是否填写正确';
                    break;
                case 'ACCOUNTNO_INVALID':
                    $result['res_msg'] = '请核实您的银行卡状态是否有效';
                    break;
                case 'ACCOUNTNO_NOT_SUPPORT':
                    $result['res_msg'] = '暂不支持此银行，请更换您的银行卡';
                    break;
                default:
                    $result['res_msg'] = $result['res_msg'];
            }
            $array['rsp_code'] = '10020';
            $array['rsp_msg'] = $result['res_msg'];
            Logger::dayLog('bindbank', "bank_result_arr", $userInfo->user_id, $array);
            return $array;
        }
        return [];
    }

    /**
     * 判断用户是否设置默认卡
     * @param $user_id
     * @return bool
     */
    public function hasDefault($user_id) {
        $data = self::find()->where(['user_id' => $user_id, 'default_bank' => 1])->one();
        if (empty($data)) {
            return false;
        }
        return true;
    }

    /**
     * 通过用户id和卡号获取用户银行卡
     * @param $user_id
     * @param $card
     * @return array|null|ActiveRecord
     */
    public function getByUserIdCard($user_id, $card) {
        if (!intval($user_id) || !(string) $card) {
            return null;
        }
        $bankInfo = self::find()->where(['user_id' => $user_id, 'card' => $card])->one();
        return $bankInfo;
    }

    public function getByCard($card) {
        if (!(string) $card) {
            return null;
        }
        $bankInfo = self::find()->where(['card' => $card])->one();
        return $bankInfo;
    }

    /**
     * 银行存管开户绑卡，自主绑卡
     * @param type $userInfo
     * @param type $cardNo
     * @return boolean
     */
    public function bindBank($userInfo, $cardNo) {
        if (empty($cardNo)) {
            Logger::dayLog('cunguan/notify', $userInfo->user_id . '--' . $cardNo, 'cardNo为空');
            return false;
        }
        $cardInfo = (new User_bank())->getByCard($cardNo);
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

    
    private function saveUserBank($user, $cardbin, $area, $cardNo){
        $condition['user_id'] = $user->user_id;
        $condition['type'] = empty($cardbin) ? '0' : $cardbin['card_type'];
        $condition['bank_abbr'] = empty($cardbin) ? '' : $cardbin['bank_abbr'];
        $condition['bank_name'] = empty($cardbin) ? '' : $cardbin['bank_name'];
        $condition['sub_bank'] = '';
        $condition['city'] =  strval($area['city']);
        $condition['area'] =  strval($area['area']);
        $condition['province'] =  strval($area['province']);
        $condition['card'] = $cardNo;
        $condition['bank_mobile'] = $user->mobile;
        $condition['verify'] = 3;
        $ret_userbank = (new User_bank())->addUserbank($condition);
        return $ret_userbank;
    }
}
