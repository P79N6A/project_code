<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pay_channel".
 *
 * @property integer $id
 * @property string $company_name
 * @property string $product_name
 * @property string $mechart_num
 * @property integer $status
 * @property string $tip
 */
class Channel extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_channel';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_name', 'mechart_num'], 'required'],
            [['status'], 'integer'],
            [['company_name', 'product_name'], 'string', 'max' => 30],
            [['mechart_num'], 'string', 'max' => 100],
            [['tip'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_name' => 'Company Name',
            'product_name' => 'Product Name',
            'mechart_num' => 'Mechart Num',
            'status' => 'Status',
            'tip' => 'Tip',
        ];
    }
    
    public static function getStatus(){
        return [
            0 => '未开通',
            1 => '已启用',
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
    
    public function getChannel($conditions = []){
        $where = [];
        if(!empty($conditions)){
            $where = $conditions;
        }
        return $data = self::find()->where($where)->indexBy('id')->all();
    }

    /**
     * 获取商编号
     * @param $channel_id
     * @return int|mixed
     */
    public function getMechartNum($channel_id)
    {
        if (empty($channel_id)){
            return 0;
        }
        $res = self::find()->where(['id'=>$channel_id])->one();
        return empty($res->mechart_num) ? '' : $res->mechart_num;
    }

    /**
     * 获取通道名称
     * @param $channel_id
     * @return int|mixed
     */
    public function getCompanyName($channel_id)
    {
        if (empty($channel_id)){
            return 0;
        }
        $res = self::find()->where(['id'=>$channel_id])->one();
        //return empty($res) ?  '未知' : $res;
        return empty($res->company_name) ? '未知' : $res->company_name;
    }
}
