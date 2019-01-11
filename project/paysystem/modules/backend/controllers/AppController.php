<?php

namespace app\modules\backend\controllers;

use app\models\App;
use Yii;
use yii\data\Pagination;
class AppController  extends  AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $pay_chan = Yii::$app->request->get('pay_chan');
        $pages = new Pagination([
            'totalCount' => App::find()->count(),
            'pageSize'   => '20'
        ]);
        $res   = App::find()
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id asc')
            ->all();
       
        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
        ]);
    }
    
    public function actionAdd() {
        if ($this->isPost()) {
            $post = $this->post();
            if(empty($post)){
                return $this ->showMessage(10 , '数据错误' );
            }
            $model = new App();
            $res   = $model->createData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(2, '数据保存失败' );
            }
        }else{
            return $this->render('add');
        }
    }
    
    public function actionUpdate(){ 
        if ($this->isPost()) {
            $post = $this->post();
            if(empty($post)){
                return $this ->showMessage(10 , '数据错误' );
            }
            $appInfo = App::findOne($post['id']);
            $res   = $appInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $appInfo = App::findOne($id);
            if (empty($appInfo)) {
                return $this->redirect('index');
            }
            return $this->render('add' , [
                'post' => $appInfo,
                'doType' => 'update',
            ]);
        }
        
    }
}
