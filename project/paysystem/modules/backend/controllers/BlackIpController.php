<?php

namespace app\modules\backend\controllers;

use Yii;
use app\models\BlackIp;
use yii\data\Pagination;
class BlackIpController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $pages = new Pagination([
            'totalCount' => BlackIp::find()->count(),
            'pageSize'   => '20'
        ]);
        $res   = BlackIp::find()
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
            if(empty($post)){
                return $this ->showMessage(10 , '数据错误' );
            }
            $isBlackIp = (new BlackIp())->isBlackIp($post['ip']);
            if (!$isBlackIp) {
                return $this ->showMessage(1 , '该IP已在黑名单中' );
            }
            $model = new BlackIp();
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
            $countInfo = BlackIp::find()->where(['ip' => $post['ip']])->andFilterWhere(['!=' , 'id',$post['id'] ])->count();
            if ($countInfo >= 1) {
                return $this ->showMessage(1, '该IP已在黑名单中');
            }
            $ipInfo = BlackIp::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = BlackIp::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            return $this->render('add' , [
                'post' => $ipInfo,
                'doType' => 'update',
            ]);
        }
    }
    
    public function actionDelete(){
        $id = $this -> post('id');
        if(intval($id) <=0){
            return $this ->showMessage(10, '数据错误' );
        }
        $info = (new BlackIp)->getById($id);
        if(empty($info)){
            return $this ->showMessage(1, '该IP不存在' );
        }
        
        $res = $info -> delete();
        if($res){
            return $this ->showMessage(0, '操作成功' );
        }else{
            return $this ->showMessage(2, '操作失败' );
        }
    }
}
