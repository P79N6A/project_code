<?php

/**
 * 给逾期用户和投资者发送推送消息
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands;

use app\models\dev\Mobile;
use Yii;
use yii\console\Controller;
use app\commonapi\Apihttp;
use app\commonapi\ApiSms;
/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */


class MobileController extends Controller {
  
  // 命令行入口文件
  public function actionIndex() {
    
    $sucess = 0;
    $total = Mobile::find()->where(['type' => 10])->count();
    $limit = 1000;
    $pages = ceil( $total / $limit );
    
    $this->log( "\n". date('Y-m-d H:i:s') . "......................");
    $this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
    
    for( $i=0; $i < $pages; $i++ ){
      $list = Mobile::find()->where(['type' => 10])->offset($i * $limit)->limit($limit)->all();
      if( empty($list) ){
        break;
      }
      
      $this->log("处理范围" . ($i * $limit). ' -- ' . ($i * $limit + $limit) );
      foreach ($list as $key=>$value){
        $value->status = 2;
        $result = $value->save();
        $name = !empty($value->name) ? $value->name : '';
        
        $ret = $this->sendSms($value->mobile, $name, $value->type);
        
        if( $result ){
          $sucess ++;
        }
      }
    }
    
    $fails = $total - $sucess;
    $this->log("\n处理结果:成功{$sucess}条, 失败{$fails}条");
  }
  
  //发送短信
    private function sendSms($mobile, $name, $type) {
      $content = '尊敬的'.$name.'，您已受邀成为vip用户，获得1000元借款额度，尽享vip绿色通道，放款只需30秒到账。点击链接，http://t.cn/RitocUo，马上领取66元红包，当日有效，机会难得！退订回T';
      $sms_type = 36;
        //$content = '【先花一亿元】' . $content;
        $api = new Apihttp();
        $params = array(
            'mobile' => $mobile,
            'content' => $content,
            'sms_type' => $sms_type,
            'aid' => 1,
        );
        //$sendRet = $api->sendSmsByChuanglan($params);
        $apiModel = new ApiSms();
        $sendRet   = $apiModel->choiceChannel($mobile, $content, $sms_type, '', 3);
        return true;
    }
  
  // 纪录日志
  private function log($message){
    echo $message."\n";
  }
}