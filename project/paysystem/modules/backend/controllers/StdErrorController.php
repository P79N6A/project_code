<?php

namespace app\modules\backend\controllers;

use Yii;
use app\models\StdError;
use yii\data\Pagination;
use app\models\Channel;
class StdErrorController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $pages = new Pagination([
            'totalCount' => StdError::find()->count(),
            'pageSize'   => '20'
        ]);
        $res   = StdError::find()
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();
        $channelinfo = (new Channel())->getChannel();
        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
                'channel' => $channelinfo
        ]);
    }

    public function actionAdd() {
        if ($this->isPost()) {
            $post = $this->post();
            if(empty($post)){
                return $this ->showMessage(10 , '数据错误' );
            }
            $where = [
                'AND',
                ['error_code' => $post['error_code']],
                ['res_code' => $post['res_code']],
                ['channel_id' => $post['channel_id']],
            ];
            $info = (new StdError())->getStdErrorInfo($where);
            if (!empty($info)) {
                return $this ->showMessage(1 , '该配置已存在' );
            }
            $model = new StdError();
            $res   = $model->createData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(2, '数据保存失败' );
            }
        }else{
            $channelinfo = (new Channel())->getChannel();
            return $this->render('add',['channelinfo'=> $channelinfo]);
        }
    }
    
    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $where = [
                'AND',
                ['channel_id' => $post['channel_id']],
                ['error_code' => $post['error_code']],
                ['res_code' => $post['res_code']],
                ['!=' , 'id',$post['id'] ]
            ];
            $info = (new StdError())->getStdErrorInfo($where);
            if (count($info) >= 1) {
                return $this ->showMessage(1, '该配置已存在' );
            }
            $ipInfo = StdError::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = StdError::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            $channelinfo = (new Channel())->getChannel();
            return $this->render('add' , [
                'post' => $ipInfo,
                'channelinfo' => $channelinfo,
                'doType' => 'update',
            ]);
        }
        
    }
    
}
