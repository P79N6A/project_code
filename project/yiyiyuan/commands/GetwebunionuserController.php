<?php

/**
 * 获取以前标注的网盟用户
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
use app\models\dev\User;
use Yii;
use yii\console\Controller;
use app\models\dev\Webunion_profit_detail;


class GetwebunionuserController extends Controller {

	// 命令行入口文件
	public function actionIndex() {
		//查询上线开始时间和当前截止时间的网盟用户数
		$begin_time = date('Y-m-d 00:00:00', (time()-24*3600));
		$end_time = date('Y-m-d H:i:s');
		$condition = "create_time >= '$begin_time' and create_time < '$end_time'";
		$total = 0;
		
		//获取网盟用户总数
		$total = User::find()->where($condition)->count();
		
		//每100条处理一次
		$limit = 100;
		$pages = ceil( $total / $limit );
		
		$this->log( "\n". date('Y-m-d H:i:s') . "......................");
		$this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");
		
		for( $i=0; $i < $pages; $i++ ){
			//分别查询每个用户对应的上级，上上级，上上上级用户
			$user_list = User::find()->select(array('user_id','from_code'))->where($condition)->offset($i * $limit)->limit($limit)->all();
			//如果没有用户，则直接结束
			if(empty($user_list)){
				break;
			}
			
			//$this->log("处理范围" . ($i * $limit). ' -- ' . ($i * $limit + $limit) );
			foreach ($user_list as $key=>$value){
				//查询该用户在网盟用户表中是否存在
				$this->checkWebUnionIsExist($value['user_id'], 0, 0, 0);
				
				if(!empty($value['from_code'])){
					//查询一级好友
					$user_list_first = User::find()->select(array('user_id','from_code'))->where(['invite_code'=>(string)$value['from_code']])->andWhere("user_id != '".$value['user_id']."'")->all();
					
					if(!empty($user_list_first)){
						foreach ($user_list_first as $k=>$v){
							//查询该用户在网盟用户表中是否存在
							$this->checkWebUnionIsExist($value['user_id'], $v['user_id'], $v['user_id'], 1);
							
							if(!empty($v['from_code'])){
								//查询二级好友
								$user_list_second = User::find()->select(array('user_id','from_code'))->where(['invite_code'=>(string)$v['from_code']])->andWhere("user_id != '".$v['user_id']."'")->all();
								if(!empty($user_list_second)){
									foreach ($user_list_second as $k_second=>$v_second){
										
										//查询该用户在网盟用户表中是否存在
										$this->checkWebUnionIsExist($value['user_id'], $v['user_id'], $v_second['user_id'], 2);
										
										if(!empty($v_second['from_code'])){
											//查询三级好友
											$user_list_third = User::find()->select(array('user_id','from_code'))->where(['invite_code'=>(string)$v_second['from_code']])->andWhere("user_id != '".$v_second['user_id']."'")->all();
											
											if(!empty($user_list_third)){
												foreach ($user_list_third as $k_first=>$v_third){
													//查询该用户在网盟用户表中是否存在
													
													$this->checkWebUnionIsExist($value['user_id'], $v['user_id'], $v_third['user_id'], 3);
												}
											}
											else{
												continue;
											}
										}
									}
								}else{
									continue;
								}
							}
						}
					}else{
						continue;
					}
				}
			}
		}
	}
	
	//查询该用户是否保存
	private function checkWebUnionIsExist($user_id,$parent_user_id,$top_user_id,$type){
		
		$user_count = Webunion_user_list::find()->where(['user_id'=>$user_id,'parent_user_id'=>$parent_user_id,'top_user_id'=>$top_user_id,'type'=>$type])->count();
		
		$result = $this->addWebUnionUser($user_count,$user_id, $parent_user_id, $top_user_id, $type);
		if(!$result){
			$this->log("\n网盟用户保存失败: id={$user_id}");
		}
	}
	
	//添加新的网盟用户
	private function addWebUnionUser($count,$user_id,$parent_user_id,$top_user_id,$type){

		if($count == 0){
			$webunion = new Webunion_user_list();
			$user_array = array(
					'user_id' => $user_id,
					'parent_user_id' => $parent_user_id,
					'top_user_id' => $top_user_id,
					'type' => $type
			);
			$transaction = Yii::$app->db->beginTransaction();
			$webunion_id = $webunion->addUser($user_array);
			if($webunion_id){
				//生成一个网盟用户的账户
				$account_count = Webunion_account::find()->where(['user_id'=>$user_id])->count();
				if($account_count == 0){
					$account = new Webunion_account();
					$user_account = array(
						'user_id' => $user_id
					);
					$result = $account->addAccount($user_account);
					if($result){
						//给该用户的父级用户5M流量和1个积分
						//$ret = $account->setAccountinfo($parent_user_id, $webunion_account);
						$sql_ret = "update ".Webunion_account::tableName()." set total_history_flow=total_history_flow+5,score=score+1 where user_id=".$user_id;
						$ret = Yii::$app->db->createCommand($sql_ret)->execute();
						//添加两条收益明细
						$ret_register = $this->addUserProfit($user_id,6,0,5,1);
						$ret_score = $this->addUserProfit($user_id,1,0,1,3);
						$transaction->commit();
						return $result;
					}else{
						$transaction->rollBack();
						return false;
					}
				}else{
					if($type != 0){
						$account_count_parent = Webunion_account::find()->where(['user_id'=>$parent_user_id])->count();
						if($account_count_parent == 0){
							$account = new Webunion_account();
							$user_account_parent = array(
									'user_id' => $parent_user_id
							);
							$result = $account->addAccount($user_account_parent);
						}
						
						$profit_detail_statistics = Webunion_profit_detail::find()->where(['user_id'=>$parent_user_id,'type'=>1,'profit_id'=>$user_id,'profit_type'=>1])->one();
						if(empty($profit_detail_statistics)){
							//给该用户的父级用户5M流量和1个积分
							//$ret = $account->setAccountinfo($parent_user_id, $webunion_account);
							$sql_ret = "update ".Webunion_account::tableName()." set total_history_flow=total_history_flow+5,score=score+1 where user_id=".$parent_user_id;
							$ret = Yii::$app->db->createCommand($sql_ret)->execute();
							//添加两条收益明细
							$ret_register = $this->addUserProfit($parent_user_id,1,$user_id,5,1);
							$ret_score = $this->addUserProfit($parent_user_id, 1, $user_id, 1, 3);
						}
					}
					$transaction->commit();
					return true;
				}
			}else{
				$transaction->rollBack();
				return false;
			}
		}else{
			return true;
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