<?php
namespace app\modules\api\controllers\controllers311;

use app\common\Logger;
use app\models\news\ApplicationList;
use app\models\news\User;
use app\modules\api\common\ApiController;
use app\commonapi\Apidepository;
use app\commonapi\Apihttp;
use Yii;

class ApplicationController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        //[{"mobile":11,"content":11,"status":1,"send_time":"2018-10-09 10:32:13"},{"mobile":22,"content":22,"status":2,"send_time":"2018-10-09 10:32:13"}]
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type');
        if (empty($user_id) && empty($type)) {
            exit($this->returnBack('99994'));
        }
        $fuser = User::findOne($user_id);
        if (empty($fuser)) {
            exit($this->returnBack('10001'));
        }
        if($type==1){
            //短信内容
            $smslist = Yii::$app->request->post('smslist');
            if (empty($smslist)) {
                exit($this->returnBack('99994'));
            }
            $date=json_decode($smslist,true);
            if(empty($date)){
                exit($this->returnBack('99994'));
            }
            $content=$smslist;
        }else{
            $applist = Yii::$app->request->post('applist');
            if (empty($applist)) {
                exit($this->returnBack('99994'));
            }
            $content=$applist;
        }
        if(!empty($content)){
            $condition=[
                'user_id'=>$user_id,
                'type'=>$type,
                'content'=>$content,
            ];
            $oApplication=ApplicationList::find()->where(['user_id'=>$user_id,'type'=>$type])->one();
            if (empty($oApplication)) {
                $res= (new ApplicationList())->save_address($condition);
            } else {
                $res = $oApplication->update_address($condition);
            }
            if (empty($res)) {
                Logger::dayLog('app/application', '记录失败', $condition);
                exit($this->returnBack('10042'));
            }
        }
        if($type ==2  && !empty($content)){
            $this->appRequest($fuser,$content); //把applist值传输给开放平台
        }
        if($type == 1 && !empty($content)){
            $this->appRequestOpen($fuser,$content);
        }
        exit($this->returnBack('0000'));
    }
    
    /**
     * 把app应用列表发送给第三方
     * @param type $fuser
     * @param type $content
     */
    public function appRequest($fuser,$content){
          $params = [
                'mobile'  => $fuser->mobile,
                'time'    => date('Y-m-d H:i:s'),
                'applist' => $content,
            ];
            $apiDep = new Apidepository();
            $ret_open = $apiDep->application($params);
    }
    /**
     * 短信列表发送给开放平台
     * @param type $fuser
     * @param type $content
     */
    public function appRequestOpen($fuser,$content){
        $params = [
            'mobile'  => $fuser->mobile,
            'msg_list' => $content,
        ];
        $sendRes = (new Apihttp())->postSmsList($params);
        return $sendRes;
    }
}
