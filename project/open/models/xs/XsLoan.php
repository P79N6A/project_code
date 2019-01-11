<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%loan}}".
 *
 * @property string $id
 * @property string $basic_id
 * @property string $identity_id
 * @property string $loan_id
 * @property string $amount
 * @property integer $loan_days
 * @property string $cardno
 * @property string $reason
 * @property string $loan_time
 * @property string $create_time
 */
class XsLoan extends \app\models\repo\CloudBase {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'dc_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['basic_id', 'aid', 'loan_days'], 'integer'],
            [['identity_id', 'loan_time', 'create_time'], 'required'],
            [['amount'], 'number'],
            [['loan_time', 'create_time'], 'safe'],
            [['identity_id', 'cardno'], 'string', 'max' => 50],
            [['loan_id'], 'string', 'max' => 100],
            [['reason'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'basic_id' => '请求表id',
            'aid' => '应用aid',
            'identity_id' => '用户唯一标识',
            'loan_id' => '业务借款id',
            'amount' => '借款金额',
            'loan_days' => '借款期限(天)',
            'cardno' => '银行卡号',
            'reason' => '借款原因',
            'loan_time' => '借款时间',
            'create_time' => '创建时间',
        ];
    }
    /*
     * 保存借款数据
     */
    public function saveData($data) {
        $time = date("Y-m-d H:i:s");
        $postData = [
            'basic_id' => $data['basic_id'],
            'identity_id' => $data['identity_id'],
            'aid' => $data['aid'],
            'loan_id' => isset($data['loan_id']) ? (string)$data['loan_id'] : '',
            'amount' => !empty($data['amount'])? $data['amount'] : 0 ,
            'loan_days' => !empty($data['loan_days'])? $data['loan_days'] : 0 ,
            'cardno' => isset($data['cardno']) ? $data['cardno'] : '',
            'reason' => $data['reason'],
            'loan_time' => !empty($data['loan_time']) ? $data['loan_time'] : '0000-00-00 00:00:00',
            'create_time' => $time,
        ];
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }

        return $this->save();
    }
    /**
     * 获取高频借款
     * @param  str $identity_id 
     * @param  int $aid         
     * @param  str $start_time  
     * @return  num
     */
    public function  getMultiLoan($identity_id, $aid, $start_time){
        $where = [
            'AND', 
            ['identity_id' => $identity_id,   'aid' => $aid, ],
            ['>','create_time', $start_time],
        ];

        return static::find() -> where($where) ->limit(1) -> count();
    }
}
