<?php
/**
 * 借款优惠券失效定时
 */
 /**
  * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
  * 2 使用 
  *   linux : /data/wwwroot/yiyiyuan/yii income > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
  *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
  */

namespace app\commands;
use yii\console\Controller;

use app\models\dev\Coupon_list;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0); 
ini_set('memory_limit','-1');

class SendloancouponinvalidController extends Controller
{	
	// 命令行入口文件
	public function actionIndex(){
		$now_time = date('Y-m-d H:i:s');
		$begin_time = date('Y-m-d 00:00:00');
		$condition = "status = 1 and end_date >= '$begin_time' and end_date <= '$now_time'";
		$total = 0;
		$sucess = 0;
		$total = Coupon_list::find()->where($condition)->count();
		$limit = 1000;
		$pages = ceil( $total / $limit );
		
		$this->log( "\n". date('Y-m-d H:i:s') . "......................");
		$this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
		
		for( $i=0; $i < $pages; $i++ ){
			$coupon_list = Coupon_list::find()->where($condition)->limit($limit)->all();
			
			// 没有数据时结束
			if( empty($coupon_list) ){
				break;
			}
			
			$this->log("处理范围" . ($i * $limit). ' -- ' . ($i * $limit + $limit) );
			
			foreach ($coupon_list as $key=>$value){
					$value->status = 3;
					
					$result = $value->save();
					
					// 计算成功数
					if( $result ){
						$sucess ++;
					}
			}
		}
		
		$fails = $total - $sucess;
		$this->log("\n处理结果:成功{$sucess}条, 失败{$fails}条");
	}
	
	// 纪录日志
	private function log($message){
		echo $message."\n";
	}
	
}