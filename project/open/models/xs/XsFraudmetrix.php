<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;
/**
 * "xs","xsfraud","save failed", $postData, $errorT;his is the model class for table "{{%fraudmetrix}}".
 *
 * @property string $id
 * @property string $seq_id
 * @property string $basic_id
 * @property string $identity_id
 * @property string $phone
 * @property string $idcard
 * @property string $event
 * @property string $decision
 * @property string $score
 * @property integer $is_black
 * @property integer $is_multi
 * @property string $create_time
 */
class XsFraudmetrix extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_fraudmetrix';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['seq_id', 'identity_id', 'phone', 'idcard', 'event', 'decision', 'score', 'create_time'], 'required'],
            [['basic_id', 'is_black', 'is_multi'], 'integer'],
            [['create_time'], 'safe'],
            [['seq_id'], 'string', 'max' => 64],
            [['identity_id'], 'string', 'max' => 50],
            [['phone', 'idcard', 'event', 'decision'], 'string', 'max' => 20],
            [['score'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'seq_id' => '同盾请求号',
            'basic_id' => '请求表id',
            'identity_id' => '用户唯一标识',
            'phone' => '手机',
            'idcard' => '身份证',
            'event' => '事件',
            'decision' => '同盾决策',
            'score' => '分数',
            'is_black' => '黑名单:0:否; 1:是',
            'is_multi' => '多投:0:否; 1:是',
            'create_time' => '创建时间',
        ];
    }
    /**
     * 获取同盾欺诈结果
     */
    public function getResult($phone,$idcard,$event,$type = 1){
        $datetime = date('Y-m-d H:i:s', strtotime('-3 month'));
        if ($type == 2) {
            $datetime = date('Y-m-d H:i:s', strtotime('-7 day'));
        }
        $where = ['AND',
            [
                'phone' => $phone,
                'idcard' => $idcard,
                'event' => $event,
            ],
            ['>','create_time',$datetime],
        ];
        $data = static::find() -> where($where)->orderBy('create_time DESC')  -> limit(1) ->one();
        return $data;
    }   
    /**
     * 获取一定时间内同盾欺诈结果
     */
    public function getFmData($phone,$idcard,$event,$datetime){
        $where = ['AND',
            [
                'phone' => $phone,
                'idcard' => $idcard,
                'event' => $event,
            ],
            ['>','create_time',$datetime],
        ];
        $data = static::find() -> where($where)->orderBy('create_time DESC')  -> limit(1) ->one();
        return $data;
    }   
    public function saveData($data){ 
        $time = date("Y-m-d H:i:s"); 
        $create_time = isset($data["create_time"]) && !empty($data["create_time"]) ? $data["create_time"] : $time ;
        $postData = [ 
            'seq_id'    =>  $data['seq_id'],
            'basic_id'  =>  (int)$data['basic_id'],
            'identity_id'   =>  $data['identity_id'],
            'phone' =>  $data['phone'],
            'idcard'    =>  $data['idcard'],
            'event' =>  $data['event'],
            'decision'  =>  $data['decision'],
            'score' =>  (string)$data['score'],
            'is_black'  =>  (int)$data['is_black'],
            'is_multi'  =>  (int)$data['is_multi'],
            'create_time'   =>  $create_time,
        ]; 

        $error = $this->chkAttributes($postData); 
        if ($error) { 
            Logger::dayLog("xs","db","XsFraudmetrix/saveData","save failed", $postData, $error);
            return false; 
        } 

        return $this->save(); 
    } 
}
