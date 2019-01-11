<?php

namespace app\modules\backend\controllers;

use Yii;
use app\models\Business;
use yii\data\Pagination;
use app\models\App;
class BusinessController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $pages = new Pagination([
            'totalCount' => Business::find()->count(),
            'pageSize'   => '20'
        ]);
        $res   = Business::find()
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();
        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
        ]);
    }

    public function actionAdd() {
        
        if ($this->isPost()) {
            $post = $this->post();
            $info = (new Business())-> findByCode($post['business_code']);
            if (!empty($info)) {
                return $this ->showMessage(1 , '该业务号已存在' );
            }
            $model = new Business();
            $res   = $model->createData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(2, '数据保存失败' );
            }
        }else{
            $appinfo = (new App())->getApp();
            return $this->render('add',['appinfo'=> $appinfo]);
        }
    }
    
    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $ipInfo = Business::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $appinfo = (new App())->getApp();
            $id = Yii::$app->request->get('id');
            $ipInfo = Business::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            return $this->render('add' , [
                'post' => $ipInfo,
                'doType' => 'update',
                'appinfo' => $appinfo,
            ]);
        }
        
    }
    
    /*
     * $data   渲染页面表单数据
     * $page   渲染页面名称
     * $msg    渲染页面错误信息
     * $dpType 当前操作action名称
     */
    private function showErrorMsg($data, $page, $msg , $doType='') {
        return $this->render($page, [
            'msg'    => $msg,
            'post'   => $data,
            'doType' => $doType
        ]);
    }

    /*
     * 禁用和启用
     */

    public function actionStatus() {
        $id = intval(Yii::$app->request->get("id"));
        if ($id <= 0) {
            return false;
        }

        $info = Business::findOne($id);
        if (empty($info)) {
            return false;
        }

        if ($info->status === 1) {
            $info->status = 0;
        } else if ($info->status === 0) {
            $info->status = 1;
        }
        $res = $info->save();
        $this->redirect("/backend/white-ip");
    }

}
