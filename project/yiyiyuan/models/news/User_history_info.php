<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_user_history_info".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $user_type
 * @property integer $data_type
 * @property string $company_school
 * @property integer $industry_edu
 * @property string $position_schooltime
 * @property string $telephone
 * @property integer $marriage
 * @property integer $area
 * @property string $address
 * @property integer $profession
 * @property string $email
 * @property string $income
 * @property string $create_time
 */
class User_history_info extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_history_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'user_type', 'data_type', 'company_school', 'create_time'], 'required'],
            [['user_id', 'user_type', 'data_type', 'industry_edu', 'marriage', 'area', 'profession'], 'integer'],
            [['create_time'], 'safe'],
            [['company_school', 'address'], 'string', 'max' => 128],
            [['position_schooltime'], 'string', 'max' => 64],
            [['telephone', 'email', 'income'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'user_type' => 'User Type',
            'data_type' => 'Data Type',
            'company_school' => 'Company School',
            'industry_edu' => 'Industry Edu',
            'position_schooltime' => 'Position Schooltime',
            'telephone' => 'Telephone',
            'marriage' => 'Marriage',
            'area' => 'Area',
            'address' => 'Address',
            'profession' => 'Profession',
            'email' => 'Email',
            'income' => 'Income',
            'create_time' => 'Create Time',
        ];
    }
    
       /**
     * 添加用户信息更改记录
     */
    public static function addHistoryInfo($user, $condition) {
        if (empty($condition) || !isset($condition['data_type'])) {
            return false;
        }
        $history_info = new User_history_info();
        foreach ($condition as $key => $val) {
            $history_info->{$key} = $val;
        }
        $history_info->create_time = date('Y-m-d H:i:s');
        if(empty($history_info->company_school)){
            $history_info->company_school = 'NULL';
        }
        if ($history_info->save()) {
            $id = Yii::$app->db->getLastInsertID();
            return $id;
        } else {
            return false;
        }
    }
    
    /**
     *添加用户信息更改记录 
     */
    public function save_historyinfo($condition) {
        if (empty($condition) || !isset($condition['data_type'])) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
    
    /**
     * 获取最近一条历史修改记录
     * @param $user_id
     * @return array|bool|
     */
    public function newestHistory($user_id)
    {
        if (empty($user_id)) return false;
        $history_info = User_history_info::find()->where(['user_id'=>$user_id])->orderBy(['create_time'=>SORT_DESC])->one();
        if (!empty($history_info)){
            return $history_info;
        }
        return array();
    }
}
