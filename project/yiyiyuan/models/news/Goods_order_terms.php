<?php

namespace app\models\news;

use app\commonapi\Keywords;
use app\commonapi\Logger;
use Yii;

/**
 * This is the model class for table "yi_goods_order_terms".
 *
 * @property string $id
 * @property string $order_id
 * @property string $user_id
 * @property string $goods_id
 * @property string $goods_content
 * @property integer $status
 * @property integer $terms
 * @property string $money
 * @property string $start_date
 * @property string $end_date
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Goods_order_terms extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_order_terms';
    }
    public function getGoods() {
        return $this->hasOne(Goods_list::className(), ['id' => 'goods_id']);
    }
    public function getAddress() {
        return $this->hasOne(Goods_address::className(), ['id' => 'a_id']);
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'a_id', 'goods_id', 'goods_content', 'terms', 'money'], 'required'],
            [['user_id', 'a_id', 'goods_id','user_credit_list_id', 'status','term_days', 'terms', 'version'], 'integer'],
            [['money'], 'number'],
            [['start_date', 'end_date', 'create_time', 'last_modify_time'], 'safe'],
            [['order_id'], 'string', 'max' => 32],
            [['goods_content'], 'string', 'max' => 510]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'user_id' => 'User ID',
            'goods_id' => 'Goods ID',
            'user_credit_list_id' => 'User Credit List Id',
            'a_id' => 'A ID',
            'goods_content' => 'Goods Content',
            'status' => 'Status',
            'term_days'=>'Term Days',
            'terms' => 'Terms',
            'money' => 'Money',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function addOrder($goods_info,$data){
        if (!is_array($data) || empty($data) || empty($goods_info)) {
            return false;
        }
        $order_id = date('Ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
        $time = date('Y-m-d H:i:s');
        $condition = $data;
        $condition['order_id']          = $order_id;
        $condition['terms']             = $data['terms'];
        $condition['term_days']             = $data['days'];
        $condition['money']             = $data['goods_price'];
        $condition['goods_content']     = $data['description'];
        $condition['status']            = 0;
        $condition['user_credit_list_id']            = $data['listId'];
        $condition['create_time']       = $time;
        $condition['last_modify_time']  = $time;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        if(!$this->save()){
            return false;
        }
        return $order_id;
    }

    /**
     * 通过order_id获取订单
     * @param $orderId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getGoodsOrderByOrderId($orderId){
        if(!$orderId) {
            return null;
        }
        return self::find()->where(['order_id'=>$orderId])->one();
    }

    /**
     * 通过用户id获取商品订单
     * @param $userId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getGoodsListByUserId($userId,$type=1){
        $userId = intval($userId);
        if(!$userId) {
            return null;
        }
        if($type==1){
            $where=[
                'AND',
                ['user_id'=>$userId],
                ['<>','user_credit_list_id',0],
            ];
        }else{
            $where=[
                'user_id'=>$userId,
                'user_credit_list_id'=>0,
            ];
        }
        return self::find()->where($where)->orderBy('create_time desc')->asArray()->all();
    }
    
    public function getHaveinOrder($userId){
        $userId = intval($userId);
        if(!$userId) {
            return null;
        }
        return self::find()->where(['user_id'=>$userId,'status'=>0])->all();
    }

    /**
     * 获取初始距离
     * @param int $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getInitData($limit = 200){
         $where = [
             'AND',
             ['status' => 0 ],
             ['<=', 'create_time', date('Y-m-d H:i:s',strtotime('-7 days'))],
         ];
        return self::find()->where($where)->limit($limit)->all();
    }

    /**
     * 批量锁定
     * @param type $ids
     * @return boolean
     */
    public function updateAllLock($ids) {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        return self::updateAll(['status' => 2], ['id' => $ids]);
    }

    /**
     * 锁定单条
     * @return bool
     */
    public function lock() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status = 2;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 驳回
     * @return bool
     */
    public function reject() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status = 1;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //查询是否有进行中的订单（二期）
    public function isOrderlist($user_id){
        if(empty($user_id)){
            return false;
        }
        $oGoodsOrderTerms=self::find()->where(['user_id'=>$user_id])->orderBy('id desc')->one();
        if(empty($oGoodsOrderTerms)){
            return false;
        }
        $oUserCreditList=UserCreditList::findOne($oGoodsOrderTerms->user_credit_list_id);
        if(!empty($oUserCreditList)){
            if($oUserCreditList->status==1){//审核中
                return $oUserCreditList;
            }
        }
        return false;
    }

    //查询评测状态 * @return user_credit_status 1:未测评;2已测评不可借;3:评测中;4:已测评可借未购买;5:已测评可借已购买;6:已过期;
    public function OrderlistStatus($order_id){
        if(empty($order_id)){
            return false;
        }
        $oGoodsOrderTerms=self::find()->where(['order_id'=>$order_id])->one();
        if(empty($oGoodsOrderTerms) || empty($oGoodsOrderTerms->user_credit_list_id)){
            return false;
        }
        $oUserCreditList=UserCreditList::findOne($oGoodsOrderTerms->user_credit_list_id);
        $yyyCredit = $this->getYyyCredit($oUserCreditList);
        if(!empty($oUserCreditList)){
            if($oUserCreditList->status==1){//评测中
                $user_credit_status= 3;
            }
            elseif ($oUserCreditList->status == 2 && $oUserCreditList->res_status == 2) {
                //已评测，不可借
                $user_credit_status = 2;
                $invalid_time = $oUserCreditList->last_modify_time;
            } elseif ($oUserCreditList->status == 2 && $oUserCreditList->res_status == 1 && $oUserCreditList->pay_status == 0 && $yyyCredit) {
                //已评测，可借 ,未购卡
                $user_credit_status = 4;
                $invalid_time = $oUserCreditList->invalid_time;
            } elseif ($oUserCreditList->status == 2 && $oUserCreditList->res_status == 1 && $oUserCreditList->pay_status == 0 && !$yyyCredit) {
                //已评测，可借 ,未购卡,一亿元额度数据不完整
                $user_credit_status = 2;
                $invalid_time = $oUserCreditList->last_modify_time;
            } elseif ($oUserCreditList->status == 2 && $oUserCreditList->res_status == 1 && $oUserCreditList->pay_status == 1) {
                //已评测，可借 ,已购卡
                $user_credit_status = 5;
                $invalid_time = $oUserCreditList->invalid_time;
            }
            if (!empty($oUserCreditList->invalid_time)) {
                if ($oUserCreditList->invalid_time < date('Y-m-d H:i:s')) {
                    //已过期
                    $user_credit_status = 6;
                }
            }
            if (!empty($oUserCreditList->loan_id)) {
                $user_credit_status = 6; //重新评测
            }

            $user_credit = [
                'order_amount' => empty($oUserCreditList->amount) ? Keywords::getMaxCreditAmounts() : $oUserCreditList->amount,
                'user_credit_status' => $user_credit_status,
            ];
            return $user_credit;
        }
        return false;
    }

    /**
     * 一亿元评测结果
     * @param type $o_user_credit
     * @return boolean true:一亿元评测数据完整  false:一亿元评测数据不完整
     */
    public function getYyyCredit($o_user_credit) {
        if (empty($o_user_credit->amount) || empty($o_user_credit->days) || empty($o_user_credit->interest_rate) || empty($o_user_credit->crad_rate)) {
            return false;
        }
        return true;
    }

    /*
     *进行按天数分期数计算每期信息
     * */
    public function ByTerms($money,$term_days,$terms,$statr_date){
        if(empty($money) || empty($term_days) || empty($terms)){
            return false;
        }
        $single_money=ceil(($money/$terms));
        $total_money=$single_money*$terms;
        $cjmoney=$total_money-$money;
        $single_money_end=$single_money-$cjmoney;
        $interest=0;
        for($i=1;$i<=$terms;$i++){
            $interest+=($money-$single_money*($i-1))*0.00098*$term_days;
            $data[]=[
                'days'=>date('Y-m-d', strtotime($statr_date)+$term_days*$i*86400),
            ];
        }
        foreach($data as $key=>$val){
            if($key==0){
                $data[$key]['single_money']=ceil(($single_money+$interest)*100)/100;
            }else{
                if($key==($terms-1)){//等于最后一期的时候
                   $data[$key]['single_money']=sprintf("%.2f",$single_money_end);
                }else{
                    $data[$key]['single_money']=sprintf("%.2f",$single_money);
                }
            }
        }
        return $data;

    }

}
