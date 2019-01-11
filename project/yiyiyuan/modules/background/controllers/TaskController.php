<?php

namespace app\modules\background\controllers;

use Yii;
use app\commands\SubController;
use app\models\dev\Userwx;
use app\models\dev\Webunion_account;
use app\models\dev\Webunion_profit_detail;
use app\models\dev\Webunion_user_list;
use app\models\dev\User;
use app\models\dev\User_auth;
use app\models\dev\Task;
use app\models\dev\Friends;
use app\models\dev\Account_settlement;
use app\models\dev\User_auth_relation;
use app\models\dev\Coupon_apply;
use app\commonapi\Common;

class TaskController extends SubController {

    public $layout = 'index_n';
    public $invest = array('', '1', '6');
    public $attestation = array('', '3', '8', '16', '28');

    private function getUser() {
        return Yii::$app->newDev->identity;
    }
    
    //任务列表
    public function actionIndex() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        //获得注册任务的完成情况
        $task_reg = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1, 'status' => 2])->count();
        //获得认证任务的完成情况
        $task_aut = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 2, 'status' => 2])->count();
        $returnUrl = '/background/default/index';
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "赚钱任务";
        return $this->render('index', [
                    'task_reg' => $task_reg,
                    'task_aut' => $task_aut,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //注册任务列表
    public function actionTaskreg() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        //获得注册任务的完成情况
        $task_reg = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1])->count();
        //如果是第一次点击进入，则接取任务
        if ($task_reg == 0) {
            $result1 = (new Task)->addTask($user->user_id, 1);
            $result2 = (new Task)->addTask($user->user_id, 2);
        }
        //获得当前邀请注册的人数
        $task_first = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1, 'step' => 1])->one();
        $finalcount = User::find()->where(['from_code' => $user->invite_code, 'status' => 3])->andWhere(['>', 'create_time', $task_first->create_time])->count();
        // 如果注册认证人数超过1个，则自动完成第一个任务，同时生成第二条任务记录
        if ($finalcount > 0) {
            if ($task_first->status != 2) {
                $task_first->status = 2;
                $task_first->save();
                $result = (new Task)->addTask($user->user_id, 1, 2);
            }
        }
        $returnUrl = '/background/task/index';
        $task_reg = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1, 'status' => 2])->count();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "赚钱任务";
        return $this->render('taskreg', [
                    'task_reg' => $task_reg,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //任务详情
    public function actionTaskdetail() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $step = (new Common)->get('step');
        if (!$step) {
            return $this->showMessage(1, '参数错误', 'html', '/background/task/taskreg');
        }
        //获得注册任务的完成情况
        $task_reg = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1, 'status' => 2])->count();
        // print_r($step."/".$task_reg);die;
        if ($task_reg >= $step) {
            return $this->redirect('/background/task/taskreg');
        }
        $viewName = 'taskdetail' . $step;
        //获得当前邀请注册的人数
        $task_first = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1, 'step' => 1])->one();
        $finalcount = User::find()->where(['from_code' => $user->invite_code, 'status' => 3])->andWhere(['>', 'create_time', $task_first->create_time])->count();
        $finalcount = $finalcount - $this->invest[$step - 1];

        $loanuserinfo = Userwx::find()->where(['openid' => $open_id])->asarray()->one();
        $time = time();
        $user_id = $user->user_id;
        $returnUrl = '/background/task/taskreg';
        $Url = urlencode(Yii::$app->request->hostInfo . "/background/default/spread1?u=" . $user_id . "&t=" . $time . "&s=" . md5($time . $user_id));
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        $jsinfo = $this->getWxParam();
        return $this->render($viewName, [
                    'step' => $step,
                    'count' => $finalcount,
                    'shareUrl' => $shareUrl,
                    'loanuserinfo' => $loanuserinfo,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //邀请任务领奖
    public function actionReceive() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        //判断是不是有参数
        $step = (new Common)->get('step');
        if (!$step) {
            return $this->showMessage(1, '参数错误', 'html', '/background/task/taskreg');
        }
        //判断当前用户是否已经领取过
        $count = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1, 'step' => $step, 'status' => 2])->count();
        if ($count) {
            return $this->showMessage(2, '请勿重复领取', 'html', '/background/task/taskreg');
        }
        //获得当前邀请注册的人数
        $task_first = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1, 'step' => 1])->one();
        $finalcount = User::find()->where(['from_code' => $user->invite_code, 'status' => 3])->andWhere(['>', 'create_time', $task_first->create_time])->count();
        //确定当前任务要完成的数量
        if ($step == 2) {
            $totalcount = 6;
            $amount = 5;
        } else {
            $totalcount = 16;
            $amount = 15;
        }
        //如果未完成，则提示错误
        if ($finalcount < $totalcount) {
            return $this->showMessage(3, '任务尚未完成，请完成任务后再试', 'html', '/background/task/taskreg');
        }
        //如果完成，则为用户增加收益，将当前任务状态改为已完成
        $account = Webunion_account::find()->where(['user_id' => $user->user_id])->one();
        $task = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1, 'step' => $step])->one();

        $account->total_history_interest += $amount;
        $account->version += 1;
        $account->last_modify_time = date('Y-m-d H:i:s');
        $account->save();
        $condition = array(
            'user_id' => $user->user_id,
            'type' => 7,
            'profit_id' => $task->id,
            'profit_amount' => $amount,
            'profit_type' => 2,
            'create_time' => date('Y-m-d H:i:s'),
            'last_modify_time' => date('Y-m-d H:i:s')
        );
        $profit_id = (new Webunion_profit_detail)->addProfit($condition);
        $task->status = 2;
        $task->source_id = $profit_id;
        $task->save();
        if ($step == 2) {
            $result = (new Task)->addTask($user->user_id, 1, 3);
        }
        return $this->redirect('/background/task/taskreg');
    }

    //认证任务列表
    public function actionTaskaut() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        //根据用户信息判断是否符合任务标准
        if ($user->status != 3) {
            return $this->showMessage(1, '您还没有通过身份认证，请前往先花一亿元补充个人资料及上传自拍照！', 'html', '/dev/account/peral?user_id=' . $user->user_id);
        }
        //获得认证任务的完成情况
        $task_aut = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 1])->count();
        //如果是第一次点击进入，则接取任务
        if ($task_aut == 0) {
            $result1 = (new Task)->addTask($user->user_id, 1);
            $result2 = (new Task)->addTask($user->user_id, 2);
        }
        //获得当前完成认证的人数
        $task_first = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 2, 'step' => 1])->one();
        $count = User_auth::find()->where(['user_id' => $user->user_id])->andWhere(['>', 'create_time', $task_first->create_time])->count();
        $returnUrl = '/background/task/index';
        $task_aut = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 2, 'status' => 2])->count();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "赚钱任务";
        return $this->render('taskaut', [
                    'count' => $count,
                    'task_aut' => $task_aut,
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    //认证任务详情
    public function actionAutdetail() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $step = (new Common)->get('step');
        if (!$step) {
            return $this->showMessage(1, '参数错误', 'html', '/background/task/taskaut');
        }
        $counts = array('', '3', '5', '8', '12', '15');
        $coupon = array('', '15', '25', '50', '75', '100');
        $coupon = $coupon[$step];
        $goal = $counts[$step];
        //获得认证任务的完成情况
        $task_aut = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 2, 'status' => 2])->count();
        if ($task_aut >= $step) {
            return $this->redirect('/background/task/taskaut');
        }
        //获取用户的认证数量
        $task_first = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 2, 'step' => 1])->one();
        $relation_count = User_auth::find()->where(['user_id' => $user->user_id])->andWhere(['>', 'create_time', $task_first->create_time])->count();
        $relation_count = $relation_count - $this->attestation[$step - 1];

        $loanuserinfo = Userwx::find()->where(['openid' => $open_id])->asarray()->one();
        $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invitation/cash?wid=" . $loanuserinfo['id']);
        // $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invitation/cash?wid=");
        $returnUrl = '/background/task/taskaut';
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "赚钱任务";
        return $this->render('autdetail', [
                    'coupon' => $coupon,
                    'step' => $step,
                    'count' => $relation_count,
                    'goal' => $goal,
                    'jsinfo' => $jsinfo,
                    'loanuserinfo' => $loanuserinfo,
                    'shareUrl' => $shareUrl,
                    'returnUrl' => $returnUrl
        ]);
    }

    //认证任务领取优惠券
    public function actionGetcoupon() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $step = (new Common)->get('step');
        if (!$step) {
            return $this->showMessage(1, '参数错误', 'html', '/background/task/taskaut');
        }
        $counts = array('', '3', '5', '8', '12', '15');
        $coupon = array('', '15', '25', '50', '75', '100');
        $coupon = $coupon[$step];
        $goal = $counts[$step];
        //获取当前用户信息
        //判断当前任务是否已经领取过奖励
        $finaltask = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 2, 'step' => $step, 'status' => 2])->count();
        if ($finaltask) {
            return $this->showMessage(3, '请勿重复领取', 'html', '/background/task/taskaut');
        }
        //获取用户的认证数量
        $task_first = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 2, 'step' => 1])->one();
        $relation_count = User_auth::find()->where(['user_id' => $user->user_id])->andWhere(['>', 'create_time', $task_first->create_time])->count();
        $relation_count = $relation_count - $this->attestation[$step - 1];
        if ($relation_count < $goal) {
            return $this->showMessage(2, '任务尚未完成，请完成任务后再试', 'html', '/background/task/taskaut');
        }
        $title = $coupon . "元优惠券";
        //获取进行中任务的信息
        $task = Task::find()->where(['user_id' => $user->user_id, 'source_type' => 2, 'step' => $step])->one();
        //给用户发送优惠券
        $id = (new Coupon_apply)->sendcoupon($user->user_id, $title, 1, 30, $coupon);
        $task->status = 2;
        $task->source_id = $id;
        $task->save();
        if ($step < 5) {
            $result = (new Task)->addTask($user->user_id, 2, $step + 1);
        }
        return $this->redirect('/background/task/taskaut');
    }

    public function actionProfit() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $loanuserinfo = Userwx::find()->where(['openid' => $open_id])->asarray()->one();
        $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invest/index");
        // $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invitation/cash?wid=");
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "赚钱任务";
        return $this->render('profit', [
                    'jsinfo' => $jsinfo,
                    'loanuserinfo' => $loanuserinfo,
                    'shareUrl' => $shareUrl
        ]);
    }

    /**
     * 显示结果信息
     * @param $res_code 错误码0 正确  | >0错误
     * @param $res_data      结果   | 错误原因
     */
    public function showMessage($res_code, $res_data, $type = null, $redirect = null) {
        // 自动判断返回类型
        if (empty($type)) {
            $type = Yii::$app->request->getIsAjax() ? 'json' : 'html';
        }
        $type = strtoupper($type);

        // 返回结果: 统一json格式或消息提示代码
        switch ($type) {
            case 'JSON':
                return json_encode([
                    'res_code' => $res_code,
                    'res_data' => $res_data,
                ]);
                break;

            default:
                $redirect = is_null($redirect) ? Yii::$app->request->getReferrer() : $redirect;
                $this->view->title = '错误提示';
                return $this->render('/showmessage', [
                            'res_code' => $res_code,
                            'res_data' => $res_data,
                            'redirect' => $redirect,
                ]);
                break;
        }
    }

}
