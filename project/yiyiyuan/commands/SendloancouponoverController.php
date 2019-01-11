<?php
/**
 * 借款优惠券即将失效推送
 */
 /**
  * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
  * 2 使用 
  *   linux : /data/wwwroot/yiyiyuan/yii income > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
  *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
  */

namespace app\commands;
use yii\console\Controller;

use app\models\dev\Accesstoken;
use app\models\dev\Coupon_list;
use app\models\dev\User;
use app\commonapi\Http;
use app\commonapi\Logger;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0); 
ini_set('memory_limit','-1');

class SendloancouponoverController extends Controller
{
	
	// 命令行入口文件
	public function actionIndex(){
//		$now_time = date('Y-m-d H:i:s');
		$nowtime = time();
		$start_time = date('Y-m-d H:i:s',strtotime('+1 days'));
                $end_time = date('Y-m-d H:i:s',  strtotime('+2 days'));
                $condition = [
                    'AND',
                    ['status'=>1],
                    ['>','end_date',$start_time],
                    ['<','end_date',$end_time]
                ];
		$total = Coupon_list::find()->where($condition)->count();
		$limit = 100;
		$pages = ceil( $total / $limit );
		
		$this->log( "\n". date('Y-m-d H:i:s') . "......................");
		$this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
		$template_id = Yii::$app->params['overduecoupon_template_id'];
		
		for( $i=0; $i < $pages; $i++ ){
			$coupon_list = Coupon_list::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
			// 没有数据时结束
			if( empty($coupon_list) ){
				break;
			}
			
			$this->log("处理范围" . ($i * $limit). ' -- ' . ($i * $limit + $limit) );
			
			foreach ($coupon_list as $key=>$value){
				$leftdays = ceil((strtotime($value['end_date'])-$nowtime)/3600/24);
				if($leftdays == 2){
						//发送微信模板消息
						$user_info = User::find()->select(array('openid'))->where(['mobile'=>$value->mobile])->one();
						if(!empty($user_info) && !empty($user_info->openid)){
							$openid = $user_info->openid;
							$end_date = date('Y-m-d', (strtotime($value['end_date'])-24*3600));
							$result = $this->sendWeixinTemplate($openid, $template_id, $end_date);
						}
				}
			}
		}
	}

	//微信模板推送
	private function sendWeixinTemplate($openid, $template_id, $end_date){
	
		$url = Yii::$app->params['app_url'] . "/dev/account/coupon";
		$nowtime = date('Y' . '年' . 'm' . '月' . 'd' . '日' . ' H:i');
		$data = '{
                                               "touser":"' . $openid . '",
                                               "template_id":"' . $template_id . '",
                                               "url":"' . $url . '",
                                               "topcolor":"#FF0000",
                                               "data":{
                                                                "first": {
                                                                          "value":"您有一张优惠在24小时后就过期了，别忘记及时使用哦。>>立即查看",
                                                                          "color":"#173177"
                                                                         },
                                                                 "keyword1":{
                                                                           "value":"先花一亿元优惠券",
                                                                           "color":"#173177"
                                                                  		 },
                                                                  "keyword2": {
                                                                            "value":"无",
                                                                            "color":"#173177"
                                                                          },
                                                                  "keyword3": {
                                                                            "value":"'.$end_date.'",
                                                                            "color":"#173177"
                                                                          },
                                                                  "remark":{
                                                                             "value":"在借款时使用，减免服务费",
                                                                              "color":"#173177"
                                                                             }
                                                         }
                                     }';
		//print_r($data);exit;
		$resulttemplate = $this->sendTemplatetouser($data);
		Logger::errorLog(print_r($resulttemplate, true), 'sendtemplatetouserbycouponover');
	
		return true;
	}
	
	private function sendTemplatetouser($data)
	{
		$url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$this->getAccessToken();
		$result = Http::dataPost($data,$url);
		return $result;
	}
	
	private function getAccessToken() {
		$appId = \Yii::$app->params['AppID']; //，需要在微信公众平台申请自定义菜单后会得到
        		$appSecret = \Yii::$app->params['AppSecret']; //需要在微信公众平台申请自定义菜单后会得到
        

		//先查询对应的数据表是否有token值
		$access_token = Accesstoken::find()->where(['type' => 1])->one();
		if (isset($access_token->access_token)) {
			//判断当前时间和数据库中时间
			$time = time();
			$gettokentime = $access_token->time;
			if (($time - $gettokentime) > 7000) {
				//重新获取token值然后替换以前的token值
				$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
				$data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
				$resultArr = json_decode($data, true); //转为数组
				$accessToken = $resultArr["access_token"]; //获取access_token
				//替换以前的token值
				$sql = "update yi_access_token set access_token = '$accessToken',time=$time where type=1";
				$result = Yii::$app->db->createCommand($sql)->execute();
	
				return $accessToken;
			} else {
				return $access_token->access_token;
			}
		} else {
			//获取token值并把token值保存在数据表中
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
			$data = Http::getCurl($url); //通过自定义函数getCurl得到https的内容
			$resultArr = json_decode($data, true); //转为数组
			$accessToken = $resultArr["access_token"]; //获取access_token
	
			$time = time();
			$sql = "insert into " . Accesstoken::tableName() . "(access_token,time) value('$accessToken','$time')";
			$result = Yii::$app->db->createCommand($sql)->execute();
	
			return $accessToken;
		}
	}
	
	// 纪录日志
	private function log($message){
		echo $message."\n";
	}
	
}