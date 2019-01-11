<?php
/**
 * 普罗米推送消息和获取消息结果
 */
/**
 *   linux : /data/wwwroot/yiyiyuan/yii promes sent
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii promes sent
 */
namespace app\commands;
use Yii;
use app\commands\BaseController;
use app\models\BaseModel;
use app\models\dev\User_loan;
use app\models\dev\User_loan_extend;
use app\models\dev\User_loan_flows;
use app\models\dev\User;
use app\models\dev\Promes;
use app\models\Flow;
use app\models\dev\White_list;
use yii\helpers\ArrayHelper;
use app\commonapi\Logger;

class PromesController extends BaseController {
	//private $today;
	
	/**
	 * 初始化
	 */
	public function init() {
		parent::init();
	}
	/**
	 * 5分钟轮询
	 * 推送普罗米数据
	 */
	public function sent() {
		//1 获取普罗米的数据
		$now = time();
		$dataStart = date('Y-m-d H:i:00', $now-36000);
		$dataEnd = date('Y-m-d H:i:00', $now-600);

		$where = [
			'AND',
			['status' => 5],
			['business_type'=> [1,4,5,6]],
			['prome_status' => 10],
			['>=', 'create_time', $dataStart], 
			['<', 'create_time', $dataEnd], 
		];
		$data = User_loan::find()->where($where)->limit(100)->all();
		$nums = is_array($data) ? count($data) : 0;
		Logger::dayLog('promes', 'sent', $dataStart . ' to ' . $dataEnd , '1 获取user_loan条数', $nums);
		if(empty($data)){
			return 0;
		}

		//2 锁定状态,避免下次重复处理
		$loan_ids = ArrayHelper::getColumn($data, 'loan_id');
		$nums = User_loan::updateAll(['prome_status'=>1, 'last_modify_time'=>date('Y-m-d H:i:s')],['loan_id'=>$loan_ids]);
		Logger::dayLog('promes', 'sent', $dataStart . ' to ' . $dataEnd , '2 锁定user_loan.prome_status=1条数', $nums);

		//3 放入普罗米
		$userData = [];
		foreach ($data as $v) {
			//是否复贷
			$where = [
				'AND',
				['user_id' => $v['user_id']],
				['loan_id'=> $v['loan_id']],
				['>', 'success_num', 0] 
			];
			$succLoan = User_loan_extend::find()->where($where)->count();
			$type = $succLoan > 0 ? 2 : 1 ;
			$userData[] = [
				'user_id' => $v['user_id'],
				'loan_id' => $v['loan_id'],
				'type' => $type
			];
		}
		$nums = Promes::addBatchByUsers($userData);
		
		//4 写日志
		Logger::dayLog('promes', 'sent', $dataStart . ' to ' . $dataEnd , '3插入到promes条数', $nums);
		return $nums;
	}
	/**
	 * 10分钟轮询
	 * 释放被自动驳回的数据
	 */
	public function releaseReject() {
		//1 获取驳回的数据
		$now = time();
		$dataStart = date('Y-m-d H:i:00', $now-3600*10);//从24小时前
		$dataEnd = date('Y-m-d H:i:00', $now-3600*6);//到6小时前

		$where = [
			'AND',
			['status' => [3,7]],//驳回状态
			['business_type'=> [1,4,5,6]],
			['prome_status' => 1],//反欺诈之后
			['>=', 'create_time', $dataStart], 
			['<', 'create_time', $dataEnd], 
		];
		$data = User_loan::find()->where($where)->limit(1000)->orderBy('create_time')->all();
		$nums = is_array($data) ? count($data) : 0;
		Logger::dayLog('promes', 'releaseReject', $dataStart . ' to ' . $dataEnd , '1 获取user_loan条数', $nums);
		if(empty($data)){
			return 0;
		}

		//2 驳回状态，超过六小时的直接释放
		$loan_ids = ArrayHelper::getColumn($data, 'loan_id');
		$nums = User_loan::updateAll(['prome_status'=>5, 'last_modify_time'=>date('Y-m-d H:i:s')],['loan_id'=>$loan_ids]);
		Logger::dayLog('promes', 'releaseReject', $dataStart . ' to ' . $dataEnd , '2 锁定user_loan.prome_status=5条数', $nums);

		return $nums;
	}

	/**
	 * 每5分钟轮询
	 * 获取普罗米跑完的数据
	 * yii promes releasePass
	 */
	public function releasePass() {
		//1 获取自动化的数据
		$oAntiAuto = new Promes;
		$result_time = date('Y-m-d H:i:00'); //当前分钟之前 
		$normals = $oAntiAuto -> getNormal($result_time);
		$nums = is_array($normals) ? count($normals) : 0;
		Logger::dayLog('promes', 'releasePass', $result_time. ' 前1小时内', '1 从promes获取正常条数', $nums);
		if(empty($normals)){
			return 0;
		}

		//2 锁定状态,避免下次重复处理
		$ids = ArrayHelper::getColumn($normals, 'id');
		$nums = Promes::updateAll(['prome_status'=>4,'modify_time'=>date('Y-m-d H:i:s')],['id'=>$ids]);
		Logger::dayLog('promes', 'releasePass', $result_time. ' 前1小时内', '2 锁定promes.prome_status=4条数', $nums);

		//3 根据普罗米结果进行处理
		$nums = $this->updateData($normals);
		Logger::dayLog('promes', 'releasePass', $result_time. ' 前1小时内',  '3 普罗米结束的数据: user_loan.prome_status=5条数', $nums);
		
		//4 结束自动化整体流程
		$nums = Promes::updateAll(['prome_status'=>5,'modify_time'=>date('Y-m-d H:i:s')],['id'=>$ids]);
		Logger::dayLog('promes', 'releasePass', $result_time. ' 前1小时内', '4 终止promes.prome_status=5条数', $nums);
		return $nums;
	}

	
	/**
	 * 进行释放操作，进入人工信审
	 * @param  [type] $loan_id [description]
	 * @return [type]          [description]
	 */
	private function updateData($normals){
		// 修改prome_status = 10 进入prome
		$loan_ids = ArrayHelper::getColumn($normals, 'loan_id');
		$loan_ids = array_unique($loan_ids);
		$ups = ['prome_status'=>20,'last_modify_time'=>date('Y-m-d H:i:s')];
		$where =  ['loan_id'=>$loan_ids, 'prome_status'=>1, 'status'=>5];
		$nums = User_loan::updateAll($ups, $where);
		return $nums;
	}

	
}
