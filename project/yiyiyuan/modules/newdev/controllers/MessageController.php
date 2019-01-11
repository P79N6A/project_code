<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Apihttp;
use app\commonapi\Common;
use app\commonapi\Crypt3Des;
use app\commonapi\ErrorCode;
use app\models\news\Cg_remit;
use app\models\news\Common as Common2;
use app\models\news\SystemMessageList;
use app\models\news\WarnMessageList;
use Yii;

class MessageController extends NewdevController {

    public function actionIndex(){
        $this->layout = false;
        $this->getView()->title = "公告列表";
        $user = $this->getUser();
        $unread_system_message_count = SystemMessageList::find()->where(['user_id' => $user->id,'read_status' => 0])->andWhere(['<','send_time',date('Y-m-d H:i:s')])->count();
        $unread_warning_message_count = WarnMessageList::find()->where(['user_id'=>$user->id,'read_status'=>0,'status'=>[2,3], 'is_show' => 1])->count();
        return $this->render('index',[
                'csrf' => $this->getCsrf(),
                'unread_system_message_count' => $unread_system_message_count,
                'unread_warning_message_count' => $unread_warning_message_count,
                'jsinfo' => $this->getWxParam()
            ]);
    }

    public function actionMessagelist(){
        if(!Yii::$app->request->isPost){
            $array = $this->errorreback('99997');
            return json_encode($array);
        }

        $user = $this->getUser();

        $limit = $this->post('limit',10);
        $page = $this->post('page',1);
        $type = $this->post('type',1);
        $offset = ($page-1)*$limit;

        //系统通知
        if($type == 1){
            $message_list = SystemMessageList::find()
                ->select('id,title,contact,read_status,send_time')
                ->where(['user_id'=>$user->id])
                ->andWhere(['<','send_time',date('Y-m-d H:i:s')])
                ->offset($offset)
                ->limit($limit)
                ->orderBy('send_time desc')
                ->asArray()
                ->all();
        }

        //消息提醒
        if($type == 2){
            $message_list = WarnMessageList::find()
                ->select('id,title,contact,read_status,last_modify_time')
                ->where(['user_id'=>$user->id,'status'=>[2,3],'is_show'=>1])
                ->offset($offset)
                ->limit($limit)
                ->orderBy('last_modify_time desc')
                ->asArray()
                ->all();
        }

        foreach($message_list as $key => $value){
            if(mb_strlen($message_list[$key]['title']) > 9){
                $message_list[$key]['title'] = mb_substr($value['title'],0,9,'utf-8').'...';
            }
            $message_list[$key]['contact'] = mb_substr($value['contact'],0,35,'utf-8');
            if($type == 1){
                $message_list[$key]['send_time'] = mb_substr($value['send_time'],0,16);
            }else{
                $message_list[$key]['last_modify_time'] = mb_substr($value['last_modify_time'],0,16);
            }
        }

        $array = $this->errorreback('0000');
        $array['rsp_data'] = array('message_list' => $message_list);
        return json_encode($array);
    }

    public function actionInfo(){
        $this->layout = false;
        $this->getView()->title = "公告";
        $type = $this->get('type');
        $id = $this->get('id');
        if(empty($id) || empty($type)){
            return $this->redirect('/new/message');
        }

        $user = $this->getUser();

        //系统通知
        if($type == 1){
            $message = SystemMessageList::find()->where(['id' => $id,'user_id' => $user->id])->one();

        }
        //消息提醒
        if($type == 2){
            $message = WarnMessageList::find()->where(['id' => $id,'user_id' => $user->id])->one();
        }

        if(empty($message)){
            return $this->redirect('/new/message');
        }

        if($message->read_status == 0){
            $message->read_status = 1;
            $message->save();
        }

        return $this->render('info',[
                'message' => $message,
                'jsinfo' => $this->getWxParam()
            ]);
    }

    /**
     * 获取msg
     * @param $code
     * @param string $msg
     * @return mixed
     */
    private function errorreback($code, $msg = '') {
        $errorCode = new ErrorCode();
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = !empty($msg) ? $msg : $errorCode->geterrorcode($code);
        return $array;
    }

    /**
     * 获取csrf
     * @return string
     */
    public function getCsrf() {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

}
