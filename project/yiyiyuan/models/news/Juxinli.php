<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
use app\common\ApiClientCrypt;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_juxinli".
 *
 * @property string $id
 * @property string $user_id
 * @property string $requestid
 * @property string $process_code
 * @property string $status
 * @property string $response_type
 * @property integer $type
 * @property string $user_name
 * @property string $password
 * @property string $last_modify_time
 * @property string $create_time
 */
class Juxinli extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_juxinli';
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'requestid', 'type', 'source'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['process_code', 'status'], 'string', 'max' => 6],
            [['response_type'], 'string', 'max' => 32],
            [['user_name', 'password'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'requestid' => 'Requestid',
            'process_code' => 'Process Code',
            'status' => 'Status',
            'response_type' => 'Response Type',
            'type' => 'Type',
            'user_name' => 'User Name',
            'password' => 'Password',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function addList($condition) {
        if (!empty($condition['user_id'])) {
            $type = isset($condition['type']) ? $condition['type'] : 1;
            $juxinli = (new self())->getJuxinliByUserId($condition['user_id'], $type);
            if (!empty($juxinli)) {
                $result = $juxinli->updateJulixin($condition);
                return $result;
            }
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $time = date('Y-m-d H:i:s');
        $this->last_modify_time = $time;
        $this->create_time = $time;
        $this->process_code = (string) $this->process_code;
        $this->status = (string) $this->status;
        $result = $this->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    /**
     * 保存聚信立数据，通过user_id和type判断是否已经存在数据，如果存在，更新，不存在，新增
     * @param $condition
     * @return bool|string
     */
    public function save_juxinli($condition) {
        if (!is_array($condition) || empty($condition) || !isset($condition['user_id'])) {
            return false;
        }
        $data = $condition;
        $type = isset($data['type']) ? $data['type'] : 1;
        $juxinli = (new self())->getJuxinliByUserId($condition['user_id'], $type);

        if (!empty($juxinli)) {//更新
            return $juxinli->update_julixin($condition);
        }

        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateJulixin($condition) {
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $time = date('Y-m-d H:i:s');
        $this->last_modify_time = $time;
        $this->process_code = (string) $this->process_code;
        $this->status = (string) $this->status;
        $result = $this->save();
        return $result;
    }

    /**
     * 更新用户jxl信息
     * @param $condition
     * @return bool
     */
    public function update_julixin($condition) {
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
     * 获取用户的juxinli信息,并且更新获取时间
     * @param $user_id
     * @param int $type
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getJuxinliByUserId($user_id, $type = 1) {
        if (empty($user_id)) {
            return false;
        }
        $result = Juxinli::find()->where(['user_id' => $user_id, 'type' => $type])->orderBy('create_time desc')->one();
        if ($result && $result->process_code == '10008') {
            $url = "juxinli/query";
            $openApi = new ApiClientCrypt;
            $res = $openApi->sent($url, [
                'phone' => $result->user->mobile,
                'website' => '',
            ]);
            $res = $openApi->parseResponse($res);
            if ($res['res_code'] == 0) {
                $result = $result->updateJxlTime($res['res_data']['create_time']);
            }
        }
        return $result;
    }

    /**
     * 更新用户jxl最后修改时间
     * @param $time
     * @return Juxinli|bool
     */
    public function updateJxlTime($time) {
        try {
            $this->last_modify_time = $time;
            $this->save();
            $result = $this;
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 聚信立认证
     * @param object $user
     * @param array  $arr
     * [
     * "get_type"=>获取数据的类型(1 获取聚信力;2 获取京东 默认为1),
     * 'type'=>执行第几步(1 执行第一步;2 执行第二步),
     * "password"=>(服务密码)
     * "captcha"=>验证码(Type=2时必传)
     * "user_name"=>京东用户名(get_type=2，必传),
     * ]
     * @return array
     * [
     * "rsp_code"=>响应代码(0000 操作成功),
     * "process_code"=>进程码,
     * "process_msg"=>进程码对应的提示,
     * "step"=>下一步执行(0 采集成功1 执行第一步；2 执行第二步；3 直接结束)
     * ]
     */
    public function juxinli($user, $arr) {
        $arr['get_type'] = empty($arr['get_type']) ? 1 : $arr['get_type'];
        if (empty($arr['get_type']) || empty($arr['type']) || empty($arr['password'])) {
            $array = $this->paramErr();
            return $array;
        }
        if ($arr['get_type'] == 2 && empty($arr['user_name'])) {
            $array = $this->paramErr();
            return $array;
        }
        if ($arr['type'] == 2 && empty($arr['captcha'])) {
            $array = $this->paramErr();
            return $array;
        }

        $juxinli = $this->getJuxinliByUserId($user->user_id, $arr['get_type']);
        switch ($arr['get_type']) {
            case 1:
                if (!empty($juxinli) && $juxinli->process_code == '10008' && date('Y-m-d H:i:s', strtotime('-4 month')) < $juxinli->last_modify_time) {
                    $array = $this->reback('0000', $juxinli->process_code, $user, 1);
                    return $array;
                } else {
                    $favModel = new Favorite_contacts();
                    $fav = $favModel->getFavoriteByUserId($user->user_id);
                    $contacts = '';
                    if (!empty($fav)) {
                        $contacts = json_encode(array(
                            array(
                                'contact_tel' => $fav->phone,
                                'contact_name' => $fav->relatives_name,
                                'contact_type' => '0', //亲属
                            ),
                            array(
                                'contact_tel' => $fav->mobile,
                                'contact_name' => $fav->contacts_name,
                                'contact_type' => '6', //常用联系人
                            )
                        ));
                    }
                    switch ($arr['type']) {
                        case 1:
                            $process_code = $this->first($user, $juxinli, $arr['password'], $contacts);
                            break;
                        case 2:
                            $process_code = $this->jusecond($user, $juxinli, $arr['password'], $arr['captcha'], $contacts);
                            break;
                    }
                    $array = $this->reback('0000', $process_code, $user, $arr['type']);
                    return $array;
                }
                break;
            case 2:
                if (!empty($juxinli) && $juxinli->process_code == '10008') {
                    $array = $this->jdreback('0000', $juxinli->process_code);
                    return $array;
                } else {
                    $favModel = new Favorite_contacts();
                    $fav = $favModel->getFavoriteByUserId($user->user_id);
                    $contacts = '';
                    if (!empty($fav)) {
                        $contacts = json_encode(array(
                            array(
                                'contact_tel' => $fav->phone,
                                'contact_name' => $fav->relatives_name,
                                'contact_type' => '0', //亲属
                            ),
                            array(
                                'contact_tel' => $fav->mobile,
                                'contact_name' => $fav->contacts_name,
                                'contact_type' => '6', //常用联系人
                            )
                        ));
                    }
                    switch ($arr['type']) {
                        case 1:
                            $process_code = $this->first($user, $juxinli, $arr['password'], $contacts, $arr['get_type'], $arr['user_name'], $arr['pwd']);
                            break;
                        case 2:
                            $process_code = $this->jusecond($user, $juxinli, $arr['password'], $arr['captcha'], $contacts, $arr['get_type'], $arr['user_name']);
                            break;
                    }
                    $array = $this->jdreback('0000', $process_code);
                    return $array;
                }
                break;
        }
    }

    /**
     * 聚信立第一步操作
     * @param object $user
     * @param object $juxinli
     * @param string $password
     * @param string $contacts
     * @param int $get_type
     * @param string $user_name
     * @param string $pwd
     * @return string
     */
    private function first($user, $juxinli, $password, $contacts = '', $get_type = 1, $user_name = '', $pwd = '') {
        $postData = array(
            'name' => $user->realname,
            'idcard' => $user->identity,
            'phone' => $user->mobile,
            'password' => $password,
            'captcha' => '',
            'type' => 'SUBMIT_CAPTCHA',
            'callbackurl' => '',
        );
        if (!empty($contacts)) {
            $postData['contacts'] = $contacts;
        }
        if ($get_type == 2) {
            $postData['account'] = $user_name;
            $postData['website'] = 'jingdong';
        }
        //$result = Http::juLixin($postData);
        $openApi = new ApiClientCrypt;
        $url = 'juxinli/postrequest';
        $res = $openApi->sent($url, $postData);
        $result = $openApi->parseResponse($res);
        Logger::errorLog(print_r($res, true), 'julixin_s');
        Logger::errorLog(print_r($result, true), 'julixin');

        if ($result['res_code'] == 0) {
            $condition['user_id'] = $user->user_id;
            $condition['requestid'] = isset($result['res_data']['requestid']) ? $result['res_data']['requestid'] : '';
            $condition['process_code'] = isset($result['res_data']['process_code']) ? (string) $result['res_data']['process_code'] : '';
            $condition['status'] = isset($result['res_data']['status']) ? (string) $result['res_data']['status'] : '';
            $condition['response_type'] = isset($result['res_data']['response_type']) ? $result['res_data']['response_type'] : '';
            if ($get_type == 2) {
                $condition['user_name'] = $user_name;
                $condition['password'] = $pwd;
            }
            $condition['type'] = $get_type;
            if (!empty($juxinli)) {
                $juxinli->update_julixin($condition);
            } else {
                $juxinliModel = new Juxinli();
                $juxinliModel->save_juxinli($condition);
            }
            return $condition['process_code'];
        } else {
            return '0';
        }
    }

    /**
     * 聚信立第二步操作
     * @param object $user
     * @param object $juxinli
     * @param string $password
     * @param int $captcha
     * @param string $contacts
     * @param int $get_type
     * @param string $user_name
     * @return string
     */
    private function Jusecond($user, $juxinli, $password, $captcha, $contacts = '', $get_type = 1, $user_name = '') {
        $postData = array(
            'requestid' => $juxinli->requestid, // 请求唯一号
            'password' => $password, // 服务密码
            'captcha' => $captcha, //验证码
            'type' => 'SUBMIT_CAPTCHA',
        );
        if (!empty($contacts)) {
            $postData['contacts'] = $contacts;
        }
        if ($get_type == 2) {
            $postData['account'] = $user_name;
            $postData['website'] = 'jingdong';
        }
        //$result = Http::juLixin($postData, $juxinli);
        $openApi = new ApiClientCrypt;
        $url = 'juxinli/postretry';
        $res = $openApi->sent($url, $postData);
        $result = $openApi->parseResponse($res);
        Logger::errorLog(print_r($res, true), 'julixin_s');
        Logger::errorLog(print_r($result, true), 'julixin');

        if ($result['res_code'] == 0) {
            $condition['user_id'] = $user->user_id;
            $condition['requestid'] = isset($result['res_data']['requestid']) ? $result['res_data']['requestid'] : $juxinli->requestid;
            $condition['process_code'] = isset($result['res_data']['process_code']) ? (string) $result['res_data']['process_code'] : $juxinli->process_code;
            $condition['status'] = isset($result['res_data']['status']) ? (string) $result['res_data']['status'] : $juxinli->status;
            $condition['response_type'] = isset($result['res_data']['response_type']) ? $result['res_data']['response_type'] : $juxinli->response_type;
            $condition['type'] = $get_type;
            $juxinli->update_julixin($condition);
            return $condition['process_code'];
        } else {
            return '0';
        }
    }

    /**
     * 京东结果
     * @param string $code
     * @param string $process_code
     * @return int
     */
    private function jdreback($code, $process_code) {
        $code_array = array(
            '10002' => '',
            '10003' => '请填写正确的京东登陆密码',
            '10004' => '请填写正确的验证码',
            '10006' => '请填写新的验证码',
            '10007' => '请填写正确的京东登陆密码',
            '10008' => '认证成功',
            '10009' => '',
            '10010' => '',
            '11000' => '请重新获取验证码',
            '30000' => '请稍后再试',
            '31000' => '',
            '0' => '请稍后再试',
        );
        $end = array('10007', '10009', '10010', '30000', '31000', '0'); //采集结束
        $sec = array('10002', '10004', '10006'); //执行第二步
        if ($process_code == '10008') {
            $step = 0;
        } else if (in_array($process_code, $end)) {//结束采集
            $step = 3;
        } else if (in_array($process_code, $sec)) {//执行第二步
            $step = 2;
        } else {//重新走第一步
            $step = 1;
        }
        $process_msg = isset($code_array[$process_code]) ? $code_array[$process_code] : '';
        $array['rsp_code'] = $code;
        $array['process_code'] = (string) $process_code;
        $array['process_msg'] = $process_msg;
        $array['step'] = $step;
        return $array;
    }

    /**
     * 聚信力手机号码验证
     * @param string $code
     * @param string $process_code
     * @param object $user
     * @param int    $nowstep 当前请求时的step 1：第一步，2：第二步
     * @return int
     */
    private function reback($code, $process_code, $user, $nowstep) {
        $code_array = array(
            '10001' => '再次输入短信验证码',
            '10002' => '验证码已经发送到尾号' . substr($user->mobile, 7) . '的手机，请查收。',
            '10003' => '服务密码错误，请重新填写',
            '10004' => '验证码错误，请重新填写',
            '10006' => '验证码过期，请重新获取',
            '10007' => '服务密码过于简单，请到运营商处重置密码',
            '10008' => '认证成功',
            '10009' => '认证失败',
            '10010' => '新密码格式错误',
            '11000' => '请重新获取验证码',
            '30000' => '认证失败！请咨询手机运营商',
            '31000' => '重置密码失败，请咨询运营商重置密码',
            '0' => '网络异常，请稍后再试',
        );
        $end = array('10007', '10009', '10010', '30000', '31000', '0'); //采集结束
        $sec = array('10001', '10002', '10004'); //执行第二步
        if ($process_code == '10008') {
            $step = 0;
        } else if (in_array($process_code, $end)) {//结束采集
            $step = 3;
        } else if (in_array($process_code, $sec)) {//执行第二步
            $step = 2;
        } else {//重新走第一步
            $step = 1;
        }
        $process_msg = isset($code_array[$process_code]) ? $code_array[$process_code] : '';
        $array['rsp_code'] = $code;
        $array['process_code'] = (string) $process_code;
        $array['process_msg'] = $process_msg;
        $array['step'] = $step;
        //当前请求时是第二步，并且需要请求第二步：1弹窗
        if ($nowstep == 2 && $step = 2) {
            $array['show_dialog'] = 1;
        } else {
            $array['show_dialog'] = 0;
        }
        return $array;
    }

    /**
     * 获取参数错误信息
     * @return array
     */
    private function paramErr() {
        $array['rsp_code'] = '0000';
        $array['process_code'] = "99994";
        $array['process_msg'] = "参数不能为空";
        $array['step'] = 3;
        return $array;
    }

    /**
     * 是否已经认证运营商
     * @param type $user_id
     */
    public function isAuthYunyingshang($user_id) {
        $o = (new self())->getJuxinliByUserId($user_id, 1);
        if (empty($o) || $o->process_code != '10008') {
            return FALSE;
        }
        $time = date('Y-m-d H:i:s');
        $end_time = date('Y-m-d H:i:s', strtotime('+4 months', strtotime($o->last_modify_time)));
        if ($time > $end_time) {
            return FALSE;
        }
        return TRUE;
    }

}
