<?php

namespace app\models\dev;

use app\models\BaseModel;
use yii\web\IdentityInterface;
use app\commonapi\Common;
use app\models\xs\XsApi;
use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class User extends BaseModel implements IdentityInterface {

    public $loan_counting;
    public $loan_amount;
    public $loan_rate;
    public $password;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    public function getUserwx() {
        return $this->hasOne(Userwx::className(), ['openid' => 'openid']);
    }

    public function getAccount() {
        return $this->hasOne(Account::className(), ['user_id' => 'user_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['user_id' => 'user_id']);
    }

    public function getloan() {
        return $this->hasOne(User_loan::className(), ['user_id' => 'user_id']);
    }

    public function getUserguarantee() {
        return $this->hasOne(User_guarantee_loan::className(), ['user_guarantee_id' => 'user_id']);
    }

    public function getExtend() {
        return $this->hasOne(User_extend::className(), ['user_id' => 'user_id']);
    }

    public function getUserpassword() {
        return $this->hasOne(User_password::className(), ['user_id' => 'user_id']);
    }

    public function getPayaccount($step = 1, $type = 1) {
        return $this->hasOne(Payaccount::className(), ['user_id' => 'user_id'])->where(['step' => $step, 'type' => $type]);
    }

    public function getCouponlist() {
        return $this->hasOne(Coupon_list::className(), ['mobile' => 'mobile']);
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
     * 冻结担保人
     */
    public function frozen() {
        $this->status = 7;
        if (!$this->save()) {
            return false;
        }
        //记录冻结的信息
        $frozen = new Frozen_log();
        $frozen->user_id = $this->user_id;
        $frozen->admin_id = '-1';
        $frozen->type = 1;
        $frozen->create_time = date('Y-m-d H:i:s');
        if (!$frozen->save()) {
            return false;
        }
        return $this;
    }

    public function getFrendsUserId($user) {
        $friendModel = new Friends();
        $fuser_id = $friendModel->find()->select(['fuser_id'])->where(['user_id' => $user->user_id])->andFilterWhere(['OR', '`auth`=1', '`authed`=1', '`invite`=1'])->groupBy('fuser_id')->all();
        $user_ids[] = Common::ArrayToString($fuser_id, 'fuser_id');
        $user_id = $friendModel->find()->select(['user_id'])->where(['fuser_id' => $user->user_id])->andFilterWhere(['OR', '`auth`=1', '`authed`=1', '`invite`=1'])->groupBy('user_id')->all();
        $user_ids[] = Common::ArrayToString($user_id, 'user_id');
        $user_ids = array_filter($user_ids);
        $ids = implode(',', $user_ids);
        $array_user_id = explode(',', $ids);
        $result = array_unique($array_user_id);
        $key = array_search($user->user_id, $result);
        if ($key !== false) {
            array_splice($result, $key, 1);
        }
        return $result;
    }

    /**
     *  根据手机号查询用户信息
     */
    public function getUserinfoByMobile($mobile) {
        if (empty($mobile)) {
            return null;
        }
        $userinfo = User::find()->where(['mobile' => $mobile])->one();
        return $userinfo;
    }

    /**
     * 根据用户id查询用户信息
     */
    public function getUserinfoByUserId($user_id) {
        if (empty($user_id)) {
            return null;
        }
        $userinfo = User::findOne($user_id);
        return $userinfo;
    }

    public function getTokenId() {
        $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
        if (empty($token_id)) {
            $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $this->user_id])->one();
            $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
        }
        return $token_id;
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
     * 根据微信唯一标识查询用户信息
     */
    public function getUserinfoByOpenid($openid) {
        if (empty($openid)) {
            return null;
        }
        $userinfo = User::find()->where(['openid' => "$openid"])->one();
        return $userinfo;
    }

    /**
     * 添加用户
     */
    public function addUser($condition) {

        $user = new User();
        $user->invite_code = isset($condition['invite_code']) ? $condition['invite_code'] : '';
        $user->from_code = isset($condition['from_code']) ? $condition['from_code'] : '';
        $user->mobile = isset($condition['mobile']) ? $condition['mobile'] : '';
        $user->user_type = isset($condition['user_type']) ? $condition['user_type'] : '';
        if (isset($condition['school'])) {
            $user->school = $condition['school'];
        }
        if (isset($condition['school_id'])) {
            $user->school_id = $condition['school_id'];
        }
        if (isset($condition['edu'])) {
            $user->edu = $condition['edu'];
        }
        if (isset($condition['school_time'])) {
            $user->school_time = $condition['school_time'];
        }
        if (isset($condition['realname'])) {
            $user->realname = $condition['realname'];
        }
        if (isset($condition['identity'])) {
            $user->identity = $condition['identity'];
        }
        if (isset($condition['school_valid'])) {
            $user->school_valid = $condition['school_valid'];
        }
        if (isset($condition['identity_valid'])) {
            $user->identity_valid = $condition['identity_valid'];
        }
        if (isset($condition['openid'])) {
            $user->openid = $condition['openid'];
        } else {
            $user->openid = '';
        }
        if (isset($condition['down_from'])) {
            $user->down_from = isset($condition['down_from']) ? $condition['down_from'] : 0;
        }
        $user->come_from = isset($condition['come_from']) ? $condition['come_from'] : '';
        $user->create_time = isset($condition['create_time']) ? $condition['create_time'] : '';
        $user->last_login_time = isset($condition['last_login_time']) ? $condition['last_login_time'] : '';
        $user->last_login_type = isset($condition['last_login_type']) ? $condition['last_login_type'] : '';
        $user->is_red_packets = 'yes';

        if ($user->save()) {
            $id = Yii::$app->db->getLastInsertID();
            $ip = Common::get_client_ip();
            $userExtendModel = new User_extend();
            $condition = [
                'user_id' => $id,
                'reg_ip' => $ip,
            ];
            $userExtendModel->addRecord($condition);
            return $id;
        } else {
            return false;
        }
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

    public function getUserinfoByIdentity($identity) {
        if (empty($identity)) {
            return null;
        }
        $userinfo = User::find()->where(['identity' => $identity])->one();
        return $userinfo;
    }

    /**
     * 根据user_id获取我的好友id
     */
    public function getFriendsByUserId($user_id) {
        if (empty($user_id)) {
            return null;
        }
        $user = $this->getUserinfoByUserId($user_id);
        if (empty($user)) {
            return null;
        }
        $friends = $this->getFrendsUserId($user);
        return empty($friends) ? null : $friends;
    }

    /**
     * 根据uid获取用户的基本信息
     */
    public function getInfoByUids($uids) {
        if (!is_array($uids) || empty($uids)) {
            return null;
        }
        $uids = array_unique($uids);
        $usertable = static::tableName();
        $wxtable = Userwx::tableName();
        $result = static::find()->select(['user_id', 'nickname', 'head', 'realname'])
                ->leftJoin($wxtable, "{$wxtable}.openid = {$usertable}.openid")
                ->where(['user_id' => $uids])
                ->orderBy('user_id ASC')
                ->asArray()
                ->all();
        if (!$result) {
            return null;
        }

        // 返回拼接的结果
        $newData = [];
        foreach ($result as $r) {
            $newData[] = [
                'user_id' => $r['user_id'],
                'nickname' => isset($r['nickname']) ? $r['realname'] : $r['realname'],
                'head' => $r['head'],
            ];
        }

        return $newData;
    }

    /**
     * 获取我的校友
     * @param bool $isBatch 解决大数据问题
     * @return $query || 结果 
     */
    public function mySchoolFriends($isBatch) {
        //2 获取学校的id 和 此用户是否通过验证
        if (!$this->school_id) {
            return null;
        }
        if ($this->school_valid != 1) {
            return null;
        }

        // 获取学校相同的好友
        $query = static::find()->where(['school_id' => $this->school_id, 'school_valid' => 1])
                ->andWhere(['!=', 'user_id', $this->user_id])
                ->orderBy('user_id');
        return $isBatch ? $query : $query->all();
    }

    /**
     * 获取我的公司好友
     * @param bool $isBatch 解决大数据问题
     * @return $query || 结果 
     */
    public function myCompanyFriends($isBatch) {
        //2 获取学校的id 和 此用户是否通过验证
        if (!$this->company) {
            return null;
        }

        // 获取学校相同的好友
        $query = static::find()->where(['company' => $this->company])
                ->andWhere(['!=', 'user_id', $this->user_id])
                ->orderBy('user_id');
        return $isBatch ? $query : $query->all();
    }

    public function getIdentityValid($identity) {
        //$identity = '412825198909303755';
        $birth_year = intval(substr($identity, 6, 4));
        if ($birth_year < 1982) {
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
     * 用户可借款额度
     * @param type $user
     * @param type $type 1:查询显示额度 2：计算用户可借额度 3：用户可借额度
     */
    public function getUserLoanAmount($user, $type = 1) {
        $user = User::findOne($user->user_id);
        $white = User_quota::find()->where(['user_id' => $user->user_id])->one();
        if ($type == 1) {
            if (!empty($white)) {
                return $white->quota + $white->temporary_quota;
            } else {
                return 1500;
            }
        } elseif ($type == 3) {
            if (!empty($white)) {
                return $white->quota;
            } else {
                return 1500;
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
     * 是否是附带用户(A类用户)
     */
    public static function isPassing($user_id) {
        $user = User::findOne($user_id);
        if (empty($user)) {
            return FALSE;
        }
        if ($user->status != 3) {
            return FALSE;
        }
        $create_time = '2016-09-01 00:00:00';
        if (strtotime($user->create_time) > strtotime($create_time)) {
            return FALSE;
        }
        $status = array(8, 9, 10, 11, 12, 13);
        $loan_times = User_loan::find()->where(['status' => $status, 'user_id' => $user_id])->count();
        if ($loan_times == 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

    public function setBlack() {
        $oApi = new XsApi;
        $res = $oApi->setBlack($this->mobile, $this->identity);
        $this->status = 5;
        $result = $this->save();
        return $result;
    }

    /**
     * 进入白名单
     * @param type $user_id
     */
    public static function inputWhite($user_id) {
        if (empty($user_id)) {
            return FALSE;
        }
        $user = User::findOne($user_id);
        if (empty($user) || $user->status != 3) {
            return FALSE;
        }
        $result = White_list::addBatch($user->identity, $user->realname, $user->mobile, 0, $user_id);
        return $result;
    }

}
