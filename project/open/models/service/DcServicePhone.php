<?php

namespace app\models\service;

use Yii;
use app\common\Logger;
/**
 * This is the model class for table "dc_service_phone".
 *
 * @property string $id
 * @property string $phone
 * @property string $service_id
 * @property string $create_time
 * @property string $last_query_time
 * @property integer $is_black
 */
class DcServicePhone extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_service_phone';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'service_id', 'create_time', 'last_query_time'], 'required'],
            [['create_time', 'last_query_time'], 'safe'],
            [['is_black'], 'integer'],
            [['phone', 'service_id'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '请求手机号',
            'service_id' => '请求方服务ID',
            'create_time' => '创建时间',
            'last_query_time' => '最近一次请求时间',
            'is_black' => '是否命中黑名单 1 命中; 0 未命中； ',
        ];
    }
    /**
     * 根据手机号获取
     * @param  str $phone 
     * @return obj
     */
    public function getByPhone($phone){
        $where = ['phone'=>$phone];
        return static::find()->where($where)->limit(1)->one();
    }
    /**
     * 根据手机号获取
     * @param  str $phone 
     * @return obj
     */
    public function getCount($where){
        return static::find()->where($where)->count();
    }
    /**
     * 根据手机号获取
     * @param  str $phone 
     * @return obj
     */
    public function getByPhoneList($phone_list){
        $where = ['in','phone',$phone_list];
        return static::find()->where($where)->limit(100)->all();
    }
    
    public function savePhone($data){
        //1. 字段验证
        $time = date("Y-m-d H:i:s");
        $phone = isset($data['phone']) ? $data['phone'] : '';
        $is_black = isset($data['is_black']) ? $data['is_black'] : 0;
        $service_id = isset($data['service_id']) ? $data['service_id'] : '';
        if(!$phone){
            return false;
        }

        //2. 更新还是添加
        $model = $this->getByPhone($phone);
        if (!$model) {
            $model = new self;
            $postData['phone'] =  $phone;
            $postData['create_time'] =  $time;
        }
        $postData['service_id'] = $service_id;
        $postData['last_query_time'] = $time;
        $postData['is_black'] = $is_black;
        //3. 保存数据
        $error = $model->chkAttributes($postData);
        if ($error) {
            Logger::dayLog('DcServicePhone/chkAttributes', $error);
            return false;
        }
        return $model->save();
    }

    public function savePhoneBatch($insert_list) {
        if (empty($insert_list)) {
            return 0;
        }
        $res = Yii::$app->db_cloudnew->createCommand()->batchInsert(
            'dc_service_phone',
            ['service_id','last_query_time','create_time','is_black','phone'],//字段
            $insert_list  
        );
        // $sql = $res->getRawSql();
        // var_dump($sql);die;
        // Logger::dayLog('sql','add_all',$sql);
        $res = $res->execute(); 
        return $res;
    }
}
