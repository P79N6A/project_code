<?php
/**
 * 聚合结果表 [目前存储数据魔盒及数立的数据抓取结果]
 */
namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;

class GatherResult extends BaseModel{

    const SOURCE_XUEXIN = 1; // 学信
    const STATUS_SHEBAO = 2; // 社保
    const STATUS_GONGJIJIN = 3; // 公积金
    const STATUS_JINGDONG = 4; // 京东电商
    const STATUS_SHULIBANK = 5;  // 数立银行流水

    public static function tableName(){
        return 'xhh_gather_result';
    }

    public function rules(){
        return [
            [['request_id', 'aid', 'data_url','create_time','user_id','source'], 'required'],
            [['create_time'], 'safe'],
            [['request_id', 'user_id','aid'], 'integer'],
            [['mobile'], 'string', 'max' => 20],
            [['data_url'], 'string', 'max' => 200],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => '自增Id',
            'aid' => '请求应用Id',
            'source' => '数据来源 1[学信] 2[社保] 3[公积金] 4[京东电商] 5[数立银行流水]',
            'request_id' => '请求Id',
            'user_id' => '用户Id',
            'mobile' => '用户手机号',
            'data_url' => '数据存储路径',
            'create_time' => '数据存储时间',
        ];
    }

    /**
     * 保存数据
     * @param array $postdata
     * @return bool
     */
    public function saveData($postdata){
        if (!$postdata) {
            return false;
        }
        $nowTime = date('Y-m-d H:i:s');
        $resultObj = $this->getOne(ArrayHelper::getValue($postdata,'request_id'),'request_id');
        if(!empty($resultObj)){
            $resultObj->create_time = $nowTime;
            $resultObj->user_id = ArrayHelper::getValue($postdata,'user_id');
            $resultObj->data_url = ArrayHelper::getValue($postdata,'data_url');
            $error = $resultObj->chkAttributes($postdata);
            if ($error) {
                return $this->returnError(false, $error);
            }
            return $resultObj->update();
        }else{
            $postdata['create_time'] = $nowTime;
            $error = $this->chkAttributes($postdata);
            if ($error) {
                return $this->returnError(false, $error);
            }
            return $this->save();
        }
    }


    /**
     * 根据指定字段及对应值查询单条语句
     * @param mix $id 字段对应值
     * @param string $column 字段名 默认id
     */
    public function getOne($id,$column='id'){
        if(empty($id)){
            return false;
        }
        $result = self::find()->where(['=' , $column , $id] )->one();
        return $result;
    }

    /**
     *  根据用户id aid  和source来获取最新的认证信息
     * @param $user_id
     * @param $aid
     * @param int $source
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getDataList($user_id,$aid,$source=0){
        if(empty($user_id) || empty($aid)){
            return false;
        }
        $result = self::find()->where(['user_id'=>$user_id,'aid'=>$aid,'source'=>$source] )->orderBy('create_time DESC')->one();
        if(empty($result)){
            return null;
        }
        return $result->attributes;
    }
}