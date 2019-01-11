<?php

/**
 * 默认控制器
 * 登录与退出
 */

namespace app\modules\backend\controllers;

use app\common\Crypt3Des;
use app\common\Curl;
use app\models\Manager;
use Yii;

class DefaultController extends AdminController {

    public $vvars = [
        'menu' => 'study',
        'nav' =>'default',
    ];
    private $user_id;
    private $user;

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $this->user = $this->getUser();
        if ($this->user) {
            $this->user_id = $this->user->id;
        }
    }

    /**
     * 登陆授权
     */
    public function behaviors() {
        return [
        ];
    }

    /**
     * 系统首页
     */
    public function actionIndex() {
        return $this->render('index',[]);
    }

    /**
     * 登录
     */
    public function actionLogin() {
        $this->layout=false;
        if ($this->isPost()) {
            $username = $this->post('username');
            $password = $this->post('password');
            $oUser    = (new Manager)->getUserByUserName($username);
            if(empty($oUser)){
                return $this->showMessage(1, '用户不存在');
            }
            if($oUser -> status !=1 ){
                return $this->showMessage(2, '该用户状态异常');
            }
            $verifyPassword  = $oUser->verifyUserPassword($password);
            if (!$verifyPassword) {
                return $this->showMessage(3, '登录密码错误');
            }
            $oUser->logintime = date('Y-m-d H:i:s');
            $result           = $oUser->save();
            if(!$result){
                return $this->showMessage(4, '系统异常');
			}
            $result = Yii::$app->admin->login($oUser,86400);
			return $this->redirect('/backend/pay');
        }
        $this->view->title = "登录";
        return $this->render('login', [
        ]);
    }
    /**
     * 退出登录
     * @return [type] [description]
     */
    public function actionLogout()
    {
        Yii::$app->admin->logout();
        return $this->redirect('/backend/default/login');
    }
}
