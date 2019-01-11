<?php

namespace app\models\news;

use app\common\ApiSign;
use app\common\Curl;
use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\BaseModel;
use app\models\xs\XsApi;
use app\models\yyy\XhhApi;
use app\models\news\Setting;
use Yii;
use yii\web\IdentityInterface;
use app\commonapi\ImageHandler;

/**
 * This is the model class for table "yi_user".
 *
 * @property string $user_id
 * @property string $openid
 * @property string $mobile
 * @property string $invite_code
 * @property string $invite_qrcode
 * @property string $from_code
 * @property integer $user_type
 * @property integer $status
 * @property integer $identity_valid
 * @property integer $school_valid
 * @property string $school
 * @property integer $school_id
 * @property string $edu
 * @property string $school_time
 * @property string $realname
 * @property string $identity
 * @property integer $industry
 * @property string $company
 * @property string $position
 * @property string $telephone
 * @property string $address
 * @property string $pic_self
 * @property string $pic_identity
 * @property integer $pic_type
 * @property integer $come_from
 * @property string $down_from
 * @property string $serverid
 * @property string $create_time
 * @property string $pic_up_time
 * @property integer $final_score
 * @property integer $birth_year
 * @property string $last_login_time
 * @property string $last_login_type
 * @property string $verify_time
 * @property string $is_webunion
 * @property string $webunion_confirm_time
 * @property string $is_red_packets
 */
class User extends BaseModel implements IdentityInterface {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user';
    }

    public function getPassword() {
        return $this->hasOne(User_password::className(), ['user_id' => 'user_id']);
    }

    public function getBank($card_type = -1) {
        $data = $this->hasOne(User_bank::className(), ['user_id' => 'user_id'])->where(['status' => 1]);
        if ($card_type != -1) {
            return $data->andFilterWhere(['type' => $card_type]);
        }
        return $data;
    }

    public function getJuxinli($type = 1) {//此方法的使用 $this->getJuxinli(1)->one();
        return $this->hasOne(Juxinli::className(), ['user_id' => 'user_id'])->where(['type' => $type]);
    }

    public function getJuxinlijoin() {
        return $this->hasOne(Juxinli::className(), ['user_id' => 'user_id']);
    }

    public function getloan() {
        return $this->hasOne(User_loan::className(), ['user_id' => 'user_id']);
    }

    public function getallloan() {
        return $this->hasMany(User_loan::className(), ['user_id' => 'user_id'])->select("concat('1_',`loan_id`) as loan_id")->where(['in', 'status', [8, 9, 11, 12, 13]])->asArray();
    }

    public function getExtend() {
        return $this->hasOne(User_extend::className(), ['user_id' => 'user_id']);
    }

    public function getPayaccount($step = 1, $type = 1) {//$user->where(2,2);
        return $this->hasOne(Payaccount::className(), ['user_id' => 'user_id'])->where(['step' => $step, 'type' => $type]);
    }

    public function getCouponlist() {
        return $this->hasOne(Coupon_list::className(), ['mobile' => 'mobile']);
    }

    public function getFavorite() {
        return $this->hasOne(Favorite_contacts::className(), ['user_id' => 'user_id']);
    }

    public function getUserwx() {
        return $this->hasOne(User_wx::className(), ['openid' => 'openid']);
    }

    public function getAuthusers() {
        return $this->hasOne(User_auth::className(), ['user_id' => 'user_id'])->where(['is_up' => 2, 'is_yyy' => 1]);
    }

    public function getAddress() {
        return $this->hasOne(Address::className(), ['user_id' => 'user_id']);
    }

    public function getUserbank() {
        return $this->hasOne(User_bank::className(), ['user_id' => 'user_id']);
    }

    public function getUsercredit() {
        return $this->hasOne(User_credit_qj::className(), ['identity' => 'identity']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_type', 'status', 'identity_valid', 'school_valid', 'school_id', 'industry', 'pic_type', 'come_from', 'final_score', 'birth_year'], 'integer'],
            [['create_time', 'pic_up_time', 'last_login_time', 'verify_time', 'webunion_confirm_time'], 'safe'],
            [['openid', 'school', 'edu', 'school_time'], 'string', 'max' => 64],
            [['mobile', 'identity'], 'string', 'max' => 20],
            [['invite_code', 'invite_qrcode', 'from_code', 'realname', 'telephone', 'down_from'], 'string', 'max' => 32],
            [['company', 'position', 'address', 'pic_self', 'pic_identity', 'serverid'], 'string', 'max' => 128],
            [['last_login_type'], 'string', 'max' => 16],
            [['is_webunion'], 'string', 'max' => 8],
            [['is_red_packets'], 'string', 'max' => 4],
            [['mobile'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'user_id' => 'User ID',
            'openid' => 'Openid',
            'mobile' => 'Mobile',
            'invite_code' => 'Invite Code',
            'invite_qrcode' => 'Invite Qrcode',
            'from_code' => 'From Code',
            'user_type' => 'User Type',
            'status' => 'Status',
            'identity_valid' => 'Identity Valid',
            'school_valid' => 'School Valid',
            'school' => 'School',
            'school_id' => 'School ID',
            'edu' => 'Edu',
            'school_time' => 'School Time',
            'realname' => 'Realname',
            'identity' => 'Identity',
            'industry' => 'Industry',
            'company' => 'Company',
            'position' => 'Position',
            'telephone' => 'Telephone',
            'address' => 'Address',
            'pic_self' => 'Pic Self',
            'pic_identity' => 'Pic Identity',
            'pic_type' => 'Pic Type',
            'come_from' => 'Come From',
            'down_from' => 'Down From',
            'serverid' => 'Serverid',
            'create_time' => 'Create Time',
            'pic_up_time' => 'Pic Up Time',
            'final_score' => 'Final Score',
            'birth_year' => 'Birth Year',
            'last_login_time' => 'Last Login Time',
            'last_login_type' => 'Last Login Type',
            'verify_time' => 'Verify Time',
            'is_webunion' => 'Is Webunion',
            'webunion_confirm_time' => 'Webunion Confirm Time',
            'is_red_packets' => 'Is Red Packets',
        ];
    }

    /*
     * 根据字段查询用户信息--返回单条信息
     * @param string $fild      字段名称
     * @param string | int $val 字段值
     * @return obj
     */

    public function checkUser($field, $val) {
        if (empty($field) || empty($val)) {
            return null;
        }
        $userinfo = User::find()->where([$field => "$val"])->one();
        return $userinfo;
    }

    /**
     * 根据身份证号查询user信息
     * @param type $identity
     * @return type
     */
    public function getUserinfoByIdentity($identity) {
        if (empty($identity)) {
            return null;
        }
        $userinfo = User::find()->where(['identity' => $identity])->one();
        return $userinfo;
    }

    /*
     * 修改user信息
     * @param int $user_id  用户id
     * @param array $update_arr 要修改的数组
     * @return false | id
     */

    public function updateUser($user_id, $update_arr) {
        if (empty($user_id) || empty($update_arr)) {
            return FALSE;
        }
        if (!is_array($update_arr)) {
            return FALSE;
        }
        $up_info = $this->checkUser('user_id', $user_id);
        if ($up_info) {
            foreach ($update_arr as $k => $v) {
                $up_info->$k = $v;
            }
            $up_info->edu = (string) $up_info->edu;
            return $up_info->save();
        }
        return FALSE;
    }

    /**
     * 更新用户信息
     */
    public function setUserinfo($user_id, $condition = array()) {
        if (empty($user_id) || empty($condition)) {
            return null;
        }

        $userinfo = User::findOne($user_id);
        foreach ($condition as $key => $val) {
            $userinfo->{$key} = $val;
        }

        if ($userinfo->save() > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 修改user信息
     * @param $condition
     * @return bool
     */
    public function update_user($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $error = $this->chkAttributes($condition);
        if ($error) {
            Logger::dayLog('openid', $this->mobile, $error);
            return false;
        }
        return $this->save();
    }

    /**
     * 根据给到的ID查询身份。
     *
     * @param string|integer $id 被查询的ID
     * @return IdentityInterface|null 通过ID匹配到的身份对象
     */
    public static function findIdentity($id) {
        return static::findOne($id);
    }

    /**
     * 根据 token 查询身份。
     *
     * @param string $token 被查询的 token
     * @return IdentityInterface|null 通过 token 得到的身份对象
     */
    public static function findIdentityByAccessToken($token, $type = null) {
        return static::findOne(['access_token' => $token]);
    }

    /**
     * @return int|string 当前用户ID
     */
    public function getId() {
        return $this->user_id;
    }

    /**
     * @return string 当前用户的（cookie）认证密钥
     */
    public function getAuthKey() {
        return $this->user_id;
    }

    /**
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey) {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * 添加用户
     * @param $condition
     * @return bool
     */
    public function addUser($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['user_type'] = 2;
        $data['is_red_packets'] = 'no';
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 根据用户id查询用户信息
     * @return obj
     */
    public function getUserinfoByUserId($user_id) {
        if (empty($user_id) || !is_numeric($user_id)) {
            return null;
        }
        $userinfo = User::findOne($user_id);
        return $userinfo;
    }

    public function getInvitationNum($invite_code)
    {
        if (empty($invite_code) || !is_numeric($invite_code)) {
            return null;
        }
        $userinfo = User::find()->where(['from_code'=>$invite_code,'come_from'=>5])->asArray()->all();
        return $userinfo;
    }

    /**
     * 根据用户手机号查询用户信息
     * @param $mobile
     * @return obj
     */
    public function getUserinfoByMobile($mobile) {
        if (empty($mobile)) {
            return null;
        }
        $userinfo = User::find()->where(["mobile" => $mobile])->one();
        return $userinfo;
    }

    /**
     * 根据用户手机号、姓名、身份证查询用户信息
     * @param $mobile
     * @param $identity
     * @param $realname
     * @return array
     */
    public function getCardBinByMni($mobile, $identity, $realname) {
        $sql = "SELECT * FROM " . self::tableName() . " WHERE realname = '" . htmlspecialchars($realname) . "' AND mobile LIKE '" . htmlspecialchars($mobile) . "%' AND identity LIKE '" . htmlspecialchars($identity) . "%'";
        $userInfo = Yii::$app->db->createCommand($sql)->queryOne();
        return $userInfo;
    }

    /**
     * 根据用户姓名、身份证查询用户信息
     * @param $identity
     * @param $realname
     * @return array
     */
    public function getCardBinByMniJdq($identity, $realname) {
        $sql = "SELECT * FROM " . self::tableName() . " WHERE realname = '" . htmlspecialchars($realname) . "' AND identity LIKE '" . htmlspecialchars($identity) . "%'";
        $userInfo = Yii::$app->db->createCommand($sql)->queryOne();
        return $userInfo;
    }

    public function getTokenId() {
        $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
        if (empty($token_id)) {
            $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $this->user_id])->one();
            $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
        }
        return $token_id;
    }

    /*
     * 根据user_id 查询联系人信息
     * @param int $user_id 用户id
     * @return obj
     */

    public function getFavoriteByUserId($user_id) {
        return Favorite_contacts::find()->where(['user_id' => $user_id])->one();
    }

    /**
     * 根据数组里的内容，判断下次需要跳转的路径
     * @param type $data
     * @return type
     */
    private function getNextPage($data) {
//        print_r($data);die;
        $next_url = '';
        foreach ($data as $key => $val) {
            if ($val['status'] == 0) {
                $next_url = $val['current_url'];
                break;
            }
        }
        return $next_url;
    }

    private function getUserInfoStatus($type, $function = 0) {
        switch ($type) {
            case 2:
                if ($this->status != 3 && (isset($this->password) && empty($this->password->iden_url))) {
                    return 0;
                }
                return $this->identity_valid == 2 || $this->identity_valid == 4 ? 1 : 0;
            case 3:
                $extend = $this->extend;
                return !empty($extend) && !empty($extend->company_area) && !empty($extend->position) ? 1 : 0;
            case 4:
                return $this->status == 3 ? 1 : 0;
            case 5:
                $favorite = $this->favorite;
                return !empty($favorite) && !empty($favorite->relation_common) ? 1 : 0;
            case 6:
                $bank = $this->getBank()->all();
                return !empty($bank) ? 1 : 0;
            case 7:
                $juxinli = $this->getJuxinli(1)->one();
                if (empty($juxinli) || $juxinli->process_code != '10008' || $juxinli->last_modify_time < date('Y-m-d H:i:s', strtotime('-4 months'))) {
                    return 0;
                } else {
                    return 1;
                }
            case 8:
                $juxinli = $this->getJuxinli(2)->one();
                if (empty($juxinli) || $juxinli->process_code != '10008' || $juxinli->last_modify_time < date('Y-m-d H:i:s', strtotime('-4 months'))) {
                    return 0;
                } else {
                    return 1;
                }
            case 9:
                $loan_no_keys = $this->user_id . "_loan_no";
                $loan_no = Yii::$app->redis->get($loan_no_keys);
                return empty($loan_no) ? 0 : 1;
            case 11:
                return 0;
            case 12:
                $bank = $this->getBank(0)->all();
                return !empty($bank) ? 1 : 0;
            case 13:
                $bank = $this->getBank(1)->all();
                $times = 0;
                if (empty($bank)) {
                    $times = ScanTimes::find()->where(['mobile' => $this->mobile, 'type' => 24])->count();
                }
                return !empty($bank) ? 1 : ($times >= 1 ? 1 : 0);
        }
    }

    /**
     * 获取当前资料是否需要完善
     * 1:借款;2:实名认证;3:工作信息;4:自拍照;5:联系人;6:绑卡;7:手机认证;8:京东认证;9:借款决策;10:购买担保卡;11:邀请认证;12：储蓄卡绑卡；13：信用卡绑卡;14:担保借款
     * @param object $user 用户信息
     * @param type $order
     * @param type $type 流程（功能） 1,2,3,....13
     * @param type $action
     * @param array $result status 0:需要填写;1:不需要填写
     * 每个页面传递的参数名orderinfo
     */
    private function getInformationStatus($user, $from, $order, $type, $action) {
        if (empty($order)) {
            return [];
        }
        $result = [];
        foreach ($order as $val) {
            if ($action == 2 && $val == $type) {
                $result[$val]['status'] = 0;
                $url = $this->getUrl($from, $val, 2);
                $result[$val]['current_url'] = $url;
                continue;
            }
            if ($val == 6 && $type == 1) {
                $status = $user->getUserInfoStatus($val, $type);
            } else {
                $status = $user->getUserInfoStatus($val);
            }
            $result[$val]['status'] = $status;
            $url = $this->getUrl($from, $val, 2);
            $result[$val]['current_url'] = $url;
        }
        return $result;
    }

    /**
     * 
     * @param type $user_id
     * @param type $from 1:wx 2:导流 3:H5 4:重构
     * @param type $type 1:借款;2:实名认证;3:工作信息;4:自拍照;5:联系人;6:绑卡;7:手机认证;8:京东认证;9:借款决策;10:购买担保卡;11:邀请认证;12：储蓄卡绑卡；13：信用卡绑卡;14:担保借款;15我要还款15不绑储蓄卡借款 16:非资料页主动绑卡
     * @param type $action 1:新增;2：修改
     * @return array 
     */
    public function getPerfectOrder($user_id, $from, $type, $action = 1) {
        $user = User::findOne($user_id);
        if (empty($user)) {
            return [];
        }
        $types = $this->getInformationOrder();
        $orders = $this->getOrder($types, $from, $type, $action);
        $url = $this->getUrl($from, $type);
        $array = $url;
        $data = $this->getInformationStatus($user, $from, $orders, $type, $action);
        // print_r($data);die;
        $array['data'] = $data;
        $nextPage = $this->getNextPage($data);
        $array['nextPage'] = $nextPage;
        Logger::dayLog('infoorder', $user_id, $array);
        return $array;
    }

    /**
     * @param $mark 1:获取type的整改url 2：获取type的current_url
     */
    public function getUrl($from, $type, $mark = 1) {
        $urlAll = $this->getAllUrl();
        if ($mark == 1) {
            return $urlAll[$from][$type];
        } elseif ($mark == 2) {
            return $urlAll[$from][$type]['current_url'];
        }
        return [];
    }

    /**
     * 获取顺序数组
     */
    public function getOrder($types, $from, $type, $action) {
        $from_key = array_key_exists($from, $types);
        if (!$from_key) {
            return [];
        }
        $action_key = array_key_exists($action, $types[$from]);
        if (!$action_key) {
            return [];
        }
        $type_key = array_key_exists($type, $types[$from][$action]);
        if (!$type_key) {
            return [];
        }
        return $types[$from][$action][$type];
    }

    /**
     * 资料顺序 1:借款;2:实名认证;3:工作信息;4:自拍照;5:联系人;6:绑卡;7:手机认证;8:京东认证;9:借款决策;10:购买担保卡;11:邀请认证;12：储蓄卡绑卡；13：信用卡绑卡;14:担保借款17不绑储蓄卡借款 16:非资料页主动绑卡
     * @return array
     */
    public function getInformationOrder() {
        $types = [
            '1' => [//wx
                '1' => [//新增
                    '1' => [2, 3, 4, 9, 5, 13, 7],
                    '2' => [2, 3, 4],
                    '3' => [2, 3, 4],
                    '4' => [2, 3, 4],
                    '5' => [5],
                    '6' => [2, 6],
                    '7' => [2, 5, 7],
                    '8' => [2, 5, 8],
                    '10' => [2, 6, 10],
                    '11' => [2, 3, 4, 9, 11], //特别注意，自拍照流程需要等待审核
                ],
                '2' => [
                    '2' => [2, 3, 4],
                    '3' => [2, 3, 4],
                    '5' => [5],
                ],
            ],
            '2' => [//导流
                '1' => [
                    '1' => [2, 3, 9],
                ],
            ],
            '3' => [//H5
                '1' => [
                    '1' => [2, 3, 5, 12, 7, 9],
                ],
            ],
            '4' => [//重构 1:借款;2:实名认证;3:工作信息;4:自拍照;5:联系人;6:绑卡;7:手机认证;8:京东认证;9:借款决策;10:购买担保卡;11:邀请认证;12：储蓄卡绑卡；13：信用卡绑卡;14:担保借款;17不绑储蓄卡借款 16:非资料页主动绑卡
                '1' => [
                    '1' => [2, 3, 4, 9, 5, 13, 7],
                    '2' => [2, 3, 4],
                    '3' => [2, 3, 4],
                    '4' => [2, 3, 4],
                    '5' => [5],
                    '6' => [2, 6],
                    '7' => [2, 5, 7],
                    '8' => [2, 5, 8],
                    '10' => [2, 6, 10],
                    '11' => [2, 3, 4, 11], //特别注意，自拍照流程需要等待审核
                    '12' => [2, 12],
                    '13' => [2, 13],
                    '14' => [2, 3, 5, 4, 13, 12, 7, 9],
                    '17' => [2, 3, 4, 9, 5, 7],
                ],
                '2' => [
                    '2' => [2],
                    '3' => [2, 3],
                    '5' => [5],
                    '16' => [2, 16],
                ],
            ],
        ];
        return $types;
    }

    public function getAllUrl() {
        $url = [
            '1' => [
                '1' => [
                    'come_url' => '/dev/loan/second',
                    'current_url' => '/dev/loan/second',
                    'end_url' => '/dev/loan',
                ],
                '2' => [
                    'come_url' => '/dev/account/peral',
                    'current_url' => '/dev/reg/personals',
                ],
                '3' => [
                    'come_url' => '/dev/account/peral',
                    'current_url' => '/dev/reg/company',
                    'end_url' => '/wap/loan',
                ],
                '4' => [
                    'come_url' => '/dev/account/peral',
                    'current_url' => '/dev/reg/pic',
                ],
                '5' => [
                    'come_url' => '/dev/account/peral',
                    'current_url' => '/dev/reg/contacts',
                ],
                '6' => [
                    'come_url' => '/dev/bank',
                    'current_url' => '/dev/bank/addcard',
                ],
                '7' => [
                    'come_url' => '/dev/account/peral',
                    'current_url' => '/new/mobileauth/phoneauth',
                ],
                '8' => [
                    'come_url' => '/dev/account/peral',
                    'current_url' => '/dev/account/jingdong',
                ],
                '9' => [
                    'come_url' => '',
                    'current_url' => '/wap/loan/loanrules',
                ],
                '10' => [
                    'come_url' => '/dev/guarantee',
                    'current_url' => '/dev/guarantee/buycard',
                ],
                '11' => [
                    'come_url' => '/dev/invitation/index',
                    'current_url' => '/dev/invitation/index',
                ],
                '12' => [//储蓄卡
                    'come_url' => '/dev/bank',
                    'current_url' => '/dev/bank/addcard',
                ],
                '13' => [//信用卡
                    'come_url' => '/dev/bank',
                    'current_url' => '/dev/bank/addcard',
                ],
            ],
            '2' => [
                '1' => [
                    'come_url' => '/dev/coupon/verify',
                    'current_url' => '/dev/coupon/verify',
                    'end_url' => '/dev/coupon/verify',
                ],
                '2' => [
                    'come_url' => '',
                    'current_url' => '/dev/coupon/personal',
                ],
                '3' => [
                    'come_url' => '',
                    'current_url' => '/dev/coupon/company',
                ],
            ],
            '3' => [
                '1' => [
                    'come_url' => '/wap/loan/second', //等待提供
                    'current_url' => '/wap/loan/second', //等待提供
                    'end_url' => '/wap/loan',
                ],
                '2' => [
                    'come_url' => '',
                    'current_url' => '/wap/userauth/nameauth',
                ],
                '3' => [
                    'come_url' => '',
                    'current_url' => '/wap/userauth/workinfo',
                    'end_url' => '/wap/loan',
                ],
                '5' => [
                    'come_url' => '',
                    'current_url' => '/wap/userauth/contacts',
                ],
                '7' => [
                    'come_url' => '',
                    'current_url' => '/new/mobileauth/phoneauth',
                ],
                '9' => [
                    'come_url' => '',
                    'current_url' => '/wap/loan/loanrules',
                ],
                '12' => [
                    'come_url' => '',
                    'current_url' => '/wap/bank/addcard',
                ],
            ],
            '4' => [
                '1' => [ //信用借款
                    'come_url' => '/new/loan/second',
                    'current_url' => '/new/loan/second',
                    'end_url' => '/new/loan',
                ],
                '2' => [
                    'come_url' => '/new/account/peral',
                    'current_url' => '/new/userauth/nameauth',
                ],
                '3' => [
                    'come_url' => '/new/account/peral',
                    'current_url' => '/new/userauth/workinfo',
                    'end_url' => '/new/loan',
                ],
                '4' => [
                    'come_url' => '/new/account/peral',
                    'current_url' => '/new/userauth/pic',
                    'end_url' => '/new/loan',
                ],
                '5' => [
                    'come_url' => '/new/account/peral',
                    'current_url' => '/new/userauth/contacts',
                ],
                '6' => [
                    'come_url' => '/new/bank',
                    'current_url' => '/new/bank/addcard?banktype=3',
                ],
                '7' => [
                    'come_url' => '/new/account/peral',
                    'current_url' => '/new/mobileauth/phoneauth',
                ],
                '8' => [
                    'come_url' => '/new/account/peral',
                    'current_url' => '/new/account/jingdong',
                ],
                '9' => [
                    'come_url' => '',
                    'current_url' => '/new/loan/loanrules',
                ],
                '10' => [
                    'come_url' => '/dev/guarantee',
                    'current_url' => '/dev/guarantee/buycard',
                ],
                '11' => [
                    'come_url' => '/new/invitation/index',
                    'current_url' => '/new/invitation/index',
                    'end_url' => '/new/loan',
                ],
                '12' => [//储蓄卡
                    'come_url' => '/new/bank',
                    'current_url' => '/new/bank/addcard?banktype=1',
                    'end_url' => '/new/loan',
                ],
                '13' => [//信用卡
                    'come_url' => '/new/account/peral',
                    'current_url' => '/new/bank/xykadd',
                ],
                '14' => [//担保卡借款
                    'come_url' => '/new/loan/second',
                    'current_url' => '/new/loan/second',
                    'end_url' => '/new/loan',
                ],
                '15' => [//我要还款
                    'come_url' => '/new/loan',
                    'current_url' => '/new/bank/addcard?banktype=3',
                    'end_url' => '/new/loan',
                ],
                '16' => [//借款主动绑卡
                    'come_url' => '/new/loan',
                    'current_url' => '/new/bank/addcard?banktype=1',
                ],
                '17' => [ //借款
                    'come_url' => '/new/loan/second',
                    'current_url' => '/new/loan/second',
                    'end_url' => '/new/loan',
                ],
            ],
        ];
        return $url;
    }

    /**
     * 
     * @param type $user
     * @param type $from 1:weixin 2:ios 3:android 4:H5
     * @return int  0:没有触犯规则 1：拉黑用户了，直接跳转
     */
    public function getRegrule($user, $from) {
        $api = new XhhApi();
        $limit = $api->runDecisions($user, $from);
        Logger::dayLog('reg_limit', $user->user_id, $limit);
        if (!empty($limit)) {
            $condition = $limit;
            $condition['old_status'] = $user->status;
            $condition['new_status'] = 5;
            Register_event::save_registerevent($user->user_id, $condition);
        }
        //用于注册决策收集数据，暂时不处理返回值
        if (!empty($user->user_id)) {
            $rsa = new ApiSign();
            $data = ['user_id' => $user->user_id];
            // 签名的使用
            $sign = $rsa->signData($data);
            $curl = new Curl();
            Logger::dayLog('reg_limit_send', print_r(array($sign), true));
            $url = Yii::$app->params['strategy'] . 'reg/regdecision';
            $ret = $curl->post($url, $sign);
            Logger::dayLog('reg_limit_return', print_r(array($ret), true));
            $result = json_decode($ret, true);
            $isVerify = (new ApiSign)->verifyData($result['data'], $result['_sign']);
            if (!$isVerify) {
                return 0;
            }
            if (!empty($result)) {
                $result_data = json_decode($result['data'], true);
                if ($result_data['res_code'] == 0) {
                    return 0;
                }
            }
        }
        /*
          $mark = 0;
          foreach ($limit as $key => $val) {
          if (in_array($key, ['age_value', 'area_value', 'number_value', 'ip_value', 'is_black'])) {
          if (!empty($val)) {
          $mark = 1;
          break;
          }
          }
          continue;
          }
          if ($mark == 0) {
          //return 0;
          }

          $condition = $limit;
          $condition['old_status'] = $user->status;
          $condition['new_status'] = 5;
          Register_event::save_registerevent($user->user_id, $condition);
         */
        return 1;
    }

    public function setBlack() {
        $oApi = new XsApi;
        $res = $oApi->setBlack($this->mobile, $this->identity);
        $this->status = 5;
        $result = $this->save();
        return $result;
    }

    /**
     * 通过身份证认证借款人是否在限制地区
     * @param int $identity
     * @return bool
     */
    public function getIdentityValid($identity, $beth = 1982) {
        $birth_year = intval(substr($identity, 6, 4));
        if ($birth_year < $beth) {
            return FALSE;
        }
        $City = array(
            15 => "内蒙古", 54 => "西藏", 65 => "新疆",
        );
        //地区验证
        if (array_key_exists(intval(substr($identity, 0, 2)), $City)) {
            return FALSE;
        }
        return TRUE;
    }

    /**
     * 根据邀请码查询用户信息
     */
    public function getUserinfoByInvitecode($invite_code) {
        if (empty($invite_code)) {
            return null;
        }
        $userinfo = User::find()->where(['invite_code' => "$invite_code"])->one();
        return $userinfo;
    }

    /**
     * 用户可借款额度
     * @param type $user
     * @param int $type 1:查询显示额度 2：计算用户可借额度 3：用户可借额度
     * @return int quota
     */
    public function getUserLoanAmount($user, $type = 1) {
        $user = User::findOne($user->user_id);
        $white = User_quota::find()->where(['user_id' => $user->user_id])->one();
        if ($type == 1) {
            if (!empty($white)) {
                return $white->quota + $white->temporary_quota;
            } else {
                return 500;
            }
        } elseif ($type == 3) {
            $white = TemQuota::find()->where(['user_id' => $user->user_id])->one();
            if (!empty($white)) {
                return $white->quota;
            } else {
                return 1000;
            }
        } else {
            $where = [
                'AND',
                ['user_id' => $user->user_id],
                ['status' => 8],
                ['business_type' => 1],
                ['!=', "DATEDIFF(`repay_time`, `start_date`)", 0]
            ];
            if (!empty($white) && $white->quota > 0) {
                $wheres = $where;
                $wheres[] = ['amount' => $white->quota];
                $loan_times = User_loan::find()->where($wheres)->count();
                if ($loan_times >= 3) {
                    return $white->quota + 500 >= 10000 ? 10000 : $white->quota + 500;
                } elseif ($loan_times >= 2 && $white->quota == 1500) {
                    return 2000;
                }
            }
            $amount = 1500;
            if (!empty($user) && $user->status != 3) {//是否审核通过
                return $amount;
            }
            $over_loan = User_loan::find()->select('amount')->where($where)->all();
            if (!empty($over_loan)) {
                $amount = $this->judgeAmount($over_loan);
                return $amount;
            } else {
                return $amount;
            }
        }
    }

    /**
     *
     * @param type $loan
     * @param type $type 1：三个月内有借款的
     * @return int
     */
    private function judgeAmount($loan, $type = 1) {
        $amounts = [];
        foreach ($loan as $val) {
            $amounts[] = $val->amount;
        }
        $max = max($amounts);
        $times_array = array_count_values($amounts);
        $times = $times_array[$max];
        switch ($type) {
            case 1:
                $times = $times_array[$max];
                if ($max < 1500) {
                    $amount = 1500;
                    break;
                } elseif ($max >= 10000) {
                    $amount = 10000;
                    break;
                }
                if ($max == 1500) {
                    $amount = $times >= 2 ? 2000 : 1500;
                    break;
                }
                if ($times <= 2) {
                    $amount = intval($max / 500) * 500;
                } else {
                    $amount = intval($max / 500) * 500 + 500;
                }
                break;
            default :
                $amount = 1500;
        }
        return $amount;
    }

    /**
     * 进入白名单
     * @param type $user_id
     */
    public function inputWhite($user_id) {
        if (empty($user_id)) {
            return FALSE;
        }
        $user = User::findOne($user_id);
        if (empty($user) || $user->status != 3) {
            return FALSE;
        }
        Logger::dayLog('Alpay', "user_idno", $user->identity);
        $result = White_list::addBatch($user->identity, $user->realname, $user->mobile, 0, $user_id);
        return $result;
    }

    /**
     * 判断当前用户是否为复贷用户
     * @param $loan
     * @return bool
     */
    public function isRepeatUser($user_id) {
        $where = [
            'AND',
            ['user_id' => $user_id],
            ['status' => 8],
            ['IN', 'business_type', [1, 4, 5, 6]],
            ['NOT IN', 'settle_type', [2]],
        ];
        $loans = User_loan::find()->where($where)->count();
        return $loans;
    }

    /**
     * 判断指定时间段是否有正常还款
     * @param $user_id
     * @param $beginTime
     * @return int
     */
    public function isRepeat($user_id, $beginTime) {
        $where = [
            'AND',
            ['user_id' => $user_id],
            ['status' => 8],
            ['IN', 'business_type', [1, 4, 5, 6]],
            ['NOT IN', 'settle_type', [2]],
            ['>=', 'last_modify_time', $beginTime],
        ];
        $loansCount = User_loan::find()->where($where)->count();
        return $loansCount;
    }

    public function updateUserStatus($status) {
        if (empty($status) || !is_numeric($status)) {
            return false;
        }
        $date['status'] = $status;
        $error = $this->chkAttributes($date);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 智融钥匙用户分桶认证
     * @param type $user_id
     * @return int
     */
    public function getEvaluationChannel($user_id, $mobile) {
        $channel_app = [];
        $channel_h5 = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
        $is_channel_app = in_array(substr($user_id, -1), $channel_app, false);
        $is_channel_h5 = in_array(substr($user_id, -1), $channel_h5, false);
        $youxin_down_url = '';
        $yxl_authentication_url = '';
        if ($is_channel_app) {
            $channel = 1;
            $youxin_down_url = Yii::$app->request->hostInfo . '/borrow/creditactivation/evaluadown?mobile=' . $mobile;
        }
        if ($is_channel_h5) {
            $channel = 2;
            $yxl_authentication_url = '/new/auth/jump?userToken=' . $mobile;
        }
        return ['channel' => $channel, 'youxin_down_url' => $youxin_down_url, 'yxl_authentication_url' => $yxl_authentication_url];
    }
    
    /**
     * 用户认证资料状态（身份认证 联系人认证 视频认证 运营商认证 信用卡认证）
     * @param type $user_id
     * @return type
     */
    public function getHaveUserData($user_id){
        $userinfo = (new User())->findOne($user_id);
        $user_extend = (new User_extend())->find()->where(['user_id' => $userinfo->user_id])->one();
        //身份信息认证  （1：未认证 2：已认证）
        $identify_valid = ($userinfo->identity_valid == 2 || $userinfo->identity_valid == 4 ) ? 2 : 1;
        //联系人认证 （1：未认证 2：已认证）
        $favorite = new Favorite_contacts();
        $fav = $favorite->getFavoriteByUserId($userinfo->user_id);
        $contacts = empty($fav) ? 1 : 2;
        //视频认证 (1:未认证 2：已认证 3：认证失败 4：认证中)
       $video_valid = ( $userinfo->status == 3 )  ? 2 : ( $userinfo->status == 4 ? 3 : ($userinfo->status == 2 ? 4 : 1) );
        //运营商认证 (1:未认证 0：已认证)
        if ($userinfo->status == 2) {
            $juli = 1;
        } else {
            $juxinliModel = new Juxinli();
            $juxinli = $juxinliModel->getJuxinliByUserId($userinfo->user_id);
            $juli = 0;
            if (empty($juxinli) || $juxinli->process_code != '10008' || ($juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) >= $juxinli->last_modify_time)) {
                $juli = 1;
            }
        }

        //信用卡认证（选填）
        $userbank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1, 'type' => 1])->one();
        $bank_valid = empty($userbank) ? 1 : 2;  //1未认证  2已认证
        
        $array['identify_valid'] = $identify_valid ;
        $array['favorite_valid'] = $contacts ;
        $array['video_valid'] = $video_valid ;
        $array['juxinli_valid'] = $juli ;
        $array['bank_valid'] = $bank_valid ;
        
        return $array;
    }
    
    public function getRequireData($user){
        $img_url_domain = (new ImageHandler())->img_domain_url;
        //身份信息 1：未认证  2:已认证
        $passModel = new User_password();
        $pass = $passModel->getUserPassword($user->user_id);
        $identify_valid = 1;
        $oUserExtend = $user->extend;
        if (!empty($pass)) {
            $path = $img_url_domain . $pass->iden_url;
            if ($user->status == 3 || ($user->identity_valid == 2 && !empty($pass) && !empty($pass->iden_url) && @fopen($path, 'r'))) {
                $identify_valid = 2;
                if( empty($oUserExtend) || empty($oUserExtend->profession) || empty($oUserExtend->income) || empty($oUserExtend->company) || empty($oUserExtend->email)){
                     $identify_valid = 1;
                }
            }
        }
        //联系人信息 1：未认证  2:已认证
        $favorite = new Favorite_contacts();
        $fav = $favorite->getFavoriteByUserId($user->user_id);
        $contacts_valid = (!empty($fav) && !empty($fav->relation_common)) ? 2 : 1;
        //视频认证  1 未认证；2 已认证;3认证失败 4人工认证中
        if ($user->status == 3) {
            $pic_valid = 2;
        } elseif ($user->status == 2) {
            $pic_valid = 4;
        } elseif ($user->status == 4) {
            $pic_valid = 3;
        } else {
            $pic_valid = 1;
        }
        //运营商认证 1:未认证；2:已认证 3已过期
        $juxinli_valid = $this->getJuxinlidata($user);
        $info['identify_valid'] = $identify_valid; 
        $info['contacts_valid'] = $contacts_valid; 
        $info['pic_valid'] = $pic_valid; 
        $info['juxinli_valid'] = $juxinli_valid ; 
        return $info;
    }
    
    public function getselectionData($user){
         //信用卡 1:未认证 2：已认证
        $userbank = User_bank::find()->where(['user_id' => $user->user_id, 'status' => 1, 'type' => 1])->one();
        $bank_valid = empty($userbank) ? 1 : 2;

        //1学历 2社保 3公积金 1:未认证 2：已认证  3认证中 4：已过期
        $userLoanModel = new User_loan();
        //学历认证
        $edu_valid = $userLoanModel->getValidByType($user->user_id, 1);
        //社保认证
        $social_valid = $userLoanModel->getValidByType($user->user_id, 2);
        //公积金认证
        $fund_valid = $userLoanModel->getValidByType($user->user_id, 3);
        //京东认证
        $jd_valid = $userLoanModel->getValidByType($user->user_id, 4);
        //银行流水
//        $bankflow_valid = $userLoanModel->getValidByType($user->user_id, '101');
        $bankflow_valid = $userLoanModel->getValidByType($user->user_id, 7);
        //淘宝
        $taobao_valid = $userLoanModel->getValidByType($user->user_id, 6);
        
        
        $selectionData['bank_valid'] = $bank_valid ;
        $selectionData['edu_valid'] = $edu_valid ;
        $selectionData['social_valid'] = $social_valid ;
        $selectionData['fund_valid'] =  $fund_valid;
        $selectionData['jd_valid'] =  $jd_valid;
        $selectionData['bankflow_valid'] =  $bankflow_valid;
        $selectionData['taobao_valid'] =  $taobao_valid;
        return $selectionData;
    }

        /**
     * 手机号运行商认证结果 : 1:未认证；2:已认证 3已过期
     */
    private function getJuxinlidata($user) {
        $juxinliModel = new Juxinli();
        $juxinli = $juxinliModel->getJuxinliByUserId($user->user_id);
        $juli = 0;
        if (empty($juxinli) || $juxinli->process_code != '10008') {
            $juli = 1;
        } else {
            if ($juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) >= $juxinli->last_modify_time) {
                $juli = 3;
            } else {
                $juli = 2;
            }
        }
        $xindiao = \app\commonapi\Keywords::xindiao();
        if (!empty($xindiao) && in_array($user->mobile, $xindiao)) {
            $juli = 2;
        }
        return $juli;
    }
    
    /**
     * 跳转到先花商城
     * @param type $oUser
     * @param type $page
     * @return string
     */
    public function getShopurl($oUser, $page, $category_id = ''){
        $user_token = $oUser->mobile;
        $url =  '/new/loan?category_id='.$category_id;
        if($page == 1){//商城首页
            $url =  '/new/loan?category_id='.$category_id;
        }
        if($page == 2){//商城订单页
            $url =  '/new/order/list';
        }
        $content = [
            'user_token' => $user_token,
            'url' => $url,
        ];
        $sign_data = (new \app\commonapi\ApiSign())->signData($content);
        $sign = urlencode($sign_data['_sign']);
        $xhShopDomain = Yii::$app->params['xhShopDomain'];
        $shop_url = $xhShopDomain.'new/index?user_token='.$user_token.'&url='.$url.'&sign='.$sign;
        return $shop_url;
    }
    
    /**
     * 
     * @param type $flag  redis的key
     * @param type $userInfo
     * @param type $type 1:默认清空redis 2:不清空
     * @return boolean
     */
    public function getShopRedisResult($flag,$userInfo,$type=1){
        $user_id = $userInfo->user_id;
        $key = $flag.$user_id;
        if(Yii::$app->redis->get($key)){
            $shop_url = (new User())->getShopurl($userInfo,1);
            if( $type == 1 ){
                Yii::$app->redis->del($flag.$user_id);
            }
            return $shop_url;
        }
        return false;
    }
    
    /**
     * 获取先花商城开关和订单链接
     * @param type $userinfo
     * @return type
     */
    public function getOrderList($userinfo){
        $shop_setting = new Setting();
        $shop_switch_result = $shop_setting->getShop();
        $shop_switch = false;
        if($shop_switch_result && ($shop_switch_result->status == 0)){
            $shop_switch = true;
        }
        $xhshop_url = (new User())->getShopurl($userinfo,2);
        return ['order_show'=>$shop_switch,'order_list_url'=>$xhshop_url];
    }
}
