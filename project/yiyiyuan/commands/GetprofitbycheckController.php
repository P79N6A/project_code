<?php
/**
 * 给前一天审核通过的用户10元的冻结收益
 * @author gaolian
 *
 */
namespace app\commands;

use app\models\dev\Webunion_account;
use app\models\dev\User;
use Yii;
use yii\console\Controller;
use app\models\dev\Webunion_profit_detail;


class GetprofitbycheckController extends Controller {
	
	// 命令行入口文件
	public function actionIndex() {
		$begin_time = date('Y-m-d 00:00:00', (time()-24*3600));
		$end_time = date('Y-m-d 23:59:59', (time()-24*3600));
		$nowtime = date('Y-m-d H:i:s');
		
		$condition = "status = 3 and verify_time >= '$begin_time' and verify_time <= '$end_time' and from_code != ''";
		
		//获取前一天审核通过的用户
		$total = User::find()->where($condition)->count();
		
		//每100条处理一次
		$limit = 100;
		$pages = ceil( $total / $limit );
		
		$this->log( "\n". date('Y-m-d H:i:s') . "......................");
		$this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
		
		for( $i=0; $i < $pages; $i++ ){
			
			//查询用户的父级
			$user_list = User::find()->select(array('user_id','from_code'))->where($condition)->offset($i * $limit)->limit($limit)->all();
			//如果没有用户，则直接结束
			if(empty($user_list)){
				break;
			}
			
			foreach ($user_list as $key=>$value){
				if(!empty($value['from_code'])){
					//给父类用户5元的收益和3个积分 
					$userinfo_parent = User::find()->select(array('status','user_id','is_webunion'))->where(['invite_code'=>(string)$value['from_code']])->one();
					
					if(!empty($userinfo_parent) && ($userinfo_parent->status != 5) && ($userinfo_parent->is_webunion == 'yes')){
						//查询是否给过收益，如果没给，直接给父类用户10元现金和3个积分
						$profit_detail = Webunion_profit_detail::find()->where(['user_id'=>$userinfo_parent->user_id,'type'=>2,'profit_id'=>$value['user_id'],'profit_type'=>2])->one();
						if(empty($profit_detail)){
							//判断父类的账户
							$webunion_count = Webunion_account::find()->where(['user_id'=>$userinfo_parent->user_id])->count();
							if($webunion_count == 0){
								$account = new Webunion_account();
								$user_account = array(
										'user_id' => $userinfo_parent->user_id,
										'frozen_interest' => 10,
										'score' => 3
										
								);
								$result = $account->addAccount($user_account);
								
							}else{
								$sql_ret = "update ".Webunion_account::tableName()." set frozen_interest=frozen_interest+10,score=score+3,last_modify_time='$nowtime',version=version+1 where user_id=".$userinfo_parent->user_id;
								$ret = Yii::$app->db->createCommand($sql_ret)->execute();
							}
							
							//添加一条积分收益明细
							$ret_score = $this->addUserProfit($userinfo_parent->user_id,2,$value['user_id'],3,3);
							//添加一条冻结收益记录
							$ret_frozen = $this->addUserProfit($userinfo_parent->user_id,2,$value['user_id'],10,2,1);
						}
					}
					
				}
			}
		}
	}
	
	//添加收益明细
	private function addUserProfit($user_id,$type,$profit_id,$profit_amount,$profit_type,$status=0){
		$profit_score = array(
				'user_id' => $user_id,
				'type' => $type,
				'profit_id' => $profit_id,
				'profit_amount' => $profit_amount,
				'profit_type' => $profit_type,
				'status' => $status
		);
		$profit = new Webunion_profit_detail();
		$ret_score = $profit->addProfit($profit_score);
		if($ret_score){
			return true;
		}else{
			return false;
		}
	}
	
	// 保存日志
	private function log($message){
		echo $message."\n";
	}
}