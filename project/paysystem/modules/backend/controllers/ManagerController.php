<?php

namespace app\modules\backend\controllers;

use app\models\Manager;
use Yii;
use yii\data\Pagination;

class ManagerController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $get  = $this -> get();
        $inputArray = ['username','realname'];  //允许搜索的值
        $where = [];
        if(!empty($get)){
            $where[] = 'AND';
            foreach($get as $k => $v){
                if($v!='' && in_array($k, $inputArray)){
                    $where[] = [$k => $v];
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => Manager::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = Manager::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();
        return $this->render('index', [
                'get'   => $get,
                'res'   => $res,
                'pages' => $pages,
        ]);
    }

    public function actionAdd() {
        if ($this->isPost()) {
            $post = $this->post();
            $info = (new Manager())-> getUserByUserName($post['username'],$post['type']);
            if (!empty($info)) {
                return $this ->showMessage(1 , '该用户已存在' );
            }
            $model = new Manager();
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
            $oManager = new Manager;
            $countInfo = $oManager ->getUserInfo($post);
            if ($countInfo >= 1) {
                return $this ->showMessage(1, '该用户已存在' );
            }
            if(isset($post['password']) && empty($post['password'])){
                unset($post['password']);
            }
            $ipInfo = Manager::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = Manager::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            return $this->render('add' , [
                'post' => $ipInfo,
                'doType' => 'update',
            ]);
        }
        
    }
    

    /*
     * 禁用和启用
     */

    public function actionStatus() {
        $id = intval(Yii::$app->request->get("id"));
        if ($id <= 0) {
            return false;
        }

        $info = (new Manager)->getById($id);
        if (empty($info)) {
            return false;
        }

        if ($info->status === 1) {
            $info->status = 2;
        } else if ($info->status === 2) {
            $info->status = 1;
        }
        $res = $info->save();
        $this->redirect("/backend/manager");
    }
    
    private function setPassword($type = 1){
        $user = $this->getUser();
        if(empty($user)){
            return $this->redirect('index');
        }
        $Info = Manager::findOne($user->id);
        if (empty($Info)) {
            return $this->redirect('index');
        }
        $post = $this->post();
        if(empty($post)){
            return $this ->showMessage(10, '数据错误' );
        }
        if(empty($post['password']) || $post['password'] != $post['re_password']){
            return $this ->showMessage(1, '两次密码不一致' );
        }
        if($type==1){  //修改密码要校验原密码是否正确 重置不检验
            $verifyPassword = $Info ->verifyUserPassword($post['old_password']);
            if(!$verifyPassword){
                return $this ->showMessage(2, '原密码不正确' );
            }
        }
        $res   = $Info->updatePassword($post['password']);
        if ($res) {
            return $this ->showMessage(0 , '操作成功' );
        } else {
            return $this ->showMessage(1, '数据保存失败' );
        }
    }


    /*
     * 修改或重置密码
     */
    public function actionUpdatePassword(){
        if ($this->isPost()) {
            return $this -> setPassword();
        }else{
            return $this->render('update-password');
        }
    }
    
    /*
     * 重置密码
     */
    public function actionResetPassword(){
        if ($this->isPost()) {
            return $this -> setPassword(2);
        }else{
            return $this->render('reset-password');
        }
    }
}
