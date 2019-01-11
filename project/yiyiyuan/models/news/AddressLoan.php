<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_address_loan".
 *
 * @property string $id
 * @property string $loan_no
 * @property string $address_id
 * @property string $user_id
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class AddressLoan extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_address_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_no', 'address_id', 'user_id', 'last_modify_time', 'create_time'], 'required'],
            [['address_id', 'user_id', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['loan_no'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_no' => 'Loan No',
            'address_id' => 'Address ID',
            'user_id' => 'User ID',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock()
    {
        return "version";
    }

    //新增记录
    public function addRecord($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    //获得一条记录，根据loan_no
    public function getRecordByLoanNo($loanNo)
    {
        if(empty($loanNo)){
            return null;
        }
        return self::find()->where(['loan_no'=>$loanNo])->one();
    }
}
