<?php

namespace app\models\cloud;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "dc_origin".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $aid
 * @property string $name
 * @property string $phone
 * @property string $idcard
 * @property integer $code
 * @property string $message
 * @property string $credit_score
 * @property string $model_score_v2
 * @property string $is_black
 * @property string $modify_time
 * @property string $create_time
 */
class DcOrigin extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_origin';
    }

    /**
     * @inheritdoc
     */
    public function rules() 
    { 
        return [
            [['loan_id', 'user_id', 'aid', 'name', 'phone', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['loan_id', 'user_id', 'aid', 'credit_score', 'model_score_v2', 'is_black'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['name', 'phone', 'idcard'], 'string', 'max' => 20],
            [['code'], 'string', 'max' => 10],
            [['message'], 'string', 'max' => 64]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'loan_id' => '用户业务端借款ID',
            'user_id' => '业务端用户唯一标识',
            'aid' => '请求来源：1 一亿元；8 7-14',
            'name' => '用户真实姓名',
            'phone' => '手机',
            'idcard' => '身份证',
            'code' => '天启请求返回码',
            'message' => '天启请求返回信息',
            'credit_score' => '天启信用分',
            'model_score_v2' => '天启模型分V2',
            'is_black' => '是否命中黑名单，0:未命中，1:命中',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ]; 
    } 

    /**
     * 获取天启结果
     */
    public function getResult($data)
    {
        //查询永久数据
        // $datetime = date('Y-m-d H:i:s', strtotime('-3 month'));
        $where = ['AND',
            [
                'phone' => $data['phone'],
                'idcard' => $data['idcard'],
                'code' => 'R0000',
            ],
            // ['>','create_time',$datetime],
        ];
        $data = static::find() -> where($where)->orderBy('id DESC')->asArray()->limit(1)->one();
        return $data;
    }   
    /**
     * 记录天启请求
     */
    public function saveData($data)
    {
        $time = date("Y-m-d H:i:s"); 
        $postData = [ 
            'user_id' => (int)$data['user_id'],
            'loan_id' => (int)$data['loan_id'],
            'aid' => (int)$data['aid'],
            'name' => (string)$data['name'],
            'phone' => (string)$data['phone'],
            'idcard' =>  (string)$data['idcard'],
            'modify_time' => $time,
            'create_time' => $time,
        ]; 
        $error = $this->chkAttributes($postData); 
        if ($error) { 
            Logger::dayLog("org","db","DcOrigin/saveData","save failed", $postData, $error);
            return false; 
        }
        return $this->save(); 
    }

    /**
     * 更新天启请求
     */
    public function updateOrgInfo($origin_result)
    {
        $this->message = $origin_result['message'];
        $this->code = $origin_result['code'];
        $this->credit_score = $origin_result['credit_score'];
        $this->model_score_v2 = $origin_result['model_score_v2'];
        $this->is_black = $origin_result['is_black'];
        $this->modify_time = date("Y-m-d H:i:s");
        return $this->save();
    }

    public function getOriginInfo($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where(['idcard'=>$where])->Asarray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }

    public function getOriginData($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
}
