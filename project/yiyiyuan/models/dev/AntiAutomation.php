<?php

namespace app\models\dev;

use app\models\news\Do_ious;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%yi_anti_automation}}".
 *
 * @property string $id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $type
 * @property integer $model_status
 * @property integer $result_status
 * @property string $result_subject
 * @property string $result_time
 * @property string $modify_time
 * @property string $create_time
 */
class AntiAutomation extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%yi_anti_automation}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'type', 'model_status', 'result_status'], 'integer'],
            [['result_subject'], 'string'],
            [['result_time', 'modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键，递增',
            'user_id' => '用户ID',
            'loan_id' => '借款id',
            'type' => '类型：1首次借贷类型；2复贷类型；',
            'model_status' => '状态:1待模型处理，2模型处理中，3模型结束，待释放，4释放中，5完成',
            'result_status' => '结果状态:0:初始; 1:通过; 2:未通过',
            'result_subject' => '结果建议: 各参数结果',
            'result_time' => '结果时间',
            'modify_time' => '最后修改时间',
            'create_time' => '创建时间',
        ];
    }
    /**
     * 保存数据
     * @param [] $data 保存的数据
     */
    /**
     * 推送一个用户到自动化中
     * @param int  $user_id 
     * @param int $loan_id 借款id
     * @return bool
     */
    public function addByUser($user_id, $loan_id=0){
        if( !is_array($data) || empty($data) ){
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $newData = [
            'user_id' => $user_id,
            'loan_id' => $loan_id, 
            'type' => 0,
            'model_status' => 1, 
            'result_status' =>0,
            'result_subject' => '',
            'result_time' => '0000-00-00',
            'modify_time' => $time,
            'create_time' =>  $time,
        ];
        $error = $this->chkAttributes($newData);
        if($error){
            return false;
        }
        return $this->save();
    }
    /**
     * 批量添加 
     * @param [type] $userData [description]
     */
    public static function addBatchByUsers($userData){
        if(empty($userData)){
            return 0;
        }
        $time = date('Y-m-d H:i:s');
        $saves = [];
        foreach ($userData as $data) {
            $saves[] = [
                'user_id' => $data['user_id'],
                'loan_id' => $data['loan_id'],
                'type' => $data['type'],
                'model_status' => 1, 
                'result_status' =>0,
                'result_subject' => '',
                'result_time' => '0000-00-00',
                'modify_time' => $time,
                'create_time' =>  $time,
            ];
        }

        return static::insertBatch($saves);
    }
    /**
     * 获取需要处理的未通过数据
     * @param date $result_time 最大时间
     * @return bool
     */
    public function getRelease($result_time,$type=0){
        $where = [
            'AND', 
            ['model_status' => 3],
            ['result_status' => 2], // 未通过用户
            ['<', 'result_time', $result_time], 
            ['>', 'result_time', date('Y-m-d H:i:s', strtotime($result_time) - 36000) ],  // 此句无意义, 是为了减少范围
        ];
        if( $type ){
            $where[] = ['type'=>$type];
        }
        $data = static::find() -> where($where) -> orderBy('result_time ASC') -> limit(1000) -> all();
        $this->saveDoIous($data);
        return $data;
    }
    /**
     * 获取需要处理的正常数据
     * @param date $result_time 当前时间
     * @return bool
     */
    public function getNormal($result_time,$type=0){
        $where = [
            'AND', 
            ['model_status' => 3],
            ['result_status' => 1], // 正常用户
            ['<', 'result_time', $result_time], 
            ['>', 'result_time', date('Y-m-d H:i:s', strtotime($result_time) - 36000) ], // 1小时内
        ];
        if( $type ){
            $where[] = ['type'=>$type];
        }
        $data = static::find() -> where($where) -> orderBy('result_time ASC') -> limit(1000) -> all();
        $this->saveDoIous($data);
        return $data;
    }
    /**
     * 获取驳回数据
     * @param date $result_time 最大时间
     * @return bool
     */
    public function getReject($result_time,$type=0){
        $where = [
            'AND', 
            ['model_status' => 3],
            ['result_status' => [3,4]], // 驳回用户
            ['<', 'result_time', $result_time],
            ['>', 'result_time', date('Y-m-d H:i:s', strtotime($result_time) - 36000) ],  // 此句无意义, 是为了减少范围
        ];
        if( $type ){
            $where[] = ['type'=>$type];
        }
        $data = static::find() -> where($where) -> orderBy('result_time ASC') -> limit(1000) -> all();
        return $data;
    }
    /**
     * 获取某个loan_id的反欺诈分析结果
     * @param  [type] $loan_id [description]
     * @return [type]          [description]
     */
    public function getResult($loan_id){
        $row = static::find() -> where(['loan_id'=>$loan_id]) -> limit(1) ->orderBy('id DESC') ->asArray() -> one();
        if( empty($row) ){
            return null;
        }
        $result_subject = $row['result_subject'];
        $result_subject = json_decode( $result_subject , true );
        $row['subject'] = $result_subject;
        return $row;
    }

    /**
     * 保存用户白条信息
     * @param $data
     * @return bool|null|number
     */
    public function saveDoIous($data){
        try {
            if(empty($data) || !is_array($data)){
                return false;
            }
            $user_ids = ArrayHelper::getColumn($data, 'user_id');
            $doIousModel = new Do_ious();
            $info = $doIousModel->getDoiousByUserID($user_ids);
            $result = ArrayHelper::index($info, 'user_id');
            $ious_user_ids = ArrayHelper::getColumn($info, 'user_id');
            foreach ($data as $key => $val){
                $result_subject = json_decode($val->result_subject,true);
                if(!empty($ious_user_ids) && in_array($val->user_id,$ious_user_ids)){
                    $result[$val->user_id]->updateIousStatus($result_subject);
                    continue;
                }
                $nowTime = date('Y-m-d H:i:s');
                $condition[] = [
                    'user_id' => $val->user_id,
                    'ious_status' => $result_subject['ious_status'],
                    'ious_days' => $result_subject['ious_days'],
                    'last_modify_time' => $nowTime,
                    'create_time' => $nowTime,
                    'version' => 0
                ];
            }
            $res = null;
            if (!empty($condition)) {
                $res = $doIousModel->insertBatch($condition);
            }
        } catch (\Exception $e) {
            $res = false;
        }
        return $res;
    }
}
