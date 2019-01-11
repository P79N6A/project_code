<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\yyy\UserLoan;
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
class UserHistoryInfo extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_history_info';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_yyy');
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
            'user_id' => '用户ID',
            'user_type' => '用户类型：1大学生；2社会人',
            'data_type' => '数据类型：1学校信息；2公司信息',
            'company_school' => '学校名称或者公司名称',
            'industry_edu' => '行业或学历',
            'position_schooltime' => '职位或入学年份',
            'telephone' => '公司电话',
            'marriage' => '婚姻',
            'area' => '数据类型为2,3时填写',
            'address' => '数据类型为2,3时填写',
            'profession' => '职业',
            'email' => '邮箱',
            'income' => '月收入',
            'create_time' => '添加时间',
        ];
    }

    public function getUserHistoryInfo($where,$loan_create_time)
    {
        //获取用户修改信息时间
        $userHistoryInfo = $this->find()->where($where)->select('create_time')->orderBy('id DESC')->one();
        if (empty($userHistoryInfo)) {
            return 0;
        }
        $edit_time = $userHistoryInfo->create_time;
        //获取修改时间后的借款笔数
        $user_loan = new UserLoan;
        $loan_where = ['and',$where,['>','create_time',$edit_time],['status' => 8]];
        $loan_amount = $user_loan->find()->where($loan_where)->count();
        if ($loan_amount > 0) {
            return 0;
        }
        return 1;
    }
}
