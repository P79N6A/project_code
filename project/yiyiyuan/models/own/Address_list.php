<?php

namespace app\models\own;

use app\commonapi\Logger;
use app\models\news\Address_list_new;
use app\models\news\User;
use Exception;
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
class Address_list extends OwnNewBaseModel {

    public $head;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'address_list';
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

    public function __destruct() {
        Yii::$app->dbanalysis->close();
    }

    /**
     * 查询用户的某一条记录
     */
    public function findMobile($user_id, $mobile, $name = '') {
        $user = User::findOne($user_id);
        $list = self::find()->where(['user_phone' => $user->mobile, 'phone' => $mobile]);
        if (!empty($name)) {
            $list = $list->andWhere(['name' => $name]);
        }
        $list = $list->one();
        return $list;
    }

    /**
     * 查询用户的所有记录
     */
    public function findAllMobile($user_id) {
        $user = User::findOne($user_id);

        Logger::dayLog('inputaddress/findlist', 'start', $user_id, microtime(TRUE));
        $list = self::find()->where(['user_phone' => $user->mobile])->all();
        Logger::dayLog('inputaddress/findlist', 'start', $user_id, microtime(TRUE));
        return $list;
    }

    public function updateMobile($condition) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->modify_time = date('Y-m-d H:i:s');
        $this->save();
        return $this;
    }

    /**
     * 保存某一帐号的手机号
     * @param  int $user_id
     * @param  [] $mobiles
     * @return   int
     */
    public function saveMobiles($user_id, $mobiles) {
        //1 验证是否合法
        if (!is_array($mobiles)) {
            return 0;
        }
        $user_id = intval($user_id);
        if (!$user_id) {
            return 0;
        }
        $user = User::findOne($user_id);
        if (empty($user)) {
            return 0;
        }

        //2 转成数据形式并去重
        $valid_mobiles = $this->getMobiles($user_id, $mobiles, $user);
        if (!is_array($valid_mobiles) || empty($valid_mobiles)) {
            return 0;
        }

        //3 再从数据库去重同名同手机
        $total = $this->getCount($user_id); //
        $limit = 1000; // 每1000条处理一次
        $pages = ceil($total / $limit);

        //3 分批取出一千条数据
        for ($i = 0; $i < $pages; $i++) {
            $dbData = $this->getData($user_id, $i * $limit, $limit);
            if (!is_array($dbData)) {
                break;
            }
            foreach ($dbData as $val) {
                $key = $val->phone . "_" . $val->name;
                if (isset($valid_mobiles[$key])) {
                    unset($valid_mobiles[$key]);
                }
            }
        }
        Logger::dayLog('inputaddress/batch', 'start', $user_id, microtime(TRUE));
        //4 分批添加到数据库
        $phone_groups = array_chunk($valid_mobiles, 1000);
        $total = 0;
        foreach ($phone_groups as $group) {
            try {
                $oReversseModel = new Address_list_new();
                $total += $oReversseModel->insertBatch($group);
            } catch (Exception $ex) {
                $error = $ex->getMessage();
                Logger::dayLog('inputaddress/batch', 'in', $user_id, microtime(TRUE), $error);
            }
        }
        Logger::dayLog('inputaddress/batch', 'end', $user_id, microtime(TRUE));

        //5 返回数据量
        return $total;
    }

    /**
     * 获取总数
     * @param  int $user_id
     * @return int 数量
     */
    private function getCount($user_id) {
        $user = User::findOne($user_id);
        if (empty($user)) {
            return 0;
        }
        $total = static::find()->where(['user_phone' => $user->mobile])->count();
        return $total;
    }

    /**
     * 获取
     * @param  int  $user_id
     * @param  int  $offset
     * @param  int $limit
     * @return
     */
    private function getData($user_id, $offset, $limit = 1000) {
        $user = User::findOne($user_id);
        if (empty($user)) {
            return 0;
        }
        return static::find()->select(['name', 'phone'])->where(['user_phone' => $user->mobile])->offset($offset)->limit($limit)->all();
    }

    /**
     * 去重并删除不合法数据
     * @param  [] $mobiles 手机号
     * @return []
     */
    private function getMobiles($user_id, $mobiles, $user) {
        if (empty($mobiles)) {
            return null;
        }
        $user_id = intval($user_id);
        if (!$user_id) {
            return 0;
        }

        $time = date('Y-m-d H:i:s');
        $valid_mobiles = [];
        foreach ($mobiles as $val) {
            //1 验证合法性
            if (!isset($val->number) || !isset($val->name) || $val->number == $val->name) {
                continue;
            }

            $val->number = preg_replace('/[^0-9]/', '', $val->number);
            preg_match_all('/[\x{4e00}-\x{9fff}\da-zA-Z]+/u', $val->name, $matches);
            $val->name = join('', $matches[0]);
            if (empty($val->name)) {
                continue;
            }
            $val->name = mb_substr($val->name, 0, 20, 'utf-8');
            $str = substr($val->number, -11);
            if (preg_match("/^(1(([3578][0-9])|(47)))\d{8}$/", $str)) {
                $val->number = $str;
            }
            if (strlen($val->number) < 7 || strlen($val->number) >= 18) {
                continue;
            }
            $key = $val->number . "_" . $val->name;

            //2 仅添加 一次同名手机号
            if (isset($valid_mobiles[$key])) {
                continue;
            }
            $valid_mobiles[$key] = [
                'aid' => 1,
                'user_id' => $user_id,
                'user_phone' => $user->mobile,
                'phone' => $val->number,
                'name' => $val->name,
                'modify_time' => $time,
                'create_time' => $time,
            ];
        }
        return $valid_mobiles;
    }

    /**
     * 保存某一帐号的手机号 进件调用
     * @param  int $user_id
     * @param  [] $mobiles
     * @return   int
     */
    public function saveMobilesLoanCheck($user_id, $mobiles) {
        //1 验证是否合法
        if (!is_array($mobiles)) {
            return 0;
        }
        $user_id = intval($user_id);
        if (!$user_id) {
            return 0;
        }

        //2 转成数据形式并去重
        $valid_mobiles = $this->getMobilesLoanCheck($user_id, $mobiles);
        if (!is_array($valid_mobiles) || empty($valid_mobiles)) {
            return 0;
        }

        //3 再从数据库去重同名同手机
        $total = $this->getCount($user_id); //
        $limit = 1000; // 每1000条处理一次
        $pages = ceil($total / $limit);

        //3 分批取出一千条数据
        for ($i = 0; $i < $pages; $i++) {
            $dbData = $this->getData($user_id, $i * $limit, $limit);
            if (!is_array($dbData)) {
                break;
            }
            foreach ($dbData as $val) {
                $key = $val->phone . "_" . $val->name;
                if (isset($valid_mobiles[$key])) {
                    unset($valid_mobiles[$key]);
                }
            }
        }
        Logger::dayLog('inputaddress/batch', 'start', $user_id, microtime(TRUE));
        //4 分批添加到数据库
        $phone_groups = array_chunk($valid_mobiles, 1000);
        $total = 1;
        foreach ($phone_groups as $group) {
            try {
                $total += $this->insertBatch($group);
            } catch (Exception $ex) {
                $error = $ex->getMessage();
                Logger::dayLog('inputaddress/batch', 'in', $user_id, microtime(TRUE), $error);
            }
        }
        Logger::dayLog('inputaddress/batch', 'end', $user_id, microtime(TRUE));
        //5 返回数据量
        return $total;
    }

    /**
     * 去重并删除不合法数据
     * @param  [] $mobiles 手机号
     * @return []
     */
    private function getMobilesLoanCheck($user_id, $mobiles) {
        if (empty($mobiles)) {
            return null;
        }
        $user_id = intval($user_id);
        if (!$user_id) {
            return 0;
        }
        $time = date('Y-m-d H:i:s');
        $valid_mobiles = [];
        foreach ($mobiles as $val) {
            //1 验证合法性
            $val = json_decode($val);
            //1 验证合法性
            if (!isset($val->number) || !isset($val->name) || $val->number == $val->name) {
                continue;
            }

            $val->number = preg_replace('/[^0-9]/', '', $val->number);
            preg_match_all('/[\x{4e00}-\x{9fff}\da-zA-Z]+/u', $val->name, $matches);
            $val->name = join('', $matches[0]);
            if (empty($val->name)) {
                continue;
            }
            $val->name = mb_substr($val->name, 0, 20, 'utf-8');
            $str = substr($val->number, -11);
            if (preg_match("/^(1(([3578][0-9])|(47)))\d{8}$/", $str)) {
                $val->number = $str;
            }
            if (strlen($val->number) < 7 || strlen($val->number) >= 18) {
                continue;
            }
            $key = $val->number . "_" . $val->name;

            //2 仅添加 一次同名手机号
            if (isset($valid_mobiles[$key])) {
                continue;
            }
            $valid_mobiles[$key] = [
                'user_id' => $user_id,
                'phone' => $val->number,
                'name' => $val->name,
                'modify_time' => $time,
                'create_time' => $time,
            ];
        }
        return $valid_mobiles;
    }

    /**
     * 查询用户通讯录记录
     * @param type $user_id
     * @param type $phone
     * @return type
     */
    public function getAddressList($user_id, $phone = '', $limit = 1) {
        $oUserModel = new User();
        Logger::dayLog('inputaddress/list', 'start', $user_id, $limit, microtime(TRUE));
        $oUser = $oUserModel->getUserinfoByUserId($user_id);
        $data = self::find()->where(['user_phone' => $oUser->mobile]);
        if (!empty($phone)) {
            $data = $data->andWhere(['phone' => $phone]);
        }
        if ($limit == 1) {
            $oAddress = $data->all();
        } else {
            $oAddress = $data->one();
        }
        Logger::dayLog('inputaddress/list', 'start', $user_id, $limit, microtime(TRUE));
        return $oAddress;
    }

    /**
     * 查询用户的通讯录条数
     * @param type $user_id
     * @param type $phone
     * @return type
     */
    public function getAddressCount($user_id) {
        $total = $this->getCount($user_id);
        return $total;
    }

    /**
     * 姓名模糊查询用户通讯录记录
     * @param type $user_id
     * @param type $phone
     * @return type
     */
    public function getLikeAddressList($user_id, $name, $limit = '', $offset = '', $andWhere = '') {
        $oUserModel = new User();
        $oUser = $oUserModel->getUserinfoByUserId($user_id);
        if(empty($oUser)){
            return null;
        }
        $data = self::find()->where(['user_phone' => $oUser->mobile]);
        if (!empty($andWhere)) {
            $data = $data->andWhere($andWhere);
        }
        if (!empty($limit) && $offset !== '') {
            $data = $data->limit($limit)->offset($offset);
        }
        $oAddress = $data->all();
        return $oAddress;
    }

}
