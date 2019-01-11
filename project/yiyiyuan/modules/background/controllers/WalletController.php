<?php

namespace app\modules\background\controllers;

use app\commands\SubController;
use app\models\dev\Webunion_account;
use app\models\dev\Webunion_profit_detail;
use Yii;

class WalletController extends SubController {

    public $layout = "index_n";

    private function getUser() {
        return Yii::$app->newDev->identity;
    }

    public function actionIndex() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id) || empty($user)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $account = Webunion_account::find()->where(['user_id' => $user->user_id])->one();
        $total_history_interest = 0; //历史总收益
        $flow = 0; //流量
        $interset = 0; //账户金额
        $yestoday_income = 0; //昨日收益
        $frozen_income = 0; //冻结收益
        $score = 0; //积分
        if (!empty($account)) {
            $total_history_interest = $account->total_history_interest; //历史总收益
            $flow = $account->total_history_flow - $account->total_on_flow; //流量
            $interset = $account->total_history_interest - $account->total_on_interest; //账户金额
            $score = $account->score; //积分
            $starttime = date('Y-m-d', strtotime('-1 days'));
            $endtime = date('Y-m-d');
            //根据收益明细获得昨天的收益总额
            $yestoday_income = Webunion_profit_detail::find()->select('profit_amount')->where(['>=', 'create_time', $endtime])->andWhere(['user_id' => $user->user_id, 'profit_type' => 2])->andWhere(['in', 'status', [0, 4]])->sum('profit_amount');
            $frozen_income = $account->frozen_interest;
        } else {
            $condition['user_id'] = $user->user_id;
            $result = (new Webunion_account)->addAccount($condition);
        }
        $returnUrl = '/background/default/index';
        $jsinfo = $this->getWxParam();
        $this->getview()->title = '我的钱包';
        return $this->render('index', [
                    'total_history_interest' => $total_history_interest, //历史总收益
                    'flow' => $flow, //流量
                    'interset' => $interset, //账户金额
                    'yestoday_income' => $yestoday_income, //昨日收益
                    'frozen_income' => $frozen_income, //冻结收益
                    'score' => $score, //积分
                    'jsinfo' => $jsinfo,
                    'returnUrl' => $returnUrl
        ]);
    }

    /**
     * 获取指定月份的第一天开始和最后一天结束的时间戳
     *
     * @return array(本月开始时间，本月结束时间)
     */
    public function mFristAndLast($date = '') {
        if ($date == '') {
            $y = date("Y");
            $m = date("m");
        } else {
            $y = date("Y", strtotime($date));
            $m = date("m", strtotime($date));
        }
        $m = sprintf("%02d", intval($m));
        $y = str_pad(intval($y), 4, "0", STR_PAD_RIGHT);

        $m > 12 || $m < 1 ? $m = 1 : $m = $m;
        $firstday = strtotime($y . $m . "01000000");
        $firstdaystr = date("Y-m-01", $firstday);
        $lastday = strtotime(date('Y-m-d 23:59:59', strtotime("$firstdaystr +1 month -1 day")));
        return array("0" => date('Y-m-d H:i:s', $firstday), "1" => date('Y-m-d H:i:s', $lastday));
    }

    //获得网盟用户的账户信息
    public function get_webunion_account($user_id) {
        $account = new Webunion_account();
        $webunion_count = Webunion_account::find()->where(['user_id' => $user_id])->count();
        if ($webunion_count == 0) {
            $user_account = array(
                'user_id' => $user_id
            );
            $result = $account->addAccount($user_account);
        }
        $webunion_account = Webunion_account::find()->where(['user_id' => $user_id])->one();
        return $webunion_account;
    }

    //冻结收益记录
    public function actionFrozeninterest() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $webunion_account = $this->get_webunion_account($user->user_id);
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "冻结收益";
        return $this->render('frozen', [
                    'webunion_account' => $webunion_account,
                    'jsinfo' => $jsinfo
        ]);
    }

    //昨日收益
    public function actionYestodayincome() {
        $user = $this->getUser();
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/new/reg?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $yestoday = date('Y-m-d', strtotime('-1 day'));
        $todaydate = date('Y-m-d');
        $total = 0;
        $query = Webunion_profit_detail::find()->where(['user_id' => $user->user_id, 'profit_type' => 2])->andWhere(['>=', 'create_time', $todaydate])->andWhere(['in', 'status', [0, 4]]);
        $all_profit_detail = $query->all();
        //获得昨日总的现金收益
        if ($all_profit_detail) {
            foreach ($all_profit_detail as $key => $val) {
                $total+=$val['profit_amount'];
            }
        }
        $jsinfo = $this->getWxParam();
        $fronzen = Webunion_profit_detail::find()->where(['user_id' => $user->user_id, 'profit_type' => 2])->andWhere(['>=', 'create_time', $todaydate])->andWhere(['status' => 2])->all();
        //获得昨日总的现金收益
        $frozen_total = 0;
        if ($fronzen) {
            foreach ($fronzen as $key => $val) {
                $frozen_total+=$val['profit_amount'];
            }
        }
        $this->getView()->title = '昨日收益';
        return $this->render('yestoday_income', [
                    'yestoday' => $yestoday,
                    'total' => $total,
                    'frozen_total' => $frozen_total,
                    'jsinfo' => $jsinfo
        ]);
    }   

}
