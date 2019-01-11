<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_loan_mapping".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $order_id
 * @property integer $source
 * @property string $callbackurl
 * @property string $create_time
 */
class Loan_mapping extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_loan_mapping';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'source'], 'integer'],
            [['create_time'], 'safe'],
            [['order_id'], 'string', 'max' => 20],
            [['callbackurl'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'order_id' => 'Order ID',
            'source' => 'Source',
            'callbackurl' => 'Callbackurl',
            'create_time' => 'Create Time',
        ];
    }
    
    /*
     * 添加映射信息
     */
    public function addLoanMapping($condition){
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');

        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        if (!$result) {
            return false;
        }
        $mapping_id = Yii::$app->db->getLastInsertID();
        return $mapping_id;
    }

    /*
     * 更新
     */
    public function updateLoanMapping($condition){
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
    
    /**
     * 获取最近一条映射记录
     * @param $user_id
     * @return array|bool|static
     */
    public function newestLoanmapping($loan_id)
    {
        if (empty($loan_id)) return false;
        $loanmapping_info = self::find()->where(['loan_id'=>$loan_id])->orderBy(['create_time'=>SORT_DESC])->one();
        if (!empty($loanmapping_info)){
            return $loanmapping_info;
        }
        return array();
    }
}
