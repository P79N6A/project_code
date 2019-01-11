<?php
/**
 * 维克流量充值接口异步通知
 */
namespace app\modules\api\controllers;

use Yii;
use app\modules\api\common\ApiController;
use app\common\Logger;
use app\common\Http;
use app\models\Flow;

class VikebackController extends ApiController
{
	public function init(){
		//parent::init(); 千万不要执行父类的验证方法
	}
	
	/**
	 * 回调
	 */
 	public function actionCallurl()
    {
		$result_post = file_get_contents('php://input');
    	// 1 保存回调的数据
		Logger::dayLog(
			'vikeback',
			'POST',$result_post
		);
    	//2 解密
		try{
			$decrypt = json_decode($result_post);
		}catch(\Exception $e){
			Logger::dayLog(
				'vikeback',
				'info', '解密失败' ,
				'base64', $result_post
			);
			return "解密失败";
		}
		
		$exec_result = $decrypt->exec_result;
		$msg_id = $decrypt->msg_id;
		$result_code = $decrypt->result_code;
		
		//修改流量充值表的状态
		$ret = $this->updateFlow($msg_id, $exec_result);
		if($ret){
			//向一亿元推送充值结果
			$url = "http://weixin.xianhuahua.com/background/webunion/lweb";
			$data = 'msg_id='.$msg_id.'&exec_result='.$exec_result.'&result_code='.$result_code;
			$result = Http::interface_post($url, $data);
			
			echo 'OK';
			eixt;
			
		}else{
			echo 'fail';
			exit;
		}
    }
    
    
    /**
     * 修改流量充值表中的状态
     */
    private function updateFlow($msg_id, $exec_result){
    	
    	$flow = Flow::find()->where(['flow_id'=>$msg_id])->one();
    	if(!empty($flow)){
    		if($exec_result == 0){
    			$flow->status = 'SUCCESS';
    			$flow->last_modify_time = date('Y-m-d H:i:s');
    		}else{
    			$flow->status = 'FAIL';
    			$flow->last_modify_time = date('Y-m-d H:i:s');
    		}
    		
    		if($flow->save()){
    			return true;
    		}else{
    			return false;
    		}	
    	}else{
    		return false;
    	}
    }

}
