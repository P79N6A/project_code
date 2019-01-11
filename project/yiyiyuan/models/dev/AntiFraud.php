<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "{{%yi_anti_fraud}}".
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
class AntiFraud extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%yi_anti_fraud}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'loan_id', 'create_time'], 'required'],
            [['user_id', 'loan_id', 'type', 'model_status', 'result_status', 'version'], 'integer'],
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
            'type' => '(预留)类型：1首次借贷类型；2复贷类型；3驳回类型',
            'model_status' => '状态:1待模型处理，2模型处理中，3模型结束，待释放，4释放中，5完成',
            'result_status' => '结果状态:0:初始; 1:欺诈; 2:安全',
            'result_subject' => '结果建议: 期诈; 安全',
            'result_time' => '结果时间',
            'modify_time' => '最后修改时间',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 乐观所版本号
     * **/
    public function optimisticLock() {
        return "version";
    }

    /**
     * 保存数据
     * @param [] $data 保存的数据
     */
    /**
     * 推送一个用户到反欺诈中
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
            'version' => 0
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
                'type' => 0,
                'model_status' => 1, 
                'result_status' =>0,
                'result_subject' => '',
                'result_time' => '0000-00-00',
                'modify_time' => $time,
                'create_time' =>  $time,
                'version' => 0
            ];
        }

        return static::insertBatch($saves);
    }
    /**
     * 获取需要处理的初始数据
     * @param date $result_time 当前时间
     * @return bool
     */
    public function getInit($result_time){
        $where = [
            'AND',
            ['model_status' => 3],
            ['result_status' => 0], // 初始用户
            ['<', 'result_time', $result_time],
            ['>', 'result_time', date('Y-m-d H:i:s', strtotime($result_time) - 18000) ], // 5小时内
        ];
        $data = static::find() -> where($where) -> orderBy('result_time ASC') -> limit(1000) -> all();
        return $data;
    }
    /**
     * 获取需要处理的初始数据---New
     * @param $create_time
     * @return bool
     */
    public function getNewInit($create_time){
        $where = [
            'AND',
            ['model_status' => 1],
            ['result_status' => 0], // 初始用户
            ['<', 'create_time', $create_time],
            ['>', 'create_time', date('Y-m-d H:i:s', strtotime($create_time) - 18000) ], // 5小时内
        ];
        $data = static::find() -> where($where) -> orderBy('create_time ASC') -> limit(1000) -> all();
        return $data;
    }
    /**
     * 保存init初始数据为锁定
     * @return  bool
     */
    public function lockInit() {
        try {
            $this->model_status = 6;
            $this->modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
    /**
     * 保存init初始数据为锁定 -- 6
     * @return  bool
     */
    public function newLockInit() {
        try {
            $this->model_status = 6;
            $this->modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 保存发送成功
     * @return  bool
     */
    public function NewSendSucc() {
        try {
            $this->model_status = 7;
            $this->modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 通过ID查询一条数据
     * @param $id
     * @return null|static
     */
    public function getFraudById($id){
        $id = intval($id);
        if (!$id) {
            return null;
        }
        return static::findOne($id);
    }

    /**
     * 修改欺诈结果
     * @param $resultStatus
     * @param $result_subject
     * @return bool
     */
    public function updateFraudResult($resultStatus, $result_subject){
        if(empty($resultStatus)){
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = [
            'model_status'=> 3,
            'result_status' => $resultStatus,
            'result_time' => $time,
            'modify_time' => $time,
            'result_subject' => $result_subject,
        ];
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }
    /**
     * 获取需要处理的欺诈数据
     * @param date $result_time 最大时间
     * @return bool
     */
    public function getFraud($result_time){
        $where = [
            'AND', 
            ['model_status' => 3],
            ['result_status' => 1], // 欺诈用户
            ['<', 'result_time', $result_time], 
            ['>', 'result_time', date('Y-m-d H:i:s', strtotime($result_time) - 86400) ],  // 24小时, 是为了减少范围
        ];
        $data = static::find() -> where($where) -> orderBy('result_time ASC') -> limit(1000) -> all();
        return $data;
    }
    /**
     * 获取需要处理的正常数据
     * @param date $result_time 当前时间
     * @return bool
     */
    public function getNormal($result_time){
        $where = [
            'AND', 
            ['model_status' => 3],
            ['result_status' => 2], // 正常用户
            ['<', 'result_time', $result_time], 
            ['>', 'result_time', date('Y-m-d H:i:s', strtotime($result_time) - 86400) ], // 24小时内
        ];
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
     * 字段映射关系
     * @return [type] [description]
     */
    public function varNames(){
        return [
            'contract_exists' => '联系人不存在于通话,通讯中',
            'addr_count' => '通讯录个数不足',
            'vs_phone_match' => '通讯,通话匹配个数不足',
            'report_use_time' => '手机号码注册时间较短',
            'report_aomen' => '出现澳门通话记录',
            'report_court' => '申请人出现在法院黑名单',
            'report_fcblack' => '申请人出现在金融服务类机构黑名单',
            'report_shutdown' => '客户关机时间较长(形如关机共7天) ',


            'com_r_rank' => '亲属联系人在聚信立中的通话排名较低', 
            'com_c_total' => '常用联系人在聚信立中的通话排名较低', 
            'addr_has_black' => '通讯录含黑名单个数', 
            #'addr_has_overdue' => '通讯录含逾期用户', 
            #'com_has_black' => '通话中含有黑名单', 
            #'com_has_overdue' => '通话中含有黑名单', 
            'report_night_percent' => '聚信立中统计的夜间通话占比', 
            'report_loan_connect' => '贷款类号码联系情况', 
            #'com_local_percent' => '聚信立中本地通话占比', 
  
            'report_110'=>'与110是否有通话记录',
            'report_120'=>'与120是否有通话记录',
            'report_lawyer'=>'与律师有通话记录',
            'report_court'=>'与法院有通话记录',
            'com_hours_connect'=>'过去90天通话时段数' ,
            'com_c_total_mavg' =>'社会人月均通话次数',
            'com_r_total_mavg' => '亲属月均通话次数',

            'com_valid_all' => '通话次数大于15次',
            'com_valid_mobile' => '有效联系人个数',
            'vs_valid_match' => '有效手机号与通讯录匹配数',
        ];
    }
}
