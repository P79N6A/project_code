<?php

namespace app\models\news;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\news\Cg_remit;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_warn_message_list".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $message_id
 * @property integer $type
 * @property integer $time_type
 * @property integer $status
 * @property integer $read_status
 * @property string $title
 * @property string $contact
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class WarnMessageList extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_warn_message_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'title', 'contact', 'create_time', 'last_modify_time'], 'required'],
            [['user_id', 'type', 'time_type', 'status', 'read_status', 'version', 'channel', 'relation_id', 'message_id'], 'integer'],
            [['contact'], 'string'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['title'], 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'message_id' => 'Message ID',
            'type' => 'Type',
            'time_type' => 'Time Type',
            'status' => 'Status',
            'read_status' => 'Read Status',
            'title' => 'Title',
            'contact' => 'Contact',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    //未读提醒类消息数量(列表展示)
    public function getWarnmsgcount($user_id, $read_status = 0) {
        $count = WarnMessageList::find()->where(['user_id' => $user_id, 'read_status' => $read_status, 'status' => [2,3],'is_show'=>1])->count();
        return $count;
    }

    //未读提醒类消息(列表展示)
    public function getWarnmsg($user_id, $offset, $limit = 10) {
        $list = WarnMessageList::find()->select('id,title,contact,read_status,create_time')->where(['user_id' => $user_id, 'status' => [2,3],'is_show'=>1])->offset($offset)
                        ->limit($limit)->orderBy('last_modify_time desc')
                        ->asArray()->all();
        foreach ($list as $key => $value) {
            $list[$key]['msg_id'] = $value['id'];
            unset($list[$key]['id']);
            $list[$key]['status'] = $value['read_status'];
            unset($list[$key]['read_status']);

            $list[$key]['time'] = date('Y-m-d H:i', strtotime($value['create_time']));
            unset($list[$key]['create_time']);
        }

        return $list;
    }

    /**
     * 根据消息id,用户id查询消息详情
     * @return array
     */
    public function getWarnmsginfoByUserIdAndMsgId($user_id, $msg_id) {
        if (empty($msg_id) || !is_numeric($msg_id) || empty($user_id) || !is_numeric($user_id)) {
            return null;
        }

        $msginfo = WarnMessageList::find()->where(['user_id' => $user_id, 'id' => $msg_id])->one();
        return $msginfo;
    }

    public function update_info($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');

        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 更改发送状态为成功
     * @return boolean
     */
    public function changeSuccess() {
        try {
            $this->status = 2;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 更改发送状态为失败
     * @return boolean
     */
    public function changeFail() {
        try {
            $this->status = 3;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 更新状态为已读
     * @return bool
     * @author 王新龙
     * @date 2018/10/9 14:33
     */
    public function updateReadSuccess(){
        try {
            $this->read_status = 1;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 单条锁定
     * @return boolean
     */
    public function changeOneLock() {

        try {
            $this->status = 1;
            $this->last_modify_time = date('Y-m-d H:i:s');
            return $this->save();
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * 批量锁定
     * @return boolean
     */
    public function changeLock($ids) {
        try {
            return self::updateAll(['status' => 1], ['id' => $ids]);
        } catch (\Exception $ex) {
            return false;
        }
    }
    
    /**
     * 定时批量插入待发送提醒消息数据
     * @param type $user_relation_info 
     * @param type $time_type 
     * @param type $type
     * @param type $selection_val
     * @return type
     */
    public function  todo($user_relation_info,$time_type,$type,$selection_val='')
    {
          if($type!=7){
              $sms_content = $this->getSmscontent($time_type,$type,$selection_val=''); //获取信息内容
          }
          //写入提醒消息表
          foreach ($user_relation_info as $val){
              if($type==7){
                  if($time_type == 1){
                      $sms_content['time_type'] = 1;    
                      $sms_content['title'] = $val['title'].'券已经到账，用券可享借款审批提速！';
                      $sms_content['content'] = $val['title'].'券已经到账，用券还可享受借款审批提速90%的特权哦！';
                      $sms_content['channel'] = 2;   //push
                  }else{
                      $sms_content['time_type'] = 2;
                      $sms_content['title'] = '您的'.$val['title'].'券即将到期，现在使用可享双重加速特权！';
                      $sms_content['content'] = '您的'.$val['title'].'券即将到期，已为您开通专属双重加速特权，款审批提速90%，通过率提高98%。';
                      $sms_content['channel'] = 4;   //短信+push
                  }
                  $remark_res =  $this->getInsertdataremark($val,$type,$sms_content['time_type']); //判断是否重复插入了数据
                  $relation_id = $this->getRelationdata($val,$type);      //获取不同类型的消息的关联id
                  if($remark_res){
                      $data[] = [
                          'user_id' => $val->user->user_id,
                          'type' => $type, //放款
                          'time_type' => $sms_content['time_type'],
                          'status' => 0,
                          'read_status' => 0,
                          'title' => $sms_content['title'],
                          'contact' => $sms_content['content'],
                          'create_time' => date('Y-m-d H:i:s'),
                          'last_modify_time' => date('Y-m-d H:i:s'),
                          'version' => 0,
                          'channel' => $sms_content['channel'], //短信+push
                          'relation_id' => $relation_id,
                      ];
                  }
              }else{
                 
                  $remark_res =  $this->getInsertdataremark($val,$type,$sms_content['time_type']); //判断是否重复插入了数据
                  $relation_id = $this->getRelationdata($val,$type);      //获取不同类型的消息的关联id
                  if($remark_res){
                      $data[] = [
                          'user_id' => $val->user_id,
                          'type' => $type, //放款
                          'time_type' => $sms_content['time_type'],
                          'status' => 0,
                          'read_status' => 0,
                          'title' => $sms_content['title'],
                          'contact' => $sms_content['content'],
                          'create_time' => date('Y-m-d H:i:s'),
                          'last_modify_time' => date('Y-m-d H:i:s'),
                          'version' => 0,
                          'channel' => $sms_content['channel'], //短信+push
                          'relation_id' => $relation_id,
                      ];
                  }
              }

          }
        $res = 0;
        $warnmsg_model = new WarnMessageList;
        
        if(!empty($data)){
            $res = $warnmsg_model->insertBatch($data);
        }
        return $res;
    }
    
    /**
     * 
     * @param type $val 包含user_id和关联id的数据
     * @param type $type  1：资料未填写 2：借款申请 3：批贷 4：加速卡 5：放款 6：活动 7：优惠券
     * @return type $relation   关联id
     */
    private function getRelationdata($val,$type){
        $relation = null;
        switch ($type){
            case 1:  
                break;
            case 2:
                break;
            case 3: 
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
            case 4:
                break;
            case 5:  
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
            case 6:
                $relation = empty($val->activity_id)? null:$val->activity_id;
                break;
            case 7:
                $relation = empty($val->id)? null:$val->id;
                break;
            case 9:  
                $relation = empty($val->req_id)? null:$val->req_id;
                break;
            case 10:  
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
            case 11:  
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
            case 12:  
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
            case 13:  
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
            case 14:  
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
            case 15:  
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
            case 16:  
                $relation = empty($val->loan_id)? null:$val->loan_id;
                break;
        }
        return $relation;
    }

     /**
     * 确定是否是重复插入的数据
     * @param type $val  包含user_id和关联id的数据
     * @param type $type 1：资料未填写 2：借款申请 3：批贷 4：加速卡 5：放款 6：活动 7：优惠券 8商城获取额度成功
     * @param type $time_type 
     */
    private function getInsertdataremark($val,$type,$time_type)
    {
        $remark = false;
        if($type ==1 || $type==2 ){
            if(empty($val->user_id)){
                Logger::errorLog(print_r($type, true), 'insertdataremark', 'crontab');
                return $remark;
            }
        }
        if($type ==3 || $type==5 ){
            if(empty($val->user_id) || empty($val->loan_id)){
                Logger::errorLog(print_r($type, true), 'insertdataremark', 'crontab');
                return $remark;
            }
        }
        if($type == 7){
            if(empty($val->user->user_id) || empty($val->id)){
                 Logger::errorLog(print_r($type, true), 'insertdataremark', 'crontab');
                 return $remark;
            }
        }
        if($type == 9){ //商城获取额度（$val是user_credit）
            if(empty($val->user_id) || empty($val->req_id)){
                 Logger::errorLog(print_r($type, true), 'insertdataremark', 'crontab');
                 return $remark;
            }
        }
        //商城购买商品待受托支付、商城下单用户未操作转售、商城债匹成功用户未操作转售、商城借款待续期、商城借款续期成功、商城借款续期失败1、商城借款续期失败2
        if(in_array($type, [10,11,12,13,14,15,16])  ){ 
            if(empty($val->user_id) || empty($val->loan_id)){
                 Logger::errorLog(print_r($type, true), 'insertdataremark', 'crontab');
                 return $remark;
            }
        }
        
       
        switch ($type){
            case 1:
                $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'time_type'=>$time_type,'type'=>$type])->all();
                if(empty($data)){
                    $remark = true;
                }
                break;
            case 2:
                $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'time_type'=>$time_type,'type'=>$type])->all();
                if(empty($data)){
                    $remark = true;
                }
                break;
            case 3:
                $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
                if(empty($data)){
                    $remark = true;
                }
                break;
            case 4:
                break;
            case 5:  
                $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
                if(empty($data)){
                    $remark = true;
                }
                break;
            case 6:
                break;
            case 7:
                $data = WarnMessageList::find()->where(['user_id'=>$val->user->user_id,'relation_id'=>$val->id,'time_type'=>$time_type,'type'=>$type])->all();
                if(empty($data)){
                    $remark = true;
                }
                break;
            case 9:
                $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->req_id,'time_type'=>$time_type,'type'=>$type])->all();
                if(empty($data)){
                    $remark = true;
                }
                break;  
            case 10:
               $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
               if(empty($data)){
                   $remark = true;
               }
               break;
            case 11:
               $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
               if(empty($data)){
                   $remark = true;
               }
               break;
            case 12:
               $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
               if(empty($data)){
                   $remark = true;
               }
               break;
           case 13:
               $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
               if(empty($data)){
                   $remark = true;
               }
               break;
          case 14:
               $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
               if(empty($data)){
                   $remark = true;
               }
               break;
         case 15:
               $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
               if(empty($data)){
                   $remark = true;
               }
               break;
        case 16:
               $data = WarnMessageList::find()->where(['user_id'=>$val->user_id,'relation_id'=>$val->loan_id,'time_type'=>$time_type,'type'=>$type])->all();
               if(empty($data)){
                   $remark = true;
               }
               break;       
        }
        
        return $remark;
    }

        /**
     * 
     * @param type $time_type
     * @param type $type
     * @return int
     */
    private function getSmscontent($time_type,$type,$selection_val=''){
        $data = [];
        
        if($type == 5){ //放款成功
            if($time_type ==1){
              $data['time_type'] = 1; //及时
              $data['title'] = '借款已到账，尽快提现！';
              $data['content'] = '银行已经把钱打到您的账户了，是时候该提现了！';
              $data['channel'] = 4;   //短信+push
            }else{
              $data['time_type'] =2; //12小时
              $data['title'] = '借款已到账，尽快提现！';
              $data['content'] = '银行已经把钱打到您的账户12个小时了，是时候该提现了！';
              $data['channel'] = 4;  
            }
        }
        
        if($type == 1){ //资料未填写
            if($time_type == 1){ //半小时
              $data['time_type'] = 1; 
              $data['title'] = '完成注册，最高可借一万元！';
              $data['content'] = '一个小时之内完成注册，额度可达10000元！你还在等什么？';
              $data['channel'] = 2;   //push
            }else{ //一天
              $data['time_type'] = 2; 
              $data['title'] = '注册就有机会获得限额免还券！';
              $data['content'] = '10000元额度的机会已经错过，别担心我们还准备了免还券，借钱不用还，还不快来借！';
              $data['channel'] = 4;   //短信+push
            }
        }
        
        if($type == 2){ //借款申请
              $data['time_type'] = 1; 
              $data['title'] = '用户资料已填写完成，可以发起借款啦！';
              $data['content'] = '尊敬的用户，您的资料已提交成功，可前往先花一亿元app或微信公众号查看详情，快来借款吧！';
              $data['channel'] = 4;   //短信+push
        }
        
        if($type == 3){ //批贷
            if($time_type == 2){
              $data['time_type'] = 2; 
              $data['title'] = '借款审核通过了，请快快处理。';
              $data['content'] = '您的借款审核已通过3小时，你还在等什么？';
              $data['channel'] = 2;   //push
            }elseif($time_type == 3){
              $data['time_type'] = 3; 
              $data['title'] = '审核通过8小时了，还不抓紧？';
              $data['content'] = '您的借款审核通过已经8个小时了，速速处理吧！';
              $data['channel'] = 2;   //push
            }elseif($time_type == 4){
              $data['time_type'] = 4; 
              $data['title'] = '借款即将失效，请尽快处理！';
              $data['content'] = '您的借款资格即将失效，请抓紧处理！';
              $data['channel'] = 4;  //短信+push
            }else{
              $data['time_type'] = 1; 
              $data['title'] = '审核通过了，快来！';
              $data['content'] = '您的借款审核已经通过，请尽快登录账户进行处理。';
              $data['channel'] = 4;  //短信+push
            }
             
        }
        
         if($type == 4){ //加速卡
             if($time_type == 1){
              $data['time_type'] = 1; 
              $data['title'] = '您的专属加速通道已经开通';
              $data['content'] = '通过安全认证，即可获得专属加速通道，下款速度提高90%，极速到账，快来体验！';
              $data['channel'] = 2;   //push
             }else{
              $data['time_type'] = 2; 
              $data['title'] = '您的专属加速通道正在失效... ...';
              $data['content'] = '下款速度提高90%的专属加速通道正在失效，通过安全认证，获得最后一次享受机会... ...';
              $data['channel'] = 2;   //push
             }
            
        }
        
         if($type == 6){ //活动
              $data['time_type'] = 1; 
              $data['title'] = $selection_val.'活动已经开始了，领大奖，享加速！';
              $data['content'] = $selection_val.'活动已经开始了，先到先得，还可享受审批加速服务哦！';
              $data['channel'] = 2;   //push
        }
        
        if($type == 9){ //商城获取额度
              $data['time_type'] = 1; 
              $data['title'] = '恭喜您获得'.$selection_val.'元额度！';
              $data['content'] = '恭喜您获得了'.$selection_val.'元额度，快来先花商城购物吧！';
              $data['channel'] = 2;   //push
        }
        if($type == 10){ //商城购买商品待受托支付
              $data['time_type'] = 1; 
              $data['title'] = '您的订单待支付！';
              $data['content'] = '您的商品已下单成功，完成授权即可购买成功，立即来授权吧！';
              $data['channel'] = 2;   //push
        }
        if($type == 11){ //商城下单用户未操作转售
              $data['time_type'] = 1; 
              $data['title'] = '商品采购中，可自动转售哦！';
              $data['content'] = '正在为您采购商品，现可进行商品自动转售哦！操作成功可自动转售您的商品！';
              $data['channel'] = 2;   //push
        }
        if($type == 12){ //商城债匹成功用户未操作转售
              $data['time_type'] = 1; 
              $data['title'] = '您的商品已发货，快来转售拿钱！';
              $data['content'] = '您的商品已发货，现可马上转售！一键转售马上拿钱！';
              $data['channel'] = 2;   //push
        }
        if($type == 13){ //商城借款续期待支付
              $data['time_type'] = 1; 
              $xuqiDays = '';
              $xuqiTime = '';
              if(is_array($selection_val)){
                  $xuqiDays = $selection_val['days'];
                  $xuqiTime = $selection_val['time'];
              }
              $data['title'] = '您的续期资格还有'.$xuqiDays.'天到期哦，请抓紧支付！';
              $data['content'] = '您的续期申请已通过，请在'.$xuqiDays.'天内完成支付即可续期成功，剩余支付时间'.$xuqiTime.'！';
              $data['channel'] = 2;   //push
        }
        if($type == 14){ //商城借款续期成功
              $data['time_type'] = 1; 
              $data['title'] = '恭喜您，您的商城账单续期成功！';
              $data['content'] = '恭喜您，您的商城账单续期成功，最后还款日变更为'.$selection_val.'！';
              $data['channel'] = 2;   //push
        }
        if($type == 15){ //商城借款续期失败1
              $data['time_type'] = 1;
              $data['title'] = '您的续期申请有新的进度！';
              $data['content'] = '很抱歉，由于您续期费用支付失败，本次续期失败，您可在'.$selection_val.'内进行重新支付！';
              $data['channel'] = 2;   //push
        }
        if($type == 16){ //商城借款续期失败2
              $data['time_type'] = 1;
              $data['title'] = '您的续期申请有新的进度！';
              $data['content'] = '很抱歉，由于您续期费用支付失败，本次续期失败。请留意最后还款日进行还款';
              $data['channel'] = 2;   //push
        }

        return $data;
    }

    /**
     * 单条保存推送信息
     * @param $loan_info
     * @param $time
     * @param $type
     * @return bool
     */
    public function saveWarnMessage($loan_info,$time,$type,$selection_val=''){
        if(empty($loan_info) || empty($time) || empty($type)){
            return false;
        }
        $sms_content = $this->getSmscontent($time,$type,$selection_val);
        //写入提醒消息表
        $remark_res =  $this->getInsertdataremark($loan_info,$type,$sms_content['time_type']);
        $relation_id = $this->getRelationdata($loan_info,$type);
        if(!$remark_res){
            return false;
        }
        $data = [
            'user_id' => $loan_info->user_id,
            'type' => $type,
            'time_type' => $sms_content['time_type'],
            'status' => 0,
            'read_status' => 0,
            'title' => $sms_content['title'],
            'contact' => $sms_content['content'],
            'channel' => $sms_content['channel'], //短信+push
            'relation_id' => $relation_id,
            'create_time' => date('Y-m-d H:i:s'),
            'last_modify_time' => date('Y-m-d H:i:s'),
            'version' => 0,
        ];
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
