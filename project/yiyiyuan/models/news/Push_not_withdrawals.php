<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_push_not_depository".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $type
 * @property integer $loan_status
 * @property integer $notify_num
 * @property integer $notify_status
 * @property string $notify_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Push_not_withdrawals extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_push_not_withdrawals';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'notify_time', 'last_modify_time', 'create_time', 'version'], 'required'],
            [['user_id', 'loan_id', 'type', 'loan_status', 'notify_num', 'notify_status', 'version'], 'integer'],
            [['notify_time', 'last_modify_time', 'create_time'], 'safe']
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
            'loan_id' => 'Loan ID',
            'type' => 'Type',
            'loan_status' => 'Loan Status',
            'notify_num' => 'Notify Num',
            'notify_status' => 'Notify Status',
            'notify_time' => 'Notify Time',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
    
    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getByLoanId($loan_id){
        $loan_id = intval($loan_id);
        if(!$loan_id){
            return null;
        }
        $data = self::find()->where(['loan_id'=>$loan_id])->one();
        return $data;
    }

}
