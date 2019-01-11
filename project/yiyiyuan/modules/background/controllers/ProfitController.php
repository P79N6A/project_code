<?php

namespace app\modules\background\controllers;

use app\commands\SubController;
use app\models\dev\User;
use app\models\dev\Webunion_account;
use app\models\dev\Webunion_profit_detail;
use Yii;

if (!class_exists('AxisPrototype')) {
    include '../jpgraph/jpgraph.php';
}
if (!class_exists('LinePlot')) {
    include '../jpgraph/jpgraph_line.php';
}

class ProfitController extends SubController {

    public $layout = 'webunion';
    public $enableCsrfValidation = false;

    /**
     * 获取帐号
     */
    private function getUser() {
        //return User::findOne(517);//@todo
        $open_id = $this->chkOpenId();
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        if (!$user) {
            echo 'access forbidden';
            exit;
            exit;
        }
        return $user;
    }

    /**
     * 获取 openid
     */
    private function chkOpenId() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            header('Location:https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
            exit;
        }
        return $open_id;
    }

    /**
     * 钱包明细
     */
    public function actionIndex() {
        //1 会员信息
        $user = $this->getUser();
        $user_id = $user->user_id;
        $oDetail = new Webunion_profit_detail;

        //2 统计信息
        $qianbao = Webunion_account::find()->where(['user_id' => $user_id])->one();

        //3 这里为空 就是账户为零
        if (empty($qianbao)) {
            $total_history_interest = 0.00;
            $total_on_interest = 0.00;
            $total_history_flow = 0.00;
            $total_on_flow = 0.00;
            $score = 0.00;
        } else {
            $total_history_interest = $qianbao->total_history_interest;
            $total_on_interest = $qianbao->total_on_interest;
            $total_history_flow = $qianbao->total_history_flow;
            $total_on_flow = $qianbao->total_on_flow;
            $score = $qianbao->score;
        }

        //4 昨日收益
        $shouyitotal = $oDetail->getYesterday($user_id);

        //5 按月统计数据
        $statData = $oDetail->getMonthStat($user_id);

        //6 输出结果		
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "钱包";
        return $this->render('index', [
                    'shouyitotal' => $shouyitotal,
                    'total_history_interest' => $total_history_interest,
                    'total_on_interest' => $total_on_interest,
                    'total_history_flow' => $total_history_flow,
                    'total_on_flow' => $total_on_flow,
                    'create_time' => $user->create_time,
                    'score' => $score,
                    'statData' => $statData,
                    'jsinfo' => $jsinfo
        ]);
    }

    /**
     * 钱包明细
     */
    public function actionDetails() {
        //1 会员信息
        $user = $this->getUser();
        $user_id = $user->user_id;
        $oDetail = new Webunion_profit_detail;

        //2 参数
        $month = Yii::$app->request->get('month');
        if (!$month) {
            echo 'month参数无.无法访问';
            exit;
        }

        //3 分页
        $page = Yii::$app->request->get('page');
        $page = intval($page);
        if (!$page) {
            $page = 0;
        }

        //4 折合月份
        $limit = 20; // @todo
        $offset = $page * $limit;
        $total = $oDetail->getMonthCount($user_id, $month);
        $details = $oDetail->getMonthDetail($user_id, $month, $offset, $limit);
        $details = $oDetail->formatReason($details);
        //5 输出数据
        $data = [
            'nextpage' => $total > $offset + $limit,
            'month' => $month,
            'total' => $total,
            'details' => $details,
        ];

        //6 输出结果
        if ($page > 0) {
            $this->layout = false;
            return $this->render('detail_list', $data);
        } else {
            $this->getView()->title = "钱包明细";
            return $this->render('details', $data);
        }
    }

}
