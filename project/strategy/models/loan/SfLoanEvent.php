<?php

namespace app\models\loan;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "loan_event".
 *
 * @property string $id
 * @property string $request_id
 * @property string $loan_id
 * @property string $user_id
 * @property integer $aid
 * @property integer $result
 * @property string $message
 * @property string $response
 * @property integer $status
 * @property string $execution_time
 * @property string $modify_time
 * @property string $create_time
 * @property integer $version
 */
class SfLoanEvent extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'loan_event';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_loan');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'user_id', 'aid', 'result', 'status', 'version'], 'integer'],
            [['execution_time', 'modify_time', 'create_time'], 'safe'],
            [['request_id', 'message'], 'string', 'max' => 64],
            [['response'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键，自增长',
            'request_id' => '请求ID',
            'loan_id' => '借款ID',
            'user_id' => '用户ID',
            'aid' => '来源',
            'result' => '决策结果',
            'message' => '决策结果信息',
            'response' => '设备号',
            'status' => '状态 1  初始 2 请求中 3 请求已返回结果',
            'execution_time' => '执行时间',
            'modify_time' => '最后更新时间',
            'create_time' => '创建时间',
            'version' => '版本号',
        ];
    }

    public function frejectNum($data)
    {
        $loan_id = ArrayHelper::getValue($data,'loan_id');
        $where = ['and',['loan_id'=> $loan_id],['!=','result','0']];
        $freject_num = $this->find()->where($where)->count();
        return (int)$freject_num;
    }   
}
