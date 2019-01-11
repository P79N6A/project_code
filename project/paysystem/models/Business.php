<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pay_business".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $name
 * @property string $business_code
 * @property integer $status
 * @property string $tip
 * @property string $create_time
 */
class Business extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_business';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'name', 'business_code', 'create_time'], 'required'],
            [['aid', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['name', 'business_code'], 'string', 'max' => 50],
            [['tip'], 'string', 'max' => 255],
            [['business_code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'name' => 'Name',
            'business_code' => 'Business Code',
            'status' => 'Status',
            'tip' => 'Tip',
            'create_time' => 'Create Time',
        ];
    }
    
    public static function getStatus(){
        return [
            0 => '未开通',
            1 => '已开通',
            2 => '临时关闭',
        ];
    }
    
    public function createData($data){
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $error = $this->chkAttributes($data);
        if ($error) {
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
    
    public function updateData($data){
        $error = $this->chkAttributes($data);
        if ($error) {
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
    
    public function findByCode($code){
        return $data = self::find()->where(["business_code" => $code])->one();
    }
    
    public function getBusiness($conditions = []){
        $where = [];
        if(!empty($conditions)){
            $where = $conditions;
        }
        return $data = self::find()->where($where)->indexBy('id')->all();
    }
    
    
    
}
