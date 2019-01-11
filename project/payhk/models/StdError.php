<?php

namespace app\models;


use Yii;
use \app\common\Logger;
/**
 * This is the model class for table "pay_std_error".
 *
 * @property integer $id
 * @property integer $channel_id
 * @property string $error_code
 * @property string $error_msg
 * @property string $res_code
 * @property string $res_msg
 * @property string $create_time
 */
class StdError extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_std_error';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['channel_id', 'error_code', 'error_msg', 'res_code', 'res_msg', 'create_time'], 'required'],
            [['channel_id'], 'integer'],
            [['create_time'], 'safe'],
            [['error_code', 'error_msg', 'res_code', 'res_msg'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel_id' => 'Channel ID',
            'error_code' => 'Error Code',
            'error_msg' => 'Error Msg',
            'res_code' => 'Res Code',
            'res_msg' => 'Res Msg',
            'create_time' => 'Create Time',
        ];
    }
    
    public function getChannel(){
        return $this->hasOne(Channel::className(),['id' => 'channel_id']);
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
    
    /*public function getStdError($conditions = []){
        $where = [];
        if(!empty($conditions)){
            $where = $conditions;
        }
        return $data = self::find()->where($where)->all();
    }*/
    /**
     * @param $channel_id 通道id
     * @param $errordata 状态码数组
     */
    public function insertThirdPayerror($channel_id,$errordata){
        if(empty($channel_id)) return false;
        if(empty($errordata)) return false;
        foreach ($errordata as $key=>$value){
            $model = new StdError();
            $model->channel_id = $channel_id;
            $model->error_code = $key;
            $model->error_msg = $value;
            $model->res_code= $this->getRescode($channel_id,true);
            $model->res_msg = $value;
            $model->create_time = date('Y:m:d H:i:s');

            $model->save();
        }
    }

    /**
     * @param $channel_id
     * @param bool $isThird
     */
    public function getRescode($channel_id,$isThird=false){
        $lastCode = self::find()->where(array('channel_id'=>$channel_id))->orderBy('id desc')->one();
        if(!empty($lastCode)){

            $res_code = $lastCode->res_code;
            $channel_len = strlen($channel_id);
            $suffix_code = substr($res_code,$channel_len);
            $new_suffix_code = intval($suffix_code)+1;
            $res_code = $channel_id.$new_suffix_code;
        }else{
            $suffix_code = 1000;
            if($isThird){
                $suffix_code = 5000;
            }
            $res_code = $channel_id.$suffix_code;
        }
        return $res_code;
    }
    public  function getStdErrorInfo($where){
        $data = static::find()->where($where)->one();
        return $data;
    }
    /**
     * @param $channel_id 支付通道
     * @param $error_code 第三方接口返回状态码
     */
    public function getStdError($channel_id,$error_code){
        //暂时通道 106 107 宝付 有数据
        $channelArr = [106,107];
        //查询是否存在缓存文件
        if(empty($channel_id)) return null;
        if(empty($error_code)) return null;
        if(!in_array($channel_id,$channelArr)) return null;
        $filepath = Yii::$app->basePath.'/log/stdError/'.$channel_id.'.php';
        $dir = dirname($filepath);
        if(!file_exists($dir)){
            mkdir($dir,0775);
        }
        if(!file_exists($filepath)){
            $errorArr= self::find()->select('error_code,error_msg,res_code,res_msg')->where(array('channel_id'=>$channel_id))->indexBy('error_code')->asArray()->all();
            file_put_contents($filepath,json_encode($errorArr,JSON_UNESCAPED_UNICODE));
        }else{
            $errorArr = file_get_contents($filepath);
            $errorArr = json_decode($errorArr,true);
        }
        $errorInfo = array();
        if(array_key_exists($error_code,$errorArr)){
            $errorInfo = $errorArr[$error_code];
        }
        return $errorInfo;
    }

    /**
     * @param $channel_id
     * @param $error_code
     * @return mixed 返回第三方标准错误码
     */
    public static function returnThirdStdError($channel_id,$error_code,$error_msg) {
        if(empty($error_code)) return ['res_code'=>'-1','res_data'=>'系统错误'];
        $errorInfo = (new StdError)->getStdError($channel_id,$error_code);
        if(!empty($errorInfo)){
            $res_code = $errorInfo['res_code'];
            $res_data = $errorInfo['error_msg']==$error_msg?$errorInfo['res_msg']:$error_msg;
        }else{
            $res_code = $error_code;
            $res_data = $error_msg;
        }
        return [
            'res_code' => $res_code,
            'res_data' => $res_data,
        ];

    }

    /**
     * @param $channel_id
     * @param $error_code
     * @param string $res_data
     * @return array
     */
    public static function returnStdErrorJson($channel_id,$error_code,$res_data=''){
        if(empty($channel_id)||empty($error_code)) return ['res_code'=>'-1','res_data'=>'系统错误'];
        $filepath = Yii::$app->basePath.'/modules/api/common/stdError.php';
        $errorArr = array();
        if(file_exists($filepath)){
            $errorArr = include $filepath;
        }
        $res_code = $channel_id.$error_code;
        if(!empty($errorArr)){
            if(array_key_exists($error_code,$errorArr)){

                $res_data  = empty($res_data)?$errorArr[$error_code]:$res_data;
            }
        }

        return json_encode([
            'res_code' => $res_code,
            'res_data' => $res_data,
        ],JSON_UNESCAPED_UNICODE);
    }

    /**
     * @param $channel_id
     * @param $error_code
     * @param string $res_data
     * @return array
     */
    public static function returnStdError($channel_id,$error_code,$res_data=''){
        if(empty($channel_id)||empty($error_code)) return ['res_code'=>'-1','res_data'=>'系统错误'];
        $filepath = Yii::$app->basePath.'/modules/api/common/stdError.php';
        $errorArr = array();
        if(file_exists($filepath)){
            $errorArr = include $filepath;
        }
        $res_code = $channel_id.$error_code;
        if(!empty($errorArr)){
            if(array_key_exists($error_code,$errorArr)){

                $res_data  = empty($res_data)?$errorArr[$error_code]:$res_data;
            }
        }

        return [
            'res_code' => $res_code,
            'res_data' => $res_data,
        ];
    }
}
