<?php

namespace app\models\policy;

use Yii;

/**
 * This is the model class for table "policy_checkbill".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $policy_number
 * @property string $policy_premium
 * @property string $billDate
 * @property integer $billStatus
 * @property string $create_time
 */
class PolicyCheckbill extends \app\models\BaseModel
{
    const STATUS_SUCCESS = 1;
    const STATUS_FAILURE = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'policy_checkbill';
    }
    public static function getStatus() {
        return [
            static::STATUS_SUCCESS => '对账成功',
            static::STATUS_FAILURE => '对账失败',          
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['billStatus'], 'integer'],
            [['billDate', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['billDate'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'billDate' => 'Bill Date',
            'billStatus' => 'Bill Status',
            'create_time' => 'Create Time',
        ];
    }
    //保存数据
    public function saveData($postData)
    { 
        if (!is_array($postData) || empty($postData)) {
            return false;
        }
        $postData['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(null,implode('|', $error));
        }
        $res = $this->save();
        if (!$res) {
            return $this->returnError(null,implode('|', $this->errors));
        }
        return $res;
    }
    public function getDataByBillDate($billDate){
        $data = static::find()->where(['billDate'=>$billDate])->one();
        return $data;
    }
    public function updateData($where,$data){
        $res = static::updateAll($data,$where);
        return $res;
    }
}