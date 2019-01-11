<?php

namespace app\models\xn;

class XnAgreement extends \app\models\BaseModel {

    const STATUS_INIT = 0; // 初始
    const STATUS_LOCK = 3; // 锁定状态
    const STATUS_SUCCESS = 6; // 成功
   
    public function init(){
        
    }
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'xn_agreement';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bid_no', 'createtime'], 'required'],
            [['createtime'], 'safe'],
            [['code', 'status'], 'integer'],
            [['loan_url', 'consulting_url', 'entrustment_url'], 'string', 'max' => 500],
            [['bid_no'], 'string', 'max' => 50],
            [['msg'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_url' => 'Loan Url',
            'consulting_url' => 'Consulting Url',
            'entrustment_url' => 'Entrustment Url',
            'bid_no' => 'Bid No',
            'createtime' => 'Createtime',
            'code' => 'Code',
            'msg' => 'Msg',
            'status' => 'Status',
        ];
    }

    /**
     * 保存数据到db库中
     * @param $data
     * @return bool
     */
    public function saveData($bidNum, $data) {
        if (!$bidNum) {
            return false;
        }
        $create_time = date('Y-m-d H:i:s');

        $notifyObj = $this ->getAgreementByBid($bidNum);
        if($notifyObj){
            $data['createtime'] = $create_time;
            $res = static::updateAll($data, ['id' => $notifyObj['id']]);
        }else{
            $data['createtime'] =  $create_time;
            $data['bid_no'] = $bidNum;
            $error = $this->chkAttributes($data);
            if ($error) {
                return $this->returnError(false, current($error));
            }
            $res = $this->save();
            
        }
        return $res;
    }
 
   
    public function getAgreementByBid($bid)
    {
        if(!$bid)
        {
            return false;
        }
        return static::find()->where(['bid_no' => $bid])->limit(1)->asArray()->one();

    }

    public function getDownList($limit=50)
    {
        $where = [
            'AND', 
            ['status' => static::STATUS_INIT],
            ['>', 'createtime', date('Y-m-d H:i:00', strtotime('-7 days'))],
            ['<', 'createtime', date('Y-m-d H:i:00')]
        ];
        $req = static::find()->where($where)->limit($limit)->all();
        return $req;
    }

    /**
     * 锁定下载请求接口的状态
     */
    public function lockDown($ids) {
        if (!is_array($ids) || empty($ids)) 
        {
            return 0;
        }
        $ups = static::updateAll(['status' => static::STATUS_LOCK], ['id' => $ids]);
        return $ups;
    }
    //下载更新状态
    public function updateStatus($id) {
        if(!$id)
        {
            return false;
        }
        
        $result = static::updateAll(['status' => static::STATUS_SUCCESS], ['id' => $id]);
        return $result;
    }
    /**
     * Undocumented function
     * 保存下载状态
     * @return void
     */
    public function saveDownSuccess(){
        $this->status = static::STATUS_SUCCESS;
        return $this->save();
    }
    /**
     * Undocumented function
     * 保存下载状态
     * @return void
     */
    public function saveDownInit(){
        $this->status = static::STATUS_INIT;
        return $this->save();
    }
    /**
     * Undocumented function
     * 从新拉取下载协议地址
     * @param integer $limit
     * @return void
     */
    public function getAgreeList($limit=100)
    {
        $where = [
            'AND', 
            ['status' => static::STATUS_INIT],
            ['>', 'createtime', date('Y-m-d H:i:00', strtotime('-5 days'))],
            ['<', 'createtime', date('Y-m-d H:i:00')]
        ];
        $req = static::find()->where($where)->limit($limit)->all();
        return $req;
    }
    /**
     * Undocumented function
     * 更新数据
     * @param [type] $data
     * @return void
     */
    public function updateData($data){
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(false, current($error));
        }
        return $this->save();
    }
}
