<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\yyy\UserLoan;

/**
 * This is the model class for table "yi_user_quota_record".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property integer $method
 * @property string $old_quota
 * @property string $new_quota
 * @property string $desc
 * @property string $last_modify_time
 * @property string $create_time
 */
class UserQuotaRecord extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_quota_record';
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
            [['user_id', 'type', 'method', 'desc', 'last_modify_time', 'create_time'], 'required'],
            [['user_id', 'type', 'method'], 'integer'],
            [['old_quota', 'new_quota'], 'number'],
            [['desc'], 'string'],
            [['last_modify_time', 'create_time'], 'safe']
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
            'type' => '额度变化类型1：提额；2：降额',
            'method' => '1：借款完成（系统提额）；2提额券；3:手动提额',
            'old_quota' => '变更前额度',
            'new_quota' => '变更后额度',
            'desc' => '提额的原因，如两次成功借款',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
        ];
    }

    public function getAmountInfo($where,$userLoaninfo)
    {
        $a_where = ['and',['type' => 1],$where];
        $amountInfo = $this->find()->where($a_where)->select('new_quota,last_modify_time')->orderBy('id DESC')->one();
        if (empty($amountInfo)) {
            return 0;
        }
        //最近一次提额时间
        $upTime = $amountInfo->last_modify_time;
        $user_loan = new UserLoan;
        $loan_where = ['and',$where,['>','create_time',$upTime],['status' => 8]];
        $loan_amount = $user_loan->find()->where($loan_where)->count();
        if ($loan_amount > 0) {
            return 0;
        }
        //本次借款金额
        $amount = $userLoaninfo['amount'];
        //变更后额度
        $new_quota = $amountInfo->new_quota;
        if ($amount < $new_quota) {
            return 0;
        }
        return 1;
    }
}
