<?php
namespace app\modules\api\controllers\controllers314;

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

class MessageController extends ApiController
{
    public $enableCsrfValidation = false;
    public static $page = 1;
    public static $limit = 10;
    //消息列表
    public function actionIndex()
    {
    	$version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type');
        $page = Yii::$app->request->post('page');
        $limit = Yii::$app->request->post('limit',self::$limit);
        if($page==0 && $page !=''){
            $page = self::$page;
        }

        if($limit ==0 ){
            $limit = self::$limit;
        }

        if (empty($version) || empty($user_id) || empty($page) ) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $offset = ($page - 1) * $limit;

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

        //1:系统未读消息
        if($type==1){
            $sysmsg_count =(new SystemMessageList())->getSysmsgcount($user_id);
            $sysmsg_list =(new SystemMessageList())->getSysmsg($user_id,$offset,$limit);

            $array = ['sys_msg_count'=>$sysmsg_count,'list'=>$sysmsg_list];

            exit($this->returnBack('0000',$array));

        }

        //提醒类消息
        if($type == 2){
            $warnmsg_count = (new WarnMessageList())->getWarnmsgcount($user_id); //未读数量
            $warnmsg_list = (new WarnMessageList())->getWarnmsg($user_id,$offset,$limit); 
            $array = ['warn_msg_count'=>$warnmsg_count,'list'=>$warnmsg_list, 'notic_num'=>$warnmsg_count];

            exit($this->returnBack('0000',$array));
        }

    }

  



}
