<?php

namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\SystemMessageList;
use app\models\news\WarnMessageList;
use app\models\news\MessageApply;
use app\modules\api\common\ApiController;
use Yii;

class MsgdetailController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
    	
    	$version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $msg_id = Yii::$app->request->post('msg_id');
        $type = Yii::$app->request->post('type');

        if (empty($version) || empty($user_id) || empty($msg_id) || empty($type)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

         if (!preg_match("/^[1-9]\d*$/", $user_id)) {
            $array = $this->returnBack('99996');
            echo $array;
            exit;
        }

        $user = new User();
        $userinfo = $user->getUserinfoByUserId($user_id);
        //用户未注册，提示用户取注册
        if (empty($userinfo)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }


        //系统消息

        if($type==1){
        
	        $sysmsginfo = (new SystemMessageList())->getSysmsginfoByUserIdAndMsgId($user_id,$msg_id);
	        if (empty($sysmsginfo)) {
	            $array = $this->returnBack('99996');
	            echo $array;
	            exit;
	        }


	        $array = $this->getmsginfo($sysmsginfo,1);
	        if($sysmsginfo->read_status==0){
	        	  //更改消息读取状态
		         $condition = [
	                    'read_status' => 1,
	                ];
		    	$update_res = $sysmsginfo->update_info($condition);
	        }
	      

	        exit($this->returnBack('0000',$array));

        }

        //提醒消息
        if($type ==2 ){

        	$warnmsginfo = (new WarnMessageList())->getWarnmsginfoByUserIdAndMsgId($user_id,$msg_id);
	        if (empty($warnmsginfo)) {
	            $array = $this->returnBack('99996');
	            echo $array;
	            exit;
	        }


	       $array = $this->getmsginfo($warnmsginfo,2);
	       if($warnmsginfo->read_status ==0){
	       	//更改消息读取状态
	         $condition = [
                    'read_status' => 1,
                ];
	    	$update_res = $warnmsginfo->update_info($condition);
	       }
	       
	    	
	        exit($this->returnBack('0000',$array));
        }


    }

    private function getmsginfo($msginfo,$msg_type){
    			$array = array();
    	
	        	$array['title'] = $msginfo->title;
	        	$array['contact'] = $msginfo->contact;
	        	if($msg_type == 1){ //系统消息按send_time
                    $array['time'] = date('Y-m-d H:i',strtotime($msginfo->send_time));
                }else{ //提醒类消息按create_time
                    $array['time'] = date('Y-m-d H:i',strtotime($msginfo->create_time));
                }

	        
	        return $array;

    }
}
