<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\yyy\UserLoan;

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
 * @property integer $source
 */
class Juxinli extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_juxinli';
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
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'user_id' => '查询用户id',
            'requestid' => '请求id',
            'process_code' => '进程码',
            'status' => '状态',
            'response_type' => '响应结果',
            'type' => '1:聚信力，2:京东',
            'user_name' => '京东',
            'password' => '密码',
            'last_modify_time' => '最后修改时间',
            'create_time' => '创建时间',
            'source' => '1、2:聚信立 3:融360 4:上数',
        ];
    }

    public function getJuxinli($where)
    {
        
        $j_where = ['and',['type' => 1],$where];
        $juxinli_info = $this->find()->where($j_where)->select('last_modify_time')->orderBy('id DESC')->one();

        if (empty($juxinli_info)) {
            return 0;
        }
        $upTime = $juxinli_info->last_modify_time;
        $user_loan = new UserLoan;
        $loan_where = ['and',$where,['>','create_time',$upTime],['status' => 8]];
        $loan_amount = $user_loan->find()->where($loan_where)->count();
        if ($loan_amount > 0) {
            return 0;
        }
        return 1;
    }
}
