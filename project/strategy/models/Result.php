<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "st_result".
 *
 * @property string $id
 * @property string $request_id
 * @property integer $from
 * @property string $res_info
 * @property string $create_time
 * @property string $modify_time
 */
class Result extends BaseModel
{
    const STATUS_APPROVAL = 1; // 安全
    const STATUS_MANUAL = 2; // 人工
    const STATUS_REJECT = 3; // 驳回
    /**
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return 'st_result'; 
    } 

    public function rules() 
    { 
        return [
            [['request_id', 'res_status', 'from', 'create_time'], 'required'],
            [['request_id', 'loan_id', 'user_id', 'res_status', 'from', 'prd_type'], 'integer'],
            [['res_info'], 'string'],
            [['create_time'], 'safe'],
            [['loan_no'], 'string', 'max' => 64]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => '主键',
            'request_id' => '请求ID，唯一',
            'loan_id' => '借款ID',
            'loan_no' => '借款编码',
            'user_id' => '用户ID',
            'res_status' => '决策结果状态',
            'from' => '决策请求来源',
            'res_info' => '决策返回结果',
            'create_time' => '创建时间',
            'prd_type' => '产品类型，1 一亿元；8 7-14天',
        ]; 
    } 

    public function addResInfo($postData)
    { 
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function getOneHour($user_id)
    {
        $time = date('Y-m-d H:i:s',strtotime('-30 minute'));
        $where = ['and',['user_id'=>$user_id],['>=','create_time',$time],['from'=>2]];
        return $this->find()->where($where)->select('res_status,id')->orderBy('id DESC')->one();
    }

    public function saveRes($postdata, $result)
    {
        $ret_info = json_encode($result, JSON_UNESCAPED_UNICODE);
        $postData = [
            'request_id' => isset($postdata['request_id']) ? $postdata['request_id'] : 0,
            'loan_id' => isset($postdata['loan_id']) ? $postdata['loan_id'] : 0,
            'loan_no' => isset($postdata['loan_no']) ? $postdata['loan_no'] : '',
            'user_id' => isset($postdata['identity_id']) ? $postdata['identity_id'] : $postdata['user_id'],
            'res_status' => isset($result['RESULT']) ? $result['RESULT'] : $result['LOAN_RESULT'],
            'from' => isset($postdata['from']) ? $postdata['from'] : 0,
            'res_info' => $ret_info,
            'prd_type' => isset($postdata['aid']) ? $postdata['aid'] : 1,
        ];
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(false, $error);
        }
        return $this->save();
    }

    public function getRes($request_id)
    {
        $where = ['request_id'=>$request_id];
        $res = $this->find()->where($where)->limit(1)->select('res_status')->one();
        if (empty($res)) {
            return false;
        }
        return $res->res_status;
    }

    public function getResData($request_id)
    {
        $where = ['request_id'=>$request_id];
        $res = $this->find()->where($where)->orderBy('id DESC')->one();
        if (empty($res)) {
            return false;
        }
        return $res;
    }

    public function getResDataHappy($request_id)
    {
        $where = ['request_id'=>$request_id, 'prd_type' => 16];
        $res = $this->find()->where($where)->orderBy('id DESC')->one();
        if (empty($res)) {
            return false;
        }
        return $res;
    }

    public function getOne($where)
    {
        return $this->find()->where($where)->asArray()->orderBy('id DESC')->one();
    }

    public function getResultiData($where,$select = '*')
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
