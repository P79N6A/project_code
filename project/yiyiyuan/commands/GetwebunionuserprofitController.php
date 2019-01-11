<?php
/**
 * 获取网盟用户在前一天应该获取的收益
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */
 
namespace app\commands;

use app\models\dev\Webunion_user_list;
use app\models\dev\Webunion_account;
use app\models\dev\Webunion_profit_detail;
use app\models\dev\User;
use app\models\dev\Standard_statistics;
use Yii;
use yii\console\Controller;
use app\models\dev\User_loan;


class GetwebunionuserprofitController extends Controller {

	// 命令行入口文件
	public function actionIndex() {
		//查询所有的网盟用户
		$condition = "type = 0";
		$begin_time = date('2015-11-09 00:00:00');
		$end_time = date('Y-m-d 23:59:59', (time()-24*3600));
		
		//获取网盟用户总数
		$total = Webunion_user_list::find()->where($condition)->count();
		
		//每100条处理一次
		$limit = 100;
		$pages = ceil( $total / $limit );
		
		$this->log( "\n". date('Y-m-d H:i:s') . "......................");
		$this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
		
		for( $i=0; $i < $pages; $i++ ){
			$user_list = Webunion_user_list::find()->select(array('user_id'))->where($condition)->offset($i * $limit)->limit($limit)->all();
			
			//如果没有用户，则直接结束
			if(empty($user_list)){
				break;
			}
			
			$this->log("处理范围" . ($i * $limit). ' -- ' . ($i * $limit + $limit) );
			
			foreach ($user_list as $key=>$value){
				//查询一级好友
				$user_list_first = Webunion_user_list::find()->select(array('user_id'))->where(['parent_user_id'=>$value['user_id'],'type'=>1])->all();
				
				if(!empty($user_list_first)){
					foreach ($user_list_first as $k_first=>$v_first){
						$this->getParentProfit($value['user_id'], $v_first['user_id'], $begin_time, $end_time);
						
						//查询二级好友
						$user_list_second = Webunion_user_list::find()->select(array('user_id'))->where(['parent_user_id'=>$v_first['user_id'],'type'=>2])->all();
						
						if(!empty($user_list_second)){
							foreach ($user_list_second as $k_second=>$v_second){
								$this->getParentProfit($v_first['user_id'], $v_second['user_id'], $begin_time, $end_time);
								
								//父级的父级收益千三
								$this->checkUserLoan($value['user_id'], $v_second['user_id'], '0.003', $begin_time, $end_time);
								
								//查询三级好友
								$user_list_third = Webunion_user_list::find()->select(array('user_id'))->where(['parent_user_id'=>$v_second['user_id'],'type'=>3])->all();
								
								if(!empty($user_list_third)){
									foreach ($user_list_third as $k_third=>$v_third){
										$this->getParentProfit($v_second['user_id'], $v_third['user_id'], $begin_time, $end_time);
										
										//父级的父级收益千三,父级的父级的父级千一
										$this->checkUserLoan($v_first['user_id'], $v_third['user_id'], '0.003', $begin_time, $end_time);
										$this->checkUserLoan($value['user_id'], $v_third['user_id'], '0.001', $begin_time, $end_time);
									}
								}else{
									continue;
								}
							}
						}else{
							continue;
						}
					}
				}else{
					continue;
				}
			}
		}
	}
	
	//查询上一级用户应该获取的收益
	private function getParentProfit($user_id,$invest_user_id,$begin_time,$end_time){
		//查询一级好友是否审核通过
		$this->checkUserStatus($user_id, $invest_user_id);
		
		//查询一级好友在前一日是否有借款
		$this->checkUserLoan($user_id, $invest_user_id, '0.005', $begin_time, $end_time);
			
		//查询一级好友在前一日是否有标的投资
		$this->checkUserInvest($user_id, $invest_user_id, $begin_time, $end_time);
	}
	
	//判断用户是否审核通过并获取收益
	private function checkUserStatus($user_id,$invest_user_id){
		$userinfo_first = User::find()->select(array('status','user_id'))->where(['user_id'=>$invest_user_id])->one();
		if(($userinfo_first->status == 3) && ($user_id != $userinfo_first->user_id)){
			//查询是否给过收益，如果没给，直接给父类用户5元现金和3个积分
			$profit_detail = Webunion_profit_detail::find()->where(['user_id'=>$user_id,'type'=>2,'profit_id'=>$invest_user_id,'profit_type'=>2])->one();
			if(empty($profit_detail)){
				//给父类用户5元现金和3个积分

				$sql_ret = "update ".Webunion_account::tableName()." set total_history_interest=total_history_interest+5,score=score+3 where user_id=".$user_id;				
				$ret = Yii::$app->db->createCommand($sql_ret)->execute();
				//添加两条收益明细
				$ret_check = $this->addUserProfit($user_id,2,$invest_user_id,5,2);
				$ret_score = $this->addUserProfit($user_id,2,$invest_user_id,3,3);
			}
		}
	}

	//判断用户是否有借款并获取收益
	private function checkUserLoan($user_id,$invest_user_id,$rate,$begin_time,$end_time,$type=1){

		$loaninfo_first = User_loan::find()->select(array('loan_id','user_id','status','current_amount'))->where(['user_id'=>$invest_user_id,'business_type'=>[1,4,5,6]])->andWhere("withdraw_time >= '$begin_time' and withdraw_time <= '$end_time'")->andWhere("status IN (8,9,11)")->all();
		if(!empty($loaninfo_first)){
				foreach ($loaninfo_first as $k_loan_first=>$v_loan_first){
					if($user_id != $v_loan_first['user_id']){
						//给父类用户借款额度千5的收益以及对应的积分
						$score_first = ($v_loan_first['status'] == 8) ? 20 : 10;
						$score_type = ($v_loan_first['status'] == 8) ? 4 : 3;
						$profit_detail_count = Webunion_profit_detail::find()->where(['user_id'=>$user_id,'type'=>$score_type,'profit_id'=>$v_loan_first['loan_id']])->count();
						if($profit_detail_count == 0){
						
							$total_history_interest = $v_loan_first['current_amount']*$rate;
							$sql_ret = "update ".Webunion_account::tableName()." set total_history_interest=total_history_interest+$total_history_interest,score=score+$score_first where user_id=".$user_id;
							
							$ret = Yii::$app->db->createCommand($sql_ret)->execute();
							//添加两条收益明细
							$ret_check = $this->addUserProfit($user_id,$score_type,$v_loan_first['loan_id'],$v_loan_first['current_amount']*$rate,2);
							$ret_score = $this->addUserProfit($user_id,$score_type,$v_loan_first['loan_id'],$score_first,3);
						}
					}
				}
		}
	}
	
	//判断用户是否有投资和获取收益
	private function checkUserInvest($user_id,$invest_user_id,$begin_time,$end_time){
		$standard_first_count = Standard_statistics::find()->where(['user_id'=>$invest_user_id])->andWhere("total_onInvested_share > 0")->andWhere("create_time >= '$begin_time' and create_time <= '$end_time'")->count();
		
		//给父类用户$standard_first_count*5M流量和$standard_first_count*10个积分
		if($standard_first_count > 0){
			//查询是否给过收益
			$profit_detail_statistics = Webunion_profit_detail::find()->where(['user_id'=>$user_id,'type'=>5,'profit_id'=>$invest_user_id,'profit_type'=>1])->one();
			if(empty($profit_detail_statistics)){
				$total_history_interest = $standard_first_count*5;
				$score = $standard_first_count*10;
				$sql_ret = "update ".Webunion_account::tableName()." set total_history_interest=total_history_interest+$total_history_interest,score=score+$score where user_id=".$user_id;
				$ret = Yii::$app->db->createCommand($sql_ret)->execute();
				//添加两条收益明细
				$ret_check = $this->addUserProfit($user_id,5,$invest_user_id,$standard_first_count*5,1);
				$ret_score = $this->addUserProfit($user_id,5,$invest_user_id,$standard_first_count*10, 3);
			}
		}
	}
	
	//添加收益明细
	private function addUserProfit($user_id,$type,$profit_id,$profit_amount,$profit_type){
		$profit_score = array(
				'user_id' => $user_id,
				'type' => $type,
				'profit_id' => $profit_id,
				'profit_amount' => $profit_amount,
				'profit_type' => $profit_type,
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