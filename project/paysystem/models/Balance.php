<?php

namespace app\models;
use app\common\Logger;
use Yii;

/**
 * This is the model class for table "balance".
 *
 * @property string $id
 * @property string $cp_name
 * @property integer $type
 * @property string $account_name
 * @property double $amt_balance
 * @property double $ser_balance
 * @property string $create_time
 */
class Balance extends \app\models\BaseModel{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'balance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cp_name', 'account_name'], 'required'],
            [['type','aid','cid'], 'integer'],
            [['amt_balance', 'ser_balance'], 'number'],
            [['create_time','balance_time'], 'safe'],
            [['cp_name'], 'string', 'max' => 30],
            [['account_name'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'cp_name' => 'Cp Name',
            'type' => 'Type',
            'aid' => 'Aid',
            'cid' => 'Cid',
            'account_name' => 'Account Name',
            'amt_balance' => 'Amt Balance',
            'ser_balance' => 'Ser Balance',
            'create_time' => 'Create Time',
        ];
    }
    public function addBalance($postdata){
        if(empty($postdata)) return false;
        $error = $this->chkAttributes($postdata);
        if ($error) {
            Logger::dayLog('balance/addBalance','保存余额失败',$postdata,$error);
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }
} 