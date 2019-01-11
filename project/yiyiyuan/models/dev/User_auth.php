<?php

namespace app\models\dev;

use app\commonapi\ImageHandler;
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
class User_auth extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_auth';
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

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'from_user_id']);
    }

    public function getUsers() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getRed() {
        return $this->hasOne(Red_packets_grant::className(), ['user_id' => 'from_user_id']);
    }

    public function getReds() {
        return $this->hasOne(Red_packets_grant::className(), ['user_id' => 'user_id']);
    }

    /**
     * 查询认证$user_id成功的用户
     */
    public function getAuthByUserId($user_id) {
        $userIds = User_auth::find()->where(['user_id' => $user_id, 'is_up' => 2])->select('from_user_id')->all();
        return $userIds;
    }

    /**
     * 是否认证了$user_id
     * @param type $user_id
     * @param type $user_id_from  当前用户id
     */
    public function isAuth($user_id, $user_id_from) {
        $user_auth = User_auth::find()->where(['user_id' => $user_id, 'is_up' => 2, 'from_user_id' => $user_id_from, 'is_yyy' => 1])->count();
        if ($user_auth > 0) {
            return $user_auth;
        }
        $user = User::findOne($user_id_from);
        $user_wx = $user->userwx;
        if (!empty($user_wx)) {
            $user_auth_wx = User_auth::find()->where(['user_id' => $user_id, 'is_up' => 2, 'from_user_id' => $user_wx->id, 'is_yyy' => 2])->count();
            if ($user_auth_wx > 0) {
                return $user_auth_wx;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 认证失败的次数
     * @param type $user_id
     * @param type $user_id_from
     * @param type $type  1、$user_id_from为user_id,2、$user_id_from为微信id
     */
    public function authFailNum($user_id, $user_id_from, $type = 1) {
        if ($type == 1) {
            $user_num = User_auth::find()->where(['from_user_id' => $user_id_from, 'user_id' => $user_id, 'is_up' => 1, 'is_yyy' => 1])->count();
            $user = User::findOne($user_id_from);
            $wx_num = 0;
            if (!empty($user->openid)) {
                $wx = Userwx::find()->where(['openid' => $user->openid])->one();
                $wx_num = User_auth::find()->where(['from_user_id' => $wx->id, 'user_id' => $user_id, 'is_up' => 1, 'is_yyy' => 2])->count();
            }
        } else {
            $wx_num = User_auth::find()->where(['from_user_id' => $user_id_from, 'user_id' => $user_id, 'is_up' => 1, 'is_yyy' => 2])->count();
            $user_wx = Userwx::findOne($user_id_from);
            $user = User::find()->where(['openid' => $user_wx->openid])->one();
            $user_num = 0;
            if (!empty($user)) {
                $user_num = User_auth::find()->where(['from_user_id' => $user->user_id, 'user_id' => $user_id, 'is_up' => 1, 'is_yyy' => 1])->count();
            }
        }
        return $user_num + $wx_num;
    }

    /**
     * 认证失败的情况下来调用该函数，判断是否认证过，如果认证过，则不能进行主动认证了
     * @param type $user_id
     * @param type $user_id_from  当前用户id
     * @param type $type 认证类型 1：投资时认证；2：认证游戏时认证；3：其它；
     */
    public function isInvestauth($user_id, $user_id_from, $type = 1) {
        $userauth_invest = User_auth::find()->where(['from_user_id' => $user_id_from, 'user_id' => $user_id, 'is_up' => 1, 'is_yyy' => 1, 'type' => $type])->count();
        if ($userauth_invest > 1) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * 添加一条认证记录
     */
    public function addAuth($condition) {
        if (empty($condition)) {
            return null;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        if ($this->save()) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        } else {
            return false;
        }
    }

    /**
     * 对认证人和被认证人进行提额
     * @param type $$user_id 被认证人
     * @param type $from_user_id 认证人
     */
    public function setAccountUp($user_id, $from_user_id, $type = 6) {
        //认证成功，进行提额,认证人和被认证人都进行提额

        $account = new Account();
        $user_amount = new User_amount_list();
        $ret_user = 1;
        $ret_from_user = 1;
        $user = User::findOne($user_id);
        if (!empty($user)) {
            $invest_amount = User_amount_list::getSumByType($user_id, 6);
            $auth_amount = User_amount_list::getSumByType($user_id, 9);
            $total_amount = $invest_amount + $auth_amount;
            if ($total_amount < 5000) {
                $amount = 5000 - $total_amount > 100 ? 100 : 5000 - $total_amount;
                $user_condition = array(
                    'remain_amount' => $amount,
                    'amount' => $amount,
                    'current_amount' => $amount
                );
                //被认证用户提额
                $ret_user = $account->setAccountinfo($user_id, $user_condition);
                //记录提额的日志
                $amount_date = array(
                    'type' => $type,
                    'user_id' => $user_id,
                    'amount' => $amount
                );
                $user_amount->CreateAmount($amount_date);
            }
        }
        $from_user = User::findOne($from_user_id);
        if (!empty($from_user)) {
            $invest_from_amount = User_amount_list::getSumByType($from_user_id, 6);
            $auth_from_amount = User_amount_list::getSumByType($from_user_id, 9);
            $total_from_amount = $invest_from_amount + $auth_from_amount;
            if ($total_from_amount < 5000) {
                $from_amount = 5000 - $total_from_amount > 100 ? 100 : 5000 - $total_from_amount;
                $from_user_condition = array(
                    'remain_amount' => $from_amount,
                    'amount' => $from_amount,
                    'current_amount' => $from_amount
                );
                //认证用户提额
                $ret_from_user = $account->setAccountinfo($from_user_id, $from_user_condition);
                $amount_date_from = array(
                    'type' => $type,
                    'user_id' => $from_user_id,
                    'amount' => $from_amount
                );
                $user_amount->CreateAmount($amount_date_from);
            }
        }
        if ($ret_user && $ret_from_user) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 删除redis里保存的用户认证题目和图片
     */
    public function delAuthInformation($key, $first_array_key, $second_array_key, $third_array_key) {
        Yii::$app->redis->del($key);
        Yii::$app->redis->del($first_array_key);
        Yii::$app->redis->del($second_array_key);
        Yii::$app->redis->del($third_array_key);

        return true;
    }

    /**
     * 判断认证好友之间的关系
     * @param unknown $code
     * @return unknown
     */
    public function getFriendRelative($user_school_id, $from_user_school_id, $user_identity, $from_user_identity) {
        if ($user_school_id == $from_user_school_id) {
            //用户为同校
            $relative = 1;
        } else {
            $loanidenty = substr($from_user_identity, 0, 4);
            $investidenty = substr($user_identity, 0, 4);
            if ($loanidenty == $investidenty) {
                //同乡
                $relative = 2;
            } else {
                //既非同校也非同乡
                $relative = 3;
            }
        }

        return $relative;
    }

    /**
     * 获取用户的认证信息
     */
    public function getUserInformation($first_array_key, $type, $loan_user_id = '', $loan_user_realname = '', $loan_user_identity = '', $loan_user_school = '', $loan_user_schooltime = '', $loan_user_company = '', $loan_user_position = '') {
        //从redis里获取3条信息，如果没有，则查询
        $first_array = array();
        $first_array_value = Yii::$app->redis->hmget($first_array_key, '0');
        $second_array_value = Yii::$app->redis->hmget($first_array_key, '1');
        $third_array_value = Yii::$app->redis->hmget($first_array_key, '2');
        $first_answer_value = Yii::$app->redis->hmget($first_array_key, '3');
        if (empty($first_array_value[0]) || empty($second_array_value[0]) || empty($third_array_value[0]) || empty($first_answer_value[0])) {
            if ($type == 2) {
                $first_array = $this->getUserRealname($first_array_key, $loan_user_id, $loan_user_realname);
            } else if ($type == 3) {
                $first_array = $this->getUserHome($first_array_key, $loan_user_identity);
            } else if ($type == 4) {
                $first_array = $this->getUserSchool($first_array_key, $loan_user_id, $loan_user_school);
            } else if ($type == 5) {
                $first_array = $this->getUserSchoolTime($first_array_key, $loan_user_id, $loan_user_schooltime);
            } else if ($type == 6) {
                $first_array = $this->getUserBirthdayYear($first_array_key, $loan_user_identity);
            } else if ($type == 7) {
                $first_array = $this->getUserCompany($first_array_key, $loan_user_id, $loan_user_company);
            } else {
                $first_array = $this->getUserPosition($first_array_key, $loan_user_id, $loan_user_position);
            }
        } else {
            $first_array[0]['name'] = $first_array_value[0];
            $first_array[1]['name'] = $second_array_value[0];
            $first_array[2]['name'] = $third_array_value[0];
        }

        return $first_array;
    }

    /**
     * 获取真实姓名
     * @param unknown $mobile
     * @param unknown $loan_mobile
     * @param unknown $loan_user_id
     * @param unknown $loan_user_realname
     * @return unknown
     */
    public function getUserRealname($first_array_key, $loan_user_id, $loan_user_realname) {

        //姓名
        //随机获取3条用户信息
        $other_userinfo = User::find()->select(array('distinct(realname)'))->where("user_id != $loan_user_id and realname != '" . $loan_user_realname . "' and realname!='花二哥'")->andWhere(['status' => 3])->limit(50)->all();
        $rand_realname = array_rand($other_userinfo, 2);
        $first_array[0]['name'] = $loan_user_realname;
        $first_array[1]['name'] = $other_userinfo[$rand_realname[0]]['realname'];
        $first_array[2]['name'] = $other_userinfo[$rand_realname[1]]['realname'];
        $first_answer = $loan_user_realname;
        shuffle($first_array);

        Yii::$app->redis->hmset($first_array_key, '0', $first_array[0]['name'], '1', $first_array[1]['name'], '2', $first_array[2]['name'], '3', $first_answer);

        return $first_array;
    }

    /**
     * 获取家乡
     * @param unknown $code
     * @return unknown
     */
    public function getUserHome($first_array_key, $loan_user_identity) {

        //家乡,获取身份证号的前4位，根据前4位来判断
        $home_number = substr($loan_user_identity, 0, 4);
        $home_name = Score::find()->where(['number' => $home_number])->one();
        //随机取2个城市名称
        $home_other = Score::find()->where("type = 'city' and number != '$home_number'")->limit(50)->all();
        $rand_home = array_rand($home_other, 2);
        $first_array[0]['name'] = isset($home_name) ? $home_name['name'] : '其他';
        $first_array[1]['name'] = $home_other[$rand_home[0]]['name'];
        $first_array[2]['name'] = $home_other[$rand_home[1]]['name'];
        $first_answer = isset($home_name) ? $home_name['name'] : '其他';
        shuffle($first_array);
        Yii::$app->redis->hmset($first_array_key, '0', $first_array[0]['name'], '1', $first_array[1]['name'], '2', $first_array[2]['name'], '3', $first_answer);

        return $first_array;
    }

    /**
     * 获取出生年份
     * @param unknown $code
     * @return unknown
     */
    public function getUserBirthdayYear($first_array_key, $loan_user_identity) {
        //出生年份，随机获取2个不同的出生年份
        $birthday_year = intval(substr($loan_user_identity, 6, 4));
        $birthday_year_one_reduce = $birthday_year - 2;
        $birthday_year_one_add = $birthday_year + 2;
        $birthday_year_second_reduce = $birthday_year - 6;
        $birthday_year_second_add = $birthday_year - 3;
        $birthday_year_third_reduce = $birthday_year + 3;
        $birthday_year_third_add = $birthday_year + 6;
        $first_array[0]['name'] = "($birthday_year_one_reduce" . '年~' . "$birthday_year_one_add" . '年' . ")";
        $first_array[1]['name'] = "($birthday_year_second_reduce" . '年~' . "$birthday_year_second_add" . '年' . ")";
        $first_array[2]['name'] = "($birthday_year_third_reduce" . '年~' . "$birthday_year_third_add" . '年' . ")";
        $first_answer = "($birthday_year_one_reduce" . '年~' . "$birthday_year_one_add" . '年' . ")";
        shuffle($first_array);
        Yii::$app->redis->hmset($first_array_key, '0', $first_array[0]['name'], '1', $first_array[1]['name'], '2', $first_array[2]['name'], '3', $first_answer);

        return $first_array;
    }

    /**
     * 获取用户的学校
     * @param unknown $code
     * @return unknown
     */
    public function getUserSchool($first_array_key, $loan_user_id, $loan_user_school) {
        //毕业院校，随机获取2所不同的学校
        $other_userinfo = User::find()->select(array('distinct(school)'))->where("user_type = 1 and user_id != $loan_user_id and school != '" . $loan_user_school . "' and school != '' and school != '0'")->limit(50)->all();
        $rand_school = array_rand($other_userinfo, 2);
        $first_array[0]['name'] = $loan_user_school;
        $first_array[1]['name'] = $other_userinfo[$rand_school[0]]['school'];
        $first_array[2]['name'] = $other_userinfo[$rand_school[1]]['school'];
        $first_answer = $loan_user_school;
        shuffle($first_array);
        Yii::$app->redis->hmset($first_array_key, '0', $first_array[0]['name'], '1', $first_array[1]['name'], '2', $first_array[2]['name'], '3', $first_answer);

        return $first_array;
    }

    /**
     * 获取用户入学时间
     * @param unknown $code
     * @return unknown
     */
    public function getUserSchoolTime($first_array_key, $loan_user_id, $loan_user_schooltime) {
        //入学年份，随机获取2所不同的入学年份
        $other_userinfo = User::find()->select(array('distinct(school_time)'))->where("user_type = 1 and user_id != $loan_user_id  and school_time != '" . $loan_user_schooltime . "'")->limit(2)->all();
        $first_array[0]['name'] = $loan_user_schooltime;
        $first_array[1]['name'] = $other_userinfo[0]['school_time'];
        $first_array[2]['name'] = $other_userinfo[1]['school_time'];
        $first_answer = $loan_user_schooltime;
        shuffle($first_array);
        Yii::$app->redis->hmset($first_array_key, '0', $first_array[0]['name'], '1', $first_array[1]['name'], '2', $first_array[2]['name'], '3', $first_answer);

        return $first_array;
    }

    /**
     * 获取用户公司
     * @param unknown $code
     * @return unknown
     */
    public function getUserCompany($first_array_key, $loan_user_id, $loan_user_company) {
        //公司，随机获取2个不同的公司
        $other_userinfo = User::find()->select(array('distinct(company)'))->where("user_type = 2 and user_id != $loan_user_id and company != '" . $loan_user_company . "'")->limit(50)->all();
        $rand_company = array_rand($other_userinfo, 2);
        $first_array[0]['name'] = $loan_user_company;
        $first_array[1]['name'] = $other_userinfo[$rand_company[0]]['company'];
        $first_array[2]['name'] = $other_userinfo[$rand_company[1]]['company'];
        $first_answer = $loan_user_company;
        shuffle($first_array);
        Yii::$app->redis->hmset($first_array_key, '0', $first_array[0]['name'], '1', $first_array[1]['name'], '2', $first_array[2]['name'], '3', $first_answer);

        return $first_array;
    }

    /**
     * 获取用户的职位
     * @param unknown $code
     * @return unknown
     */
    public function getUserPosition($first_array_key, $loan_user_id, $loan_user_position) {
        //职位，随机获取2个不同的职位
        $sql_userinfo = "select distinct(u.position) as position,s.name from yi_user as u,yi_score as s where u.user_type=2 and u.user_id!=$loan_user_id and u.position!='" . $loan_user_position . "' and u.position=s.level and s.type='work' limit 2";
        $other_userinfo = Yii::$app->db->createCommand($sql_userinfo)->queryAll();
        //$other_userinfo = User::find()->select(array('distinct(position)'))->where("user_type = 2 and user_id != $loan_id and position != '".$loaninfo['position']."'")->limit(2)->all();
        $aFieldScore = Score::find()->select(array('name'))->where("type='work' and level='" . $loan_user_position . "'")->one();
        $first_array[0]['name'] = $aFieldScore['name'];
        $first_array[1]['name'] = $other_userinfo[0]['name'];
        $first_array[2]['name'] = $other_userinfo[1]['name'];
        $first_answer = $aFieldScore['name'];
        shuffle($first_array);
        Yii::$app->redis->hmset($first_array_key, '0', $first_array[0]['name'], '1', $first_array[1]['name'], '2', $first_array[2]['name'], '3', $first_answer);

        return $first_array;
    }

    /**
     * 获取用户的头像
     * @param unknown $code
     * @return unknown
     */
    public function getUserHeadUrl($third_array_key, $loan_user_id, $loan_user_pic_identity) {
        $first_array_value_url = Yii::$app->redis->hmget($third_array_key, '0');
        $second_array_value_url = Yii::$app->redis->hmget($third_array_key, '1');
        $third_array_value_url = Yii::$app->redis->hmget($third_array_key, '2');
        $third_answer_value = Yii::$app->redis->hmget($third_array_key, '3');
        if (empty($first_array_value_url[0]) || empty($second_array_value_url[0]) || empty($third_array_value_url[0]) || empty($third_answer_value[0])) {
            //随机获取3张照片
            $other_userinfo_pic = User::find()->select(array('pic_identity'))->where("status = 3 and user_type !=3 and user_id != $loan_user_id and pic_identity != ''")->limit(50)->all();
            $rand_array = array_rand($other_userinfo_pic, 2);
//            $third_array[0]['url'] = Yii::$app->params['back_url'] . '/' . $loan_user_pic_identity;
//            $third_array[1]['url'] = Yii::$app->params['back_url'] . '/' . $other_userinfo_pic[$rand_array[0]]['pic_identity'];
//            $third_array[2]['url'] = Yii::$app->params['back_url'] . '/' . $other_userinfo_pic[$rand_array[1]]['pic_identity'];
//            $third_answer = Yii::$app->params['back_url'] . '/' . $loan_user_pic_identity;
            $third_array[0]['url'] = ImageHandler::getUrl($loan_user_pic_identity);
            $third_array[1]['url'] = ImageHandler::getUrl($other_userinfo_pic[$rand_array[0]]['pic_identity']);
            $third_array[2]['url'] = ImageHandler::getUrl($other_userinfo_pic[$rand_array[1]]['pic_identity']);
            $third_answer = ImageHandler::getUrl($loan_user_pic_identity);

            shuffle($third_array);
            Yii::$app->redis->hmset($third_array_key, '0', $third_array[0]['url'], '1', $third_array[1]['url'], '2', $third_array[2]['url'], '3', $third_answer);
        } else {
            $third_array[0]['url'] = $first_array_value_url[0];
            $third_array[1]['url'] = $second_array_value_url[0];
            $third_array[2]['url'] = $third_array_value_url[0];
        }

        return $third_array;
    }

    /**
     * 获取学生用户的答案
     * @param unknown $key
     * @param unknown $first_array_key
     * @param unknown $loan_user_id
     * @param unknown $loan_user_realname
     * @param unknown $loan_user_identity
     * @param unknown $loan_user_school
     * @param unknown $loan_user_school_time
     * @param unknown $loan_user_company
     * @param unknown $loan_user_position
     * @return Ambigous <multitype:, unknown>
     */
    public function getStudentAnswer($key, $first_array_key, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position) {
        switch ($key) {
            case 2:
                $first_array = $this->getUserInformation($first_array_key, 2, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
                break;
            case 3:
                $first_array = $this->getUserInformation($first_array_key, 3, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
                break;
            case 4:
                $first_array = $this->getUserInformation($first_array_key, 4, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
                break;
            case 5:
                $first_array = $this->getUserInformation($first_array_key, 5, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
                break;
            default:
                $first_array = $this->getUserInformation($first_array_key, 6, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
        }

        return $first_array;
    }

    /**
     * 获取社会用户的答案
     * @param unknown $key
     * @param unknown $first_array_key
     * @param unknown $loan_user_id
     * @param unknown $loan_user_realname
     * @param unknown $loan_user_identity
     * @param unknown $loan_user_school
     * @param unknown $loan_user_school_time
     * @param unknown $loan_user_company
     * @param unknown $loan_user_position
     * @return Ambigous <multitype:, unknown>
     */
    public function getSociologyAnswer($key, $first_array_key, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position) {
        switch ($key) {
            case 2:
                $first_array = $this->getUserInformation($first_array_key, 2, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
                break;
            case 3:
                $first_array = $this->getUserInformation($first_array_key, 3, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
                break;
            case 6:
                $first_array = $this->getUserInformation($first_array_key, 6, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
                break;
            default:
                $first_array = $this->getUserInformation($first_array_key, 7, $loan_user_id, $loan_user_realname, $loan_user_identity, $loan_user_school, $loan_user_school_time, $loan_user_company, $loan_user_position);
        }

        return $first_array;
    }

    /**
     * 获取社会人士认证的标题
     * @param unknown $code
     * @return unknown
     */
    public function getSociologyAuthTitle($key) {
        switch ($key) {
            case 2:
                $first_question = 'TA的名字叫什么？';
                break;
            case 3:
                $first_question = 'TA的家乡是哪里？';
                break;
            case 6:
                $first_question = 'TA是哪年出生的？';
                break;
            default:
                $first_question = 'TA是哪个公司的？';
        }

        return $first_question;
    }

    /**
     * 获取学生用户认证的标题
     * @param unknown $code
     * @return unknown
     */
    public function getStudentAuthTitle($key) {
        switch ($key) {
            case 2:
                $first_question = 'TA的名字叫什么？';
                break;
            case 3:
                $first_question = 'TA的家乡是哪里？';
                break;
            case 4:
                $first_question = 'TA是哪个学校的？';
                break;
            case 5:
                $first_question = 'TA的哪年入学的？';
                break;
            default:
                $first_question = 'TA是哪年出生的？';
        }

        return $first_question;
    }

}
