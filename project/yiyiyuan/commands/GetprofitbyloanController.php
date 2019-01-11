<?php
/**
 * 给前一天放款用户的父类0.5%的收益，父父类0.3%收益，父父父类0.1%收益,如果是首贷，则给父类10元冻结金额
 * @author gaolian
 *
 */
namespace app\commands;

use app\models\news\Webunion_account;
use app\models\news\User;
use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use Yii;
use yii\console\Controller;
use app\models\dev\Webunion_profit_detail;


class GetprofitbyloanController extends Controller {
	
	// 命令行入口文件
	public function actionIndex() {
		$begin_time = date('Y-m-d 00:00:00', (time()-24*3600));
		$end_time = date('Y-m-d 23:59:59', (time()-24*3600));
		$nowtime = date('Y-m-d H:i:s');
		
//		$sql_count = "select count(*) as count from yi_user_loan as l,yi_user as u,yi_user_loan_flows as f where f.loan_id=l.loan_id and (l.business_type IN(1,4,5,6)) and l.user_id=u.user_id and f.create_time >= '$begin_time' and f.create_time <= '$end_time' and f.loan_status = 9";
//		//获取前一天放款的借款
//		$total = Yii::$app->db->createCommand($sql_count)->queryAll();
		$where=[
				"AND",
				["BETWEEN",User_loan_extend::tableName(). ".last_modify_time", $begin_time,$end_time],
				['in' ,'business_type' , [1,4,5,6]],
				['=',User_loan_extend::tableName().'.status','SUCCESS'],
		];

		$total= User_loan::find()->joinWith('user',true,'LEFT JOIN')->joinWith('loanextend',true,'LEFT JOIN')->where($where)->count();
		//每100条处理一次
		$limit = 500;
		$pages = ceil($total / $limit );

		$this->log( "\n". date('Y-m-d H:i:s') . "......................");
		$this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");

		for( $i=0; $i < $pages; $i++ ){
//			$offset = $i * $limit;
//			$sql = "select l.current_amount,l.loan_id,u.user_id,u.from_code from yi_user_loan as l,yi_user as u,yi_user_loan_flows as f where f.loan_id=l.loan_id and (l.business_type IN(1,4,5,6)) and l.user_id=u.user_id and f.create_time >= '$begin_time' and f.create_time <= '$end_time' and f.loan_status = 9 order by f.create_time asc limit $limit offset $offset";
//			$loan_list = Yii::$app->db->createCommand($sql)->queryAll();
			$loan_list= User_loan::find()->joinWith('user',true,'LEFT JOIN')->joinWith('loanextend',true,'LEFT JOIN')->where($where)->offset($i*$limit)->limit($limit)->all();

			//如果没有用户，则直接结束
			if(empty($loan_list)){
				break;
			}

			foreach ($loan_list as $key=>$value){
				$user_id = $value['user_id'];

				$loguserloan=User_loan::find()->where(['user_id'=>$user_id,'status'=>[8,9,11,12,13]])->andWhere( ['<', 'loan_id',$value['loan_id']])->one();
				if($loguserloan){
                  continue;
				}
				if(!empty($value['user']['from_code'])){
					$userinfo_first = User::find()->select(array('status','user_id','is_webunion','from_code'))->where(['invite_code'=>(string)$value['user']['from_code']])->andWhere("user_id != $user_id")->one();
					$score_first = 10;
					$score_type = 3;
					$account = new Webunion_account();

					//父级用户获取0.5%的收益
					if(!empty($userinfo_first) && ($userinfo_first->status != 5) && ($userinfo_first->is_webunion == 'yes')){
						$profit_detail_count_first = Webunion_profit_detail::find()->where(['user_id'=>$userinfo_first->user_id,'type'=>$score_type,'profit_id'=>$value['loan_id']])->count();
						if($profit_detail_count_first == 0){
							$webunion_count_first = Webunion_account::find()->where(['user_id'=>$userinfo_first->user_id])->count();
							if($webunion_count_first == 0){
								$user_account_first = array(
										'user_id' => $userinfo_first->user_id
								
								);
								$result = $account->addAccount($user_account_first);
							}
							$total_history_interest_first = $value['current_amount']*0.005;
							//判断该笔借款是否是首贷
							$loan_info = User_loan::find()->where(['user_id' => $user_id, 'status' => 8, 'business_type' => [1,4,5,6]])->one();
							if(!empty($loan_info)){
								//不是首贷
								$sql_ret_first = "update ".Webunion_account::tableName()." set total_history_interest=total_history_interest+$total_history_interest_first,score=score+$score_first,last_modify_time='$nowtime',version=version+1 where user_id=".$userinfo_first->user_id;
								$ret_first = Yii::$app->db->createCommand($sql_ret_first)->execute();
							}else{
								//是首贷，发放10元冻结金额
								$sql_ret_first = "update ".Webunion_account::tableName()." set total_history_interest=total_history_interest+$total_history_interest_first,frozen_interest=frozen_interest+10,score=score+$score_first,last_modify_time='$nowtime',version=version+1 where user_id=".$userinfo_first->user_id;
								$ret_first = Yii::$app->db->createCommand($sql_ret_first)->execute();
								//首贷，添加一条冻结收益记录
								$ret_frozen_first = $this->addUserProfit($userinfo_first->user_id,$score_type,$value['loan_id'],10,2,1);
							}
							
							//添加两条收益明细
							$ret_check_first = $this->addUserProfit($userinfo_first->user_id,$score_type,$value['loan_id'],$value['current_amount']*0.005,2);
							$ret_score_first = $this->addUserProfit($userinfo_first->user_id,$score_type,$value['loan_id'],$score_first,3);
						}
					}
					
					//父父级用户获取0.003的收益
					if(!empty($userinfo_first['from_code'])){
						$user_second = User::find()->select(array('status','user_id','is_webunion','from_code'))->where(['invite_code'=>(string)$userinfo_first['from_code']])->andWhere("user_id != $user_id")->one();
							
						if(!empty($user_second) && ($user_second->status != 5) && ($user_second->is_webunion == 'yes')){
							$profit_detail_count_second = Webunion_profit_detail::find()->where(['user_id'=>$user_second->user_id,'type'=>$score_type,'profit_id'=>$value['loan_id']])->count();
							if($profit_detail_count_second == 0){
								$webunion_count_second = Webunion_account::find()->where(['user_id'=>$user_second->user_id])->count();
								if($webunion_count_second == 0){
									$user_account_second = array(
											'user_id' => $user_second->user_id
					
									);
									$result = $account->addAccount($user_account_second);
								}
								$total_history_interest_second = $value['current_amount']*0.003;
								$sql_ret_second = "update ".Webunion_account::tableName()." set total_history_interest=total_history_interest+$total_history_interest_second,score=score+$score_first,last_modify_time='$nowtime',version=version+1 where user_id=".$user_second->user_id;
								$ret_second = Yii::$app->db->createCommand($sql_ret_second)->execute();
								
								//添加两条收益明细
								$ret_check_second = $this->addUserProfit($user_second->user_id,$score_type,$value['loan_id'],$value['current_amount']*0.003,2);
								$ret_score_second = $this->addUserProfit($user_second->user_id,$score_type,$value['loan_id'],$score_first,3);
							}
						}	
					
						//父父父级用户获取0.001的收益
						if(!empty($user_second['from_code'])){
							$user_third = User::find()->select(array('status','user_id','is_webunion','from_code'))->where(['invite_code'=>(string)$user_second['from_code']])->andWhere("user_id != $user_id")->one();
							
							if(!empty($user_third) && ($user_third->status != 5) && ($user_third->is_webunion == 'yes')){
								$profit_detail_count_third = Webunion_profit_detail::find()->where(['user_id'=>$user_third->user_id,'type'=>$score_type,'profit_id'=>$value['loan_id']])->count();
								if($profit_detail_count_third == 0){
									$webunion_count_third = Webunion_account::find()->where(['user_id'=>$user_third->user_id])->count();
									if($webunion_count_third == 0){
										$user_account_third = array(
												'user_id' => $user_third->user_id
													
										);
										$result = $account->addAccount($user_account_third);
									}
									$total_history_interest_third = $value['current_amount']*0.001;
									$sql_ret_third = "update ".Webunion_account::tableName()." set total_history_interest=total_history_interest+$total_history_interest_third,score=score+$score_first,last_modify_time='$nowtime',version=version+1 where user_id=".$user_third->user_id;
									$ret_third = Yii::$app->db->createCommand($sql_ret_third)->execute();
									
									//添加两条收益明细
									$ret_check_third = $this->addUserProfit($user_third->user_id,$score_type,$value['loan_id'],$value['current_amount']*0.001,2);
									$ret_score_third = $this->addUserProfit($user_third->user_id,$score_type,$value['loan_id'],$score_first,3);
								}
								
							}
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