<?php

namespace app\modules\backend\controllers;

use app\models\ClientNotify;
use Yii;
use yii\data\Pagination;
use app\models\Payorder;
use yii\helpers\ArrayHelper;
class NotifyController  extends  AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $payorder_id = $this->get('payorder_id');
        $where = [];
        if(!empty($payorder_id)){
            $where['payorder_id']=$payorder_id;
        }
        $pages = new Pagination([
            'totalCount' => ClientNotify::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = ClientNotify::find()
            ->where($where)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();

        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
                'payorder_id'=>$payorder_id
        ]);
    }
    /**
     * Undocumented function
     * 后台发送通知
     * @return void
     */
    public function actionRenotify(){
        $notify_id =  $this->get('id');
        if(empty($notify_id)){
            return $this->redirect('index');
        }
        $oNotify = ClientNotify::findOne($notify_id);
        if(empty($oNotify)){
            return $this->redirect('index');
        }
        $oPayorder = Payorder::findOne($oNotify['payorder_id']);
        if (empty($oPayorder)) {
            return $this->redirect('index');
        }
        if (!$oPayorder['callbackurl']) {
            $ret = $oNotify->saveNotifyStatus(ClientNotify::STATUS_FAILURE);
            return $this->redirect('index');
        }
        $isNotify = $oPayorder->doClientNotify();
        if ($isNotify) {
            $nextStatus = ClientNotify::STATUS_SUCCESS;
        } else {
            $nextStatus = ClientNotify::STATUS_RETRY;
        }

        //4 保存状态
        $result = $oNotify->saveNotifyStatus($nextStatus);
        return $this->redirect('index');
    }
    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $nowTime = date('Y-m-d H:i:s');
            $notify_time = date('Y-m-d H:i:s',strtotime('+5minute'));
            $data['notify_num'] = intval(ArrayHelper::getValue($post, 'notify_num',0));
            if($data['notify_num']>7 || $data['notify_num']<0){
                return $this ->showMessage(1 , '通知次数设置错误' );
            }
            $data['notify_status'] = intval(ArrayHelper::getValue($post, 'notify_status',0));
            $data['notify_time'] = ArrayHelper::getValue($post, 'notify_time',$nowTime);
            /*if($data['notify_time']<$nowTime){
                $data['notify_time'] = $notify_time;
            }*/
            $notifyInfo = ClientNotify::findOne($post['id']);
            if($notifyInfo){
                $res = $notifyInfo->updateNotifyStatus($data);
            }else{
                $data['payorder_id'] = intval(ArrayHelper::getValue($post, 'payorder_id',0));
                if(!$data['payorder_id']){
                    return $this ->showMessage(1 , '支付订单号不能为空' );
                }
                $data['create_time'] = $nowTime;
                $res = (new ClientNotify())->addData($data);
            }
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $notifyInfo = ClientNotify::findOne($id);
            if (empty($notifyInfo)) {
                return $this->render('add');
            }
            return $this->render('add',[
                'post' => $notifyInfo,
                'doType' => 'update',
            ]);
        }
    }
}
