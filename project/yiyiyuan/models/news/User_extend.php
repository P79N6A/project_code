<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
use app\models\news\User_history_info;

class User_extend extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_extend';
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id'], 'required'],
            [['user_id', 'school_valid', 'school_id', 'industry', 'marriage', 'home_area', 'company_area', 'version', 'is_new', 'is_callback'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['uuid'], 'string', 'max' => 55],
            [['telephone', 'email', 'income'], 'string', 'max' => 32],
            [['school', 'edu', 'school_time'], 'string', 'max' => 64],
            [['company', 'position', 'profession', 'home_address', 'company_address'], 'string', 'max' => 128],
            [['reg_ip'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'uuid' => 'Uuid',
            'school_valid' => 'School Valid',
            'school_id' => 'School ID',
            'school' => 'School',
            'edu' => 'Edu',
            'school_time' => 'School Time',
            'industry' => 'Industry',
            'company' => 'Company',
            'position' => 'Position',
            'profession' => 'Profession',
            'telephone' => 'Telephone',
            'marriage' => 'Marriage',
            'email' => 'Email',
            'income' => 'Income',
            'home_area' => 'Home Area',
            'home_address' => 'Home Address',
            'company_area' => 'Company Area',
            'company_address' => 'Company Address',
            //'version' => 'Version',
            'is_new' => 'Is New',
            'is_callback' => 'Is Callback',
            'reg_ip' => 'Reg Ip',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public static function getUserExtend($user_id) {
        $user_extend = User_extend::find()->where(['user_id' => $user_id])->one();
        return $user_extend;
    }

    public function getEdu() {
        switch ($this->edu) {
            case 1:
                $ext_diploma = '博士';
                break;
            case 2:
                $ext_diploma = '硕士';
                break;
            case 3:
                $ext_diploma = '本科';
                break;
            default:
                $ext_diploma = '专科';
        }
        return $ext_diploma;
    }

    /**
     *  添加User_extend(通过user_id判断是否存在)，不存在就新增，存在就修改
     *  @param  $condition  array   array('field'=>fieldvalue);
     *  @return bool
     */
    public function addRecord($condition) {
        if (empty($condition) || !isset($condition['user_id'])) {
            return false;
        }
        $user_extend = User_extend::getUserExtend($condition['user_id']);
        if (!empty($user_extend)) {
            $result = $user_extend->updateRecord($condition);
        } else {
            $data = $condition;
            $data['version'] = 1;
            $data['is_new'] = 1;
            $data['last_modify_time'] = date('Y-m-d H:i:s');
            $data['create_time'] = date('Y-m-d H:i:s');
            $error = $this->chkAttributes($data);
            if ($error) {
                return false;
            }
            $result = $this->save();
        }
        return $result;
    }

    /**
     * 修改数据
     * @param $condition  array   array('field'=>fieldvalue);
     * @return bool
     */
    public function updateRecord($condition) {
        if (empty($condition) || !isset($condition['user_id'])) {
            return false;
        }
        $user = User::findOne($condition['user_id']);
        $user_extend = User_extend::getUserExtend($user->user_id);
        if ((isset($condition['edu']) || isset($condition['marriage'])) && !empty($user_extend->home_address)) {
            $data_type = 3;
            $h_condition = [
                'user_id' => $user->user_id,
                'user_type' => $user->user_type,
                'data_type' => $data_type,
                'industry_edu' => $user_extend->edu,
                'marriage' => $user_extend->marriage,
                'area' => $user_extend->home_area,
                'address' => $user_extend->home_address,
            ];
            $history_id = User_history_info::addHistoryInfo($user, $h_condition);
        } else if (isset($condition['email']) && !empty($user_extend->company)) {
            $data_type = 2;
            $h_condition = [
                'user_id' => $user->user_id,
                'user_type' => $user->user_type,
                'data_type' => $data_type,
                'company_school' => $user_extend->company,
                'industry_edu' => $user_extend->industry,
                'position_schooltime' => $user_extend->position,
                'telephone' => $user_extend->telephone,
                'area' => $user_extend->company_area,
                'address' => $user_extend->company_address,
                'profession' => $user_extend->profession,
                'email' => $user_extend->email,
                'income' => $user_extend->income,
            ];
            $history_id = User_history_info::addHistoryInfo($user, $h_condition);
        }

        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        //$this->version = $this->version + 1;
        $this->is_new = 1;
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 修改用户拓展数据方法
     * @param $condition
     * @date 2017/07/11
     * @return bool
     * @author Zhangchao <zhangchao@xianhuahua.com>
     */
    public function update_extend($condition) {
        if (empty($condition) || !isset($condition['user_id'])) {
            return false;
        }
        $user = User::findOne($condition['user_id']);
        $user_extend = User_extend::getUserExtend($user->user_id);
        if (isset($condition['company']) && !empty($user_extend->company)) {
            $data_type = 2;
            $h_condition = [
                'user_id' => $user->user_id,
                'user_type' => $user->user_type,
                'data_type' => $data_type,
                'company_school' => $user_extend->company,
                'industry_edu' => $user_extend->industry,
                'position_schooltime' => $user_extend->position,
                'telephone' => $user_extend->telephone,
                'area' => $user_extend->company_area,
                'address' => $user_extend->company_address,
                'profession' => $user_extend->profession,
                'email' => $user_extend->email,
                'income' => $user_extend->income,
            ];
            $history_id = (new User_history_info())->save_historyinfo($h_condition);
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['is_new'] = 1;
        $error = $user_extend->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $user_extend->save();
            return $result;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    /**
     * 添加用户拓展数据方法
     * @param $condition
     * @return bool
     */
    public function save_extend($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $user_extend = User_extend::getUserExtend($condition['user_id']);
        if (!empty($user_extend)) {
            $result = $user_extend->update_extend($condition);
        } else {
            $data = $condition;
            $data['version'] = 1;
            $data['is_new'] = 1;
            $data['last_modify_time'] = date('Y-m-d H:i:s');
            $data['create_time'] = date('Y-m-d H:i:s');
            $error = $this->chkAttributes($data);
            if ($error) {
                return false;
            }
            $result = $this->save();
        }
        return $result;
    }

}
