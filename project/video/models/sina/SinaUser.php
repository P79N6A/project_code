<?php

namespace app\models\sina;

use app\common\Http;
use app\common\Logger;
use app\models\App;
use app\modules\api\common\sinapay\Sinapay;

/**
 * 新浪绑定会员
 */
class SinaUser extends \app\models\BaseModel {
    /**
     * 新浪处理类
     * @var [type]
     */
    private $sinapay;
    public function __construct() {
        $this->sinapay = new Sinapay();
    }
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'sina_user';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'user_id', 'identity_id', 'name', 'idcard', 'phone', 'create_time', 'modify_time'], 'required'],
            [['aid', 'idcard_valid', 'binding_valid', 'password_valid', 'version'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['user_id', 'idcard'], 'string', 'max' => 20],
            [['identity_id', 'name'], 'string', 'max' => 30],
            [['phone'], 'string', 'max' => 60],
            [['passwordurl'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '结算记录ID',
            'aid' => '应用id',
            'user_id' => '客户端user_id',
            'identity_id' => '新浪userid, 由aid_user_id组成',
            'name' => '用户姓名',
            'idcard' => '用户身份证',
            'idcard_valid' => '实名:0未; 1:是',
            'phone' => '用户手机',
            'binding_valid' => '绑定验证(手机):0未; 1:是',
            'passwordurl' => '密码回调地址',
            'password_valid' => '密码认证:0未; 1:是(激活才可出款)',
            'create_time' => '创建时间',
            'modify_time' => '更新时间',
            'version' => '乐观锁',
        ];
    }
    /**
     * 根据唯一标识获取纪录
     * @param  string $identity_id 唯一标识
     * @return obj
     */
    public static function getByIdentityId($identity_id) {
        return static::find()->where(['identity_id' => $identity_id])->limit(1)->one();
    }

    /**
     * 根据唯一标识获取纪录
     * @param  string $identity_id 唯一标识
     * @return obj
     */
    public function validUser() {
        $is_valid = $this->idcard_valid &&
        $this->binding_valid &&
        $this->password_valid;
        return $is_valid;
    }

    /**
     * 注册接口
     * @param [] $data
     * @return bool
     */
    public function regs($data) {
        //1 参数验证
        $regData = [
            'aid' => $data['aid'],
            'user_id' => $data['user_id'],
            'identity_id' => $data['identity_id'],
            'name' => $data['name'],
            'idcard' => $data['idcard'],
            'phone' => $data['phone'],
            'ip' => $data['ip'],
        ];

        //2. 注册或查找
        $user_model = static::getByIdentityId($regData['identity_id']);
        if (!$user_model) {
            $user_model = $this->regs_member($regData);
            if (!$user_model) {
                return $this->returnError(null, $this->errinfo);
            }
        }

        //3 实名
        $res = $user_model->regs_realname($regData['identity_id'], $regData['name'], $regData['idcard'], $regData['ip']);
        if (!$res) {
            return $this->returnError(null, $user_model->errinfo);
        }

        //4 手机认证
        $res = $user_model->regs_binding($regData['identity_id'], $regData['phone'], $regData['ip']);
        if (!$res) {
            return $this->returnError(null, $user_model->errinfo);
        }
        return $user_model;
    }
    /**
     * 第一步注册
     * @return [type] [description]
     */
    public function regs_member($data) {
        //1 查询 user 手机号是否重复, 该手机已经被绑定.
        $count = static::find()->where(['phone' => $data['phone'], 'binding_valid' => 1])->count();
        if ($count) {
            return $this->returnError(null, json_encode([
                'response_code' => '__USER_DEFINED',
                'response_message' => '此手机号已经被占用',
            ]));
        }

        //2. 调用注册接口
        $res = $this->sinapay->create_activate_member($data['identity_id'], $data['ip']);
        // 失败时重试一次
        if ( $this->sinapay->isTimeout() ) {
            $res = $this->sinapay->create_activate_member($data['identity_id'], $data['ip']);
        }

        if (!$res) {
            return $this->returnError(null, $this->sinapay->errinfo);
        }

        //2. 添加到db中
        $result = $this->saveData($data);
        if (!$result) {
            return $this->returnError(null, json_encode([
                'response_code' => '__USER_DEFINED',
                'response_message' => 'regs save error',
            ]));
        }
        return $this;
    }
    /**
     * 第二步注册
     * @return bool
     */
    public function regs_realname($identity_id, $name, $idcard, $ip) {
        if (!$this->idcard_valid) {
            $res = $this->sinapay->set_real_name($identity_id, $name, $idcard, $ip);
            // 失败时重试一次
            if ($this->sinapay->isTimeout()) {
                $res = $this->sinapay->set_real_name($identity_id, $name, $idcard, $ip);
            }
            if (!$res) {
                return $this->returnError(false, $this->sinapay->errinfo);
            }
            $this->name = $name;
            $this->idcard = $idcard;
            $this->idcard_valid = 1;
            $res = $this->save();
        }
        $ok = $name == $this->name && $idcard == $this->idcard;
        if (!$ok) {
            return $this->returnError(false, json_encode([
                'response_code' => '__USER_DEFINED',
                'response_message' => 'name,idcard cant match',
            ]));
        }
        return true;
    }
    /**
     * 第三步绑定
     * @return bool
     */
    public function regs_binding($identity_id, $phone, $ip) {
        if (!$this->binding_valid) {
            $res = $this->sinapay->binding_verify($identity_id, $phone, $ip);
            // 失败时重试一次
            if ( $this->sinapay->isTimeout() ) {
                $res = $this->sinapay->binding_verify($identity_id, $phone, $ip);
            }
            if (!$res) {
                return $this->returnError(false, $this->sinapay->errinfo);
            }
            $this->phone = $phone;
            $this->binding_valid = 1;
            $res = $this->save();
        }
        $ok = $phone == $this->phone;
        if (!$ok) {
            return $this->returnError(false, json_encode([
                'response_code' => '__USER_DEFINED',
                'response_message' => 'phone is not match',
            ]));
        }
        return true;
    }

    /**
     * 添加一条纪录到数据库
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveData($data) {
        //保存数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'aid' => $data['aid'],
            'user_id' => $data['user_id'],
            'identity_id' => $data['identity_id'],
            'name' => $data['name'],
            'idcard' => $data['idcard'],
            'idcard_valid' => 0,
            'phone' => $data['phone'],
            'binding_valid' => 0,
            'passwordurl' => '',
            'password_valid' => 0,
            'create_time' => $time,
            'modify_time' => $time,
        ];
        $errors = $this->chkAttributes($data);
        if ($errors) {
            Logger::dayLog('sinauser', '保存失败', $data, $errors);
            return false;
        }

        return $this->save();
    }
    /**
     * 未激活的密码
     * @return [type] [description]
     */
    public function pwdNoSet() {
        $time = time();
        $where = [
            'AND',
            ['>=', 'modify_time', date('Y-m-d H:i:s', $time - 3600)],
            ['<', 'modify_time', date('Y-m-d H:i:s', $time - 600)],
            ['!=', 'passwordurl', ''],
            ['password_valid' => 0],
        ];
        $data = static::find()->where($where)->limit(500)->all();
        return $data;
    }
    /**
     * 通知客户端
     * @param  [type] $this [description]
     * @return [type]        [description]
     */
    public function notifypassword() {
        //1 纪录日志
        $identity_id = $this->identity_id;
        Logger::dayLog('sinaback/passwordurl', 'crontab', $identity_id);

        //2  检测本地已经更新过了，则没必要再处理一次
        $error_msg = '';
        $sinapay = new Sinapay();
        $res = $sinapay->query_is_set_pay_password($identity_id);
        if ($res) {
            $this->refresh();
            $this->password_valid = 1;
            //$this->modify_time = date('Y-m-d H:i:s');
            $dbres = $this->save();
        } else {
            $err = json_decode($sinapay->errinfo, true);
            $error_msg = $err['response_message'];
        }

        //3  加密响应结果
        $res_data = [
            'password_valid' => $this->password_valid,
            'user_id' => $this->user_id,
            'identity_id' => $this->identity_id,
            'error_msg' => $error_msg,
        ];
        $responseData = App::model()->encryptData($this->aid, $res_data);
        if (empty($responseData)) {
            Logger::dayLog('sinaback/passwordurl', 'notice', '无法加密', 'appid', $this->aid, 'res_data', $res_data);
            return false;
        }

        //4 获取给客户端的回调地址
        $this->curlPost($this->passwordurl, $responseData);
        return true;
    }

    /**
     * 获取数据
     * @param array $data
     * @param str2json
     * @return null
     */
    private function curlPost($fcallbackurl, $responseData) {
        // 跳转到客户端地址
        $res = Http::interface_post($fcallbackurl, ['res_data' => $responseData, 'res_code' => 0]);
        Logger::dayLog('sinaback/passwordurl', 'crontab', $fcallbackurl, $res);
    }
}
