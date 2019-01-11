<?php

/**
 * 三月用户找回优惠券发送
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands;

use app\models\news\SmsSend;
use Yii;
use app\models\dev\Mobile;
use yii\console\Controller;
use app\models\news\Coupon_list;
use app\models\dev\Coupon_apply;
/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */


class SendcouponmarchController extends Controller {
    public $type;
    public $val;
    public $days;
  // 命令行入口文件
  public function actionIndex($type_pamas = '54', $val_pamas = "36", $days_pamas = 5) {
    $type = $this->type = $type_pamas;//type
    $val = $this->val = $val_pamas;//优惠券金额
    $days = $this->days = $days_pamas;//优惠券有效期
    $sucess = 0;
    $total = Mobile::find()->where(['type' => $type,'status' => 1])->count();
//    print_r($type);die;
    $limit = 1000;
    $pages = ceil( $total / $limit );
    
    $this->log( "\n". date('Y-m-d H:i:s') . "......................");
    $this->log("\n all:{$total},limit:{$limit},pages:{$pages}\n");

    for( $i=0; $i < $pages; $i++ ){
      $list = Mobile::find()->where(['type' => $type,'status' => 1])->limit($limit)->all();
      if( empty($list) ){
        break;
      }
      
      $this->log("doing..." . ($i * $limit). ' -- ' . ($i * $limit + $limit) );
      foreach ($list as $key=>$value){
          $value->status = 2;
          $result = $value->save();
          if(empty($value->userbyuserid)){
              continue;
          }
            //$coupon = Coupon_list::find()->where(['mobile' => $value->userbyuserid->mobile,'status' => 1,'val' => $val])->one();
            //if (!empty($coupon)) {
            //    continue;
            //} else {
                //$couponModel = new Coupon_apply();
                if(isset($value->userbyuserid) && !empty($value->userbyuserid)){
                    //发送优惠卷
                    //$sendCouponRes = $couponModel->sendcoupon($value->userbyuserid->user_id, $val.'元回馈券', 1, $days, $val, 100000);//名称
                    //添加到短信发送表
                    $addData['mobile'] = $value->userbyuserid->mobile;
                    $addData['content'] = '您已提额，立即申请享受全新额度，并享有审批及资金匹配双重加速福利！http://c7.gg/PsrV。退订回TD';
                    $addData['sms_type'] = 14;
                    $addData['status'] = 0;
                    $addData['channel'] = 3;
                    $addData['send_time'] = date('Y-m-d H:i:s');
                    $sms_model = new SmsSend();
                    $SmsRes = $sms_model->addSmsSend($addData);
                    if ($SmsRes) {
                        $sucess++;
                    }
                }
           // }
        
      }
    }
    
    $fails = $total - $sucess;
    $this->log("\n result:success:{$sucess}, fails{$fails}");
  }
  
  
  // 纪录日志
  private function log($message){
    echo $message."\n";
  }
}