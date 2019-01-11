<?php

namespace app\modules\background\controllers;

use Yii;
use app\commands\SubController;
use app\models\dev\Userwx;
use app\models\dev\Webunion_broadcast;
use app\models\dev\Webunion_profit_detail;
use app\models\dev\Webunion_user_list;
use app\models\dev\User_bank;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\User_invest;
use app\models\dev\Webunion_feedback;
use app\models\dev\Webunion_account;
use app\models\dev\Webunion_flow_settlement;
use app\models\dev\Webunion_notice;
use app\models\dev\Account_settlement;
use app\models\dev\User_remit_list;
use app\models\dev\Scan_times;
use yii\data\Pagination;
use app\commonapi\Apihttp;
use app\commonapi\Http;
use app\commonapi\Logger;

if (!class_exists('AxisPrototype')) {
    include '../jpgraph/jpgraph.php';
}
if (!class_exists('LinePlot')) {
    include '../jpgraph/jpgraph_line.php';
}

class WebunionController extends SubController {

    public $layout = 'webunion';
    public $enableCsrfValidation = false;

    private function getUser() {
        return Yii::$app->newDev->identity;
    }

    public function actionCs() {
        $open_id = $this->getVal('openid');
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $profit = Webunion_profit_detail::find()->select(array('profit_amount', 'create_time'))->where(['user_id' => $user_id])->orderBy('create_time desc')->limit(30)->asarray()->all();
        print_r($profit);
        $arr = array();
        foreach ($profit as $v) {
            $arr[] = $v['profit_amount'];
        }

        $size = count($profit);
        foreach ($profit as $k => $v) {
            if ($k == 0) {
                $arr1[] = substr($v['create_time'], 5, 5);
            } else if ($k == $size - 1) {
                $arr1[] = substr($v['create_time'], 5, 5);
            } else {
                $arr1[] = '';
            }
        }


        print_r($arr1);
    }

    public function actionGrapf() {
//$ydata = array(110, 3, 8, 12, 50, 1, 9, 12, 74, 34, 110, 3, 8, 12, 50, 1, 9, 12, 74, 34, 110, 3, 8, 12, 50, 1, 9, 12, 74, 34);
//$datax = array("10-04", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "11-04");
        $open_id = $this->getVal('openid');
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $profit = Webunion_profit_detail::find()->select(array('profit_amount', 'create_time'))->where(['user_id' => $user_id, 'profit_type' => 2])->orderBy('create_time desc')->limit(30)->asarray()->all();
        $ydata = array();
        foreach ($profit as $v) {
            $ydata[] = number_format($v['profit_amount'], 2, '.', '');
        }
        $datax = array();
        $size = count($profit);
        foreach ($profit as $k => $v) {
//$datax[] = substr($v['create_time'], 5, 5);
            if ($k == 0) {
                $datax[] = substr($v['create_time'], 5, 5);
            } else if ($k == $size - 1) {
                $datax[] = substr($v['create_time'], 5, 5);
            } else {
                $datax[] = '';
            }
        }
        $datax = array_reverse($datax);

        if (empty($ydata)) {
            $ydata = array(0, 0);
        }

        $ydata = array_reverse($ydata);
//print_r($ydata);
//exit;
        $graph = new \Graph(350, 250, "auto");
//设定尺度类型
        $graph->SetScale('textlin');
// 设置图表大标题  
//$graph->title->Set('');
// Create the linear plot
        $lineplot = new \LinePlot($ydata);
        $lineplot->SetColor('blue');
// 加入 x 轴标注  
        $graph->xaxis->SetTickLabels($datax);
// 定位 x 轴标注垂直位置应在最下方  
        $graph->xaxis->SetPos("min");

// 设置 x 轴标注文字为斜体，粗体，6号小字  
//$graph->xaxis->SetFont(FF_ARIAL, FS_BOLD, 6);
// 设置 x 轴标注文字 45 度倾斜。注：前面 SetFont 必须为 FF_ARIAL  
//$graph->xaxis->SetLabelAngle(45);
// x 轴刻度间隔为 2  
//$graph->xaxis->SetTextLabelInterval(30);
// 标题和 y 轴标题字体为标准字体  
//$graph->title->SetFont(FF_FONT1, FS_BOLD, 2);
//$graph->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
// Add the plot to the graph
        $graph->Add($lineplot);

// Display the graph
        $graph->Stroke();
    }

    public function actionIndex() {

        $open_id = $this->getVal('openid');

        $user = $this->getUser();

        if (empty($open_id) || empty($user)) {

            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/borrow/reg/login?url=/background/webunion/index&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

        return $this->redirect('/background/default/index');
    }

    /**
     * 钱包明细
     */
    public function actionWallet() {
        return $this->redirect('/background/profit');
    }

    private function actionWallet22222() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id', 'create_time'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $qianbao = Webunion_account::find()->where(['user_id' => $user_id])->one();
        //这里为空 就是账户为零
        if (empty($qianbao) && !isset($qianbao)) {
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
        $shouyi = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', date('Y-m-d', strtotime('-1 day', time())), date('Y-m-d')])->all();
        if (empty($shouyi) && !isset($shouyi)) {
            $shouyitotal = 0.00;
        } else {
            $shouyitotal = 0.00;
            foreach ($shouyi as $v) {
                $shouyitotal+=$v['profit_amount'];
            }
        }
        $shouyitotal = number_format($shouyitotal, 2, ".", "");
        $shouyi10 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2016-03-01', '2016-03-31'])->all();
        $shouyi11 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-11-01', '2015-11-31'])->all();
        $shouyi12 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-12-01', '2015-12-31'])->all();
        $shouyi1 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2016-01-01', '2015-01-31'])->all();
        $shouyi2 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-02-01', '2015-02-31'])->all();
        $shouyi3 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-12-01', '2015-12-31'])->all();
        $shouyi4 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-12-01', '2015-12-31'])->all();
        $shouyi5 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-12-01', '2015-12-31'])->all();
        $shouyi6 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-12-01', '2015-12-31'])->all();
        $shouyi7 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-12-01', '2015-12-31'])->all();
        $shouyi8 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-12-01', '2015-12-31'])->all();
        $shouyi9 = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-12-01', '2015-12-31'])->all();
        $userinfo = array();
        $loaninfo = array();
        $investinfo = array();
        $total11 = 0;
        foreach ($shouyi11 as $k => $v) {
            $total11+=$v->profit_amount;
            if ($v->type == 1 || $v->type == 2) {
//为好友id
//echo '1';
                $userinfo[] = Webunion_profit_detail::find()->joinWith('user')->where([Webunion_profit_detail::tableName() . '.type' => array(1, 2)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
            } else if ($v->type == 3 || $v->type == 4) {
//为借款id
//echo '2';
                $loan_id = $v->profit_id;
                $loaninfo[$k] = Webunion_profit_detail::find()->joinWith('loan')->where([Webunion_profit_detail::tableName() . '.type' => array(3, 4)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $loaninfo[$k]['loan']['user_id'];

                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $loaninfo[$k]['loan']['user_id'] = 'bob';
                } else {
                    $loaninfo[$k]['loan']['user_id'] = $usfo->realname;
                }
            } else if ($v->type == 5) {
//投资id
// echo '3';
                $invest_id = $v->profit_id;
                $investinfo[$k] = Webunion_profit_detail::find()->joinWith('invest')->where([Webunion_profit_detail::tableName() . '.type' => 5])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $investinfo[$k]['invest']['user_id'];
                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $investinfo[$k]['invest']['user_id'] = 'bob';
                    $investinfo[$k]['invest']['amount'] = 0.00;
                } else {
                    $investinfo[$k]['invest']['user_id'] = $usfo->realname;
                }
            }
        }

        $userinfo10 = array();
        $loaninfo10 = array();
        $investinfo10 = array();
        $total10 = 0;
        foreach ($shouyi10 as $k => $v) {
            $total10+=$v->profit_amount;
            if ($v->type == 1 || $v->type == 2) {
//为好友id
//echo '1';
                $userinfo10[] = Webunion_profit_detail::find()->joinWith('user')->where([Webunion_profit_detail::tableName() . '.type' => array(1, 2)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
            } else if ($v->type == 3 || $v->type == 4) {
//为借款id
//echo '2';
                $loan_id = $v->profit_id;
                $loaninfo10[$k] = Webunion_profit_detail::find()->joinWith('loan')->where([Webunion_profit_detail::tableName() . '.type' => array(3, 4)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $loaninfo10[$k]['loan']['user_id'];

                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $loaninfo10[$k]['loan']['user_id'] = '未知用户';
                } else {
                    $loaninfo10[$k]['loan']['user_id'] = $usfo->realname;
                }
            } else if ($v->profit_type == 5) {
//投资id
// echo '3';
                $invest_id = $v->profit_id;
                $investinfo10[$k] = Webunion_profit_detail::find()->joinWith('invest')->where([Webunion_profit_detail::tableName() . '.type' => 5])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $investinfo10[$k]['invest']['user_id'];
                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $investinfo10[$k]['invest']['user_id'] = 'bob';
                    $investinfo10[$k]['invest']['amount'] = 0.00;
                } else {
                    $investinfo10[$k]['invest']['user_id'] = $usfo->realname;
                }
            }
        }

        $userinfo12 = array();
        $loaninfo12 = array();
        $investinfo12 = array();
        $total12 = 0;
        foreach ($shouyi12 as $k => $v) {
            $total12+=$v->profit_amount;
            if ($v->type == 1 || $v->type == 2) {
//为好友id
//echo '1';
                $userinfo12[] = Webunion_profit_detail::find()->joinWith('user')->where([Webunion_profit_detail::tableName() . '.type' => array(1, 2)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
            } else if ($v->type == 3 || $v->type == 4) {
//为借款id
//echo '2';
                $loan_id = $v->profit_id;
                $loaninfo12[$k] = Webunion_profit_detail::find()->joinWith('loan')->where([Webunion_profit_detail::tableName() . '.type' => array(3, 4)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $loaninfo12[$k]['loan']['user_id'];

                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $loaninfo12[$k]['loan']['user_id'] = 'bob';
                } else {
                    $loaninfo12[$k]['loan']['user_id'] = $usfo->realname;
                }
            } else if ($v->profit_type == 5) {
//投资id
// echo '3';
                $invest_id = $v->profit_id;
                $investinfo12[$k] = Webunion_profit_detail::find()->joinWith('invest')->where([Webunion_profit_detail::tableName() . '.type' => 5])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $investinfo12[$k]['invest']['user_id'];
                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $investinfo12[$k]['invest']['user_id'] = 'bob';
                    $investinfo12[$k]['invest']['amount'] = 0.00;
                } else {
                    $investinfo12[$k]['invest']['user_id'] = $usfo->realname;
                }
            }
        }

        $userinfo1 = array();
        $loaninfo1 = array();
        $investinfo1 = array();
        $total1 = 0;
        foreach ($shouyi1 as $k => $v) {
            $total1+=$v->profit_amount;
            if ($v->type == 1 || $v->type == 2) {
//为好友id
//echo '1';
                $userinfo1[] = Webunion_profit_detail::find()->joinWith('user')->where([Webunion_profit_detail::tableName() . '.type' => array(1, 2)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
            } else if ($v->type == 3 || $v->type == 4) {
//为借款id
//echo '2';
                $loan_id = $v->profit_id;
                $loaninfo1[$k] = Webunion_profit_detail::find()->joinWith('loan')->where([Webunion_profit_detail::tableName() . '.type' => array(3, 4)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $loaninfo1[$k]['loan']['user_id'];

                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $loaninfo1[$k]['loan']['user_id'] = '未知用户';
                } else {
                    $loaninfo1[$k]['loan']['user_id'] = $usfo->realname;
                }
            } else if ($v->profit_type == 5) {
//投资id
// echo '3';
                $invest_id = $v->profit_id;
                $investinfo1[$k] = Webunion_profit_detail::find()->joinWith('invest')->where([Webunion_profit_detail::tableName() . '.type' => 5])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $investinfo1[$k]['invest']['user_id'];
                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $investinfo1[$k]['invest']['user_id'] = 'bob';
                    $investinfo1[$k]['invest']['amount'] = 0.00;
                } else {
                    $investinfo1[$k]['invest']['user_id'] = $usfo->realname;
                }
            }
        }

        $userinfo2 = array();
        $loaninfo2 = array();
        $investinfo2 = array();
        $total2 = 0;
        foreach ($shouyi2 as $k => $v) {
            $total2+=$v->profit_amount;
            if ($v->type == 1 || $v->type == 2) {
//为好友id
//echo '1';
                $userinfo2[] = Webunion_profit_detail::find()->joinWith('user')->where([Webunion_profit_detail::tableName() . '.type' => array(1, 2)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
            } else if ($v->type == 1 || $v->type == 2) {
//为借款id
//echo '2';
                $loan_id = $v->profit_id;
                $loaninfo2[$k] = Webunion_profit_detail::find()->joinWith('loan')->where([Webunion_profit_detail::tableName() . '.type' => array(13, 4)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $loaninfo2[$k]['loan']['user_id'];

                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $loaninfo2[$k]['loan']['user_id'] = 'bob';
                } else {
                    $loaninfo2[$k]['loan']['user_id'] = $usfo->realname;
                }
            } else if ($v->profit_type == 5) {
//投资id
// echo '3';
                $invest_id = $v->profit_id;
                $investinfo2[$k] = Webunion_profit_detail::find()->joinWith('invest')->where([Webunion_profit_detail::tableName() . '.type' => 5])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $investinfo2[$k]['invest']['user_id'];
                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $investinfo2[$k]['invest']['user_id'] = 'bob';
                    $investinfo2[$k]['invest']['amount'] = 0.00;
                } else {
                    $investinfo2[$k]['invest']['user_id'] = $usfo->realname;
                }
            }
        }


//print_r($userinfo);exit;
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "钱包";
        return $this->render('wallet', [
                    'shouyitotal' => $shouyitotal,
                    'total_history_interest' => $total_history_interest,
                    'total_on_interest' => $total_on_interest,
                    'total_history_flow' => $total_history_flow,
                    'total_on_flow' => $total_on_flow,
                    'create_time' => $user->create_time,
                    'score' => $score,
                    'userinfo' => $userinfo,
                    'loaninfo' => $loaninfo,
                    'investinfo' => $investinfo,
                    'total11' => $total11,
                    'userinfo10' => $userinfo10,
                    'loaninfo10' => $loaninfo10,
                    'investinfo10' => $investinfo10,
                    'total10' => $total10,
                    'userinfo12' => $userinfo12,
                    'loaninfo12' => $loaninfo12,
                    'investinfo12' => $investinfo12,
                    'total12' => $total12,
                    'userinfo1' => $userinfo1,
                    'loaninfo1' => $loaninfo1,
                    'investinfo1' => $investinfo1,
                    'total1' => $total1,
                    'userinfo2' => $userinfo2,
                    'loaninfo2' => $loaninfo2,
                    'investinfo2' => $investinfo2,
                    'total2' => $total2,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionSpread() {
        $open_id = $this->getVal('openid');
        $mobile = $this->getVal('mobile');


        if (empty($open_id)) {
            if (isset($_GET['code'])) {
                $code = $_GET['code'];
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . self::$_appid . "&secret=" . self::$_appSecret . "&code=" . $code . "&grant_type=authorization_code";
                $data = Http::getCurl($url);
                $resultArr = json_decode($data, true); //转为数组
                if (isset($resultArr['openid']) && !empty($resultArr['openid'])) {
                    $isUser = $this->isOpenidReg($resultArr['openid']);
                    if (!$isUser) {
                        $usinfo = $this->getWebAuthThree($resultArr);
//保存新用户
                        if ($this->openidRegSave($usinfo)) {
                            $this->setVal('openid', $usinfo["openid"]);
                        } else {
//保存微信用户失败，去出错页面
                            return $this->redirect('/dev/site/error');
                        }
                    } else {
                        $this->setVal('openid', $resultArr['openid']);
                    }
                } else {
//没有取到token值和openid，去错误页面
                    return $this->redirect('/dev/site/error');
                }
            }
            $open_id = $this->getVal('openid');
        }
        if (empty($open_id) || empty($mobile)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $userinfo = User::find()->where(['openid' => $open_id])->one();
        if (empty($userinfo) && !isset($userinfo)) {
            $invite_code = '';
        } else {
            $invite_code = $userinfo->invite_code;
        }
        $loanuserinfo = Userwx::find()->where(['openid' => $open_id])->asarray()->one();
        $time = time();
        $Url = urlencode(Yii::$app->request->hostInfo . "/background/webunion/spread1?u=" . $userinfo->user_id . "&t=" . $time . "&s=" . md5($time . $userinfo->user_id));
//$Url = urlencode(Yii::$app->request->hostInfo . "/background/webunion/spread");
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        $this->getView()->title = "推广";
        $jsinfo = $this->getWxParam();
        return $this->render('spread', [
                    'invite_code' => $invite_code,
                    'shareUrl' => $shareUrl,
                    'loanuserinfo' => $loanuserinfo,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionSpread1() {
        $open_id = $this->getVal('openid');
        $mobile = $this->getVal('mobile');
//获取借款记录的ID
        $user_id = intval($_GET['u']);
//获取时间
        $t = intval($_GET['t']);
        $s = $_GET['s'];

        if (empty($open_id)) {
            if (isset($_GET['code'])) {
                $code = $_GET['code'];
                $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . self::$_appid . "&secret=" . self::$_appSecret . "&code=" . $code . "&grant_type=authorization_code";
                $data = Http::getCurl($url);
                $resultArr = json_decode($data, true); //转为数组
                if (isset($resultArr['openid']) && !empty($resultArr['openid'])) {
                    $isUser = $this->isOpenidReg($resultArr['openid']);
                    if (!$isUser) {
                        $usinfo = $this->getWebAuthThree($resultArr);
//保存新用户
                        if ($this->openidRegSave($usinfo)) {
                            $this->setVal('openid', $usinfo["openid"]);
                        } else {
//保存微信用户失败，去出错页面
                            return $this->redirect('/dev/site/error');
                        }
                    } else {
                        $this->setVal('openid', $resultArr['openid']);
                    }
                } else {
//没有取到token值和openid，去错误页面
                    return $this->redirect('/dev/site/error');
                }
            }
            $open_id = $this->getVal('openid');
        }
        if (empty($open_id) || empty($mobile)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $userinfo = User::find()->where(['user_id' => $user_id])->one();
        if (empty($userinfo) && !isset($userinfo)) {
            $invite_code = '';
        } else {
            $invite_code = $userinfo->invite_code;
        }
        $loanuserinfo = Userwx::find()->where(['openid' => $open_id])->asarray()->one();
        $time = time();
        $Url = urlencode(Yii::$app->request->hostInfo . "/background/webunion/spread1?u=" . $user_id . "&t=" . $time . "&s=" . md5($time . $user_id));
//$Url = urlencode(Yii::$app->request->hostInfo . "/background/webunion/spread");
        $shareUrl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . self::$_appid . "&redirect_uri=" . $Url . "&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
        $this->getView()->title = "推广";
        $jsinfo = $this->getWxParam();
        return $this->render('spread1', [
                    'invite_code' => $invite_code,
                    'shareUrl' => $shareUrl,
                    'loanuserinfo' => $loanuserinfo,
                    'jsinfo' => $jsinfo,
        ]);
    }

    public function actionTixian() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $accountinfo = Account_settlement::find()->where(['user_id' => $user_id, 'type' => 4])->all();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "提现列表";
        return $this->render('tixian', [
                    'accountinfo' => $accountinfo,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionCommission() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "佣金制度";
        return $this->render('commission', [
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionContact() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "联系我们";
        return $this->render('contact', [
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionOption() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "意见反馈";
        return $this->render('option', [
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionMethod() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            $resultArr = array('ret' => '0', 'url' => '/dev/loan');
            echo json_encode($resultArr);
            exit;
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = !empty($user->user_id) ? $user->user_id : 0;

        $content = $_POST['content'];
//echo $content;exit;
        $time = date('Y-m-d H:i:s');
        $webfb = new Webunion_feedback();
        $webfb->user_id = $user_id;
        $webfb->content = $content;
        $webfb->create_time = $time;
        $webfb->last_modify_time = $time;
//$webfb->version = 1;
        if ($webfb->save()) {
//echo '成功';
            $resultArr = array('ret' => '1', 'url' => '/dev/loan');
            echo json_encode($resultArr);
            exit;
        } else {
//echo '失败';
            $resultArr = array('ret' => '0', 'url' => '/dev/loan');
            echo json_encode($resultArr);
            exit;
        };
    }

    public function actionPinfor() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user_id = $_GET['id'] + 0;
        $userinfo = User::find()->where(['user_id' => $user_id])->one();
        $userwx = Userwx::find()->where(['openid' => $open_id])->one();
        if (empty($userwx) && !isset($userwx)) {
            $heads = '/images/bigFace.png';
        } else {
            $heads = $userwx->head;
        }
        $user_bank = User_bank::find()->where(['user_id' => $user_id])->all();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "个人信息";
        return $this->render('pinfor', [
                    'userwx' => $userwx,
                    'userinfo' => $userinfo,
                    'user_bank' => $user_bank,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionTorrow() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $shouyi = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', date('Y-m-d', strtotime('-1 day', time())), date('Y-m-d')])->all();
//$shouyi = Webunion_profit_detail::find()->where(['user_id' => $user_id])->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-10-01', '2015-10-31'])->all();
//$shouyi = Webunion_profit_detail::find()->where(['user_id' => $user_id])->all();->andFilterWhere(['between', Webunion_profit_detail::tableName() . '.`create_time`', '2015-10-01', '2015-10-31'])
        if (empty($shouyi) && !isset($shouyi)) {
            $shouyitotal = 0.00;
        } else {
            $shouyitotal = 0.00;
            foreach ($shouyi as $v) {
                $shouyitotal+=$v['profit_amount'];
            }
        }
        $shouyitotal = number_format($shouyitotal, 2, ".", "");
        $userinfo = array();
        $loaninfo = array();
        $investinfo = array();
        foreach ($shouyi as $k => $v) {
            if ($v->type == 1 || $v->type == 2) {
//为好友id
//echo '1';
                $userinfo[] = Webunion_profit_detail::find()->joinWith('user')->where([Webunion_profit_detail::tableName() . '.type' => array(1, 2)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
            } else if ($v->type == 3 || $v->type == 4) {
//为借款id
//echo '2';
                $loan_id = $v->profit_id;
                $loaninfo[$k] = Webunion_profit_detail::find()->joinWith('loan')->where([Webunion_profit_detail::tableName() . '.type' => array(3, 4)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $loaninfo[$k]['loan']['user_id'];

                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $loaninfo[$k]['loan']['user_id'] = 'Bob';
                } else {
                    $loaninfo[$k]['loan']['user_id'] = $usfo->realname;
                }
            } else if ($v->type == 5) {
//投资id
// echo '3';
                $user_id = $v->profit_id;
                $investinfo[$k] = Webunion_profit_detail::find()->joinWith('invest')->where([Webunion_profit_detail::tableName() . '.type' => 5])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
//$user_id = $investinfo[$k]['invest']['user_id'];
                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $investinfo[$k]['invest']['user_id'] = 'bob';
                    $investinfo[$k]['invest']['amount'] = 0.00;
                } else {
                    $investinfo[$k]['invest']['user_id'] = $usfo->realname;
                }
            }
        }
//print_r($investinfo);
//exit;
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "昨日收益";
        return $this->render('torrow', [
                    'shouyitotal' => $shouyitotal,
                    'userinfo' => $userinfo,
                    'loaninfo' => $loaninfo,
                    'investinfo' => $investinfo,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionMainyou() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $haoyou1 = Webunion_user_list::find()->where(['type' => 1, 'parent_user_id' => $user_id])->count();
        $haoyou2 = Webunion_user_list::find()->where(['top_user_id' => $user_id, 'type' => 2])->count();
        $pages = new Pagination(['totalCount' => $haoyou1, 'pageSize' => '50']);
        $haoyone = Webunion_user_list::find()->joinWith('user')->where(['type' => 1, 'parent_user_id' => $user_id])->offset($pages->offset)->limit($pages->limit)->asarray()->all();
        foreach ($haoyone as $k => $v) {
            $openid = $v['user']['openid'];
            $user_ids = $v['user_id'];
            $userinfo = User::find()->where(['user_id' => $user_ids])->one();
            $loaninfo = User_loan::find()->where(['user_id' => $user_ids])->orderBy('create_time desc')->one();
            if (empty($loaninfo) && !empty($userinfo)) {
//没有借款
                if ($userinfo->status == 3) {
//已认证
                    $zstatus = 0;
                } else {
//未认证
                    $zstatus = 1;
                }
            } else {
                if ($loaninfo->status == 6 || $loaninfo->status == 9 || $loaninfo->status == 11 || $loaninfo->status == 12 || $loaninfo->status == 13) {
//借款中
                    $zstatus = 2;
//$amount = number_format($loaninfo->amount, 2, ".", "");
                } else if ($loaninfo->status == 8) {
//已还款 显示3天
                    if (time() - (strtotime($loaninfo->create_time) ) <= 3 * 24 * 3600) {
                        $zstatus = 3;
                    } else {
                        $zstatus = 0;
                    }
                } else if ($loaninfo->status == 3) {
                    if ($userinfo->status == 3) {
                        $zstatus = 0;
                    } else {
                        $zstatus = 1;
                    }
                } else if ($loaninfo->status == 1) {
                    $zstatus = 1;
                } else {
                    $zstatus = 0;
                }
            }
            $userwx = Userwx::find()->where(['openid' => $openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/bigFace.png';
            } else {
                $heads = $userwx->head;
            }
            $haoyone[$k]['heads'] = $heads;
            $haoyone[$k]['user']['zstatus'] = $zstatus;
        }
        $haoywei = Webunion_user_list::find()->joinWith('user')->where(['top_user_id' => $user_id, 'type' => 1, User::tableName() . '.status' => array(1, 2, 4, 5, 6, 7)])->all();
        foreach ($haoywei as $k => $v) {
            $openid = $v->user->openid;
            //这里判断用户的状态
            $user_id = $v->user->user_id;

            $userwx = Userwx::find()->where(['openid' => $openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/bigFace.png';
            } else {
                $heads = $userwx->head;
            }
            $haoywei[$k]['user']['company'] = $heads;
        }

        $jsinfo = $this->getWxParam();
        $this->getView()->title = "我的好友";
        return $this->render('mainyou', [
                    'haoyou1' => $haoyou1,
                    'haoyou2' => $haoyou2,
                    'haoyone' => $haoyone,
                    'haoywei' => $haoywei,
                    'pages' => $pages,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionWebsave() {
        $user_id = $_POST['id'];
        $haoytwo = Webunion_user_list::find()->joinWith('user')->where(['parent_user_id' => $user_id, 'type' => 2])->limit(50)->asarray()->all();
        foreach ($haoytwo as $k => $v) {
            $openid = $v['user']['openid'];
            //这里判断用户的状态
            $user_id = $v['user']['user_id'];

            $userwx = Userwx::find()->where(['openid' => $openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/bigFace.png';
            } else {
                $heads = $userwx->head;
            }
            $haoytwo[$k]['user']['company'] = $heads;
        }
        echo json_encode($haoytwo);
        exit;
    }

    public function actionMainyous() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $haoyou1 = Webunion_user_list::find()->where(['type' => 1, 'parent_user_id' => $user_id])->count();
        $haoyou2 = Webunion_user_list::find()->where(['top_user_id' => $user_id, 'type' => 2])->count();

        $haoyone = Webunion_user_list::find()->joinWith('user')->where(['type' => 1, 'parent_user_id' => $user_id])->asarray()->all();
        $haoytwo = Webunion_user_list::find()->joinWith('user')->where(['top_user_id' => $user_id, 'type' => 2])->asarray()->all();

        foreach ($haoyone as $k => $v) {
            $openid = $v['user']['openid'];
            foreach ($haoytwo as $k1 => $v1) {
                if ($v1['parent_user_id'] == $v['user_id']) {
                    $haoyone[$k]['two'][$k1] = $v1;
                    foreach ($haoyone[$k]['two'] as $k2 => $v2) {
                        $openid1 = $v2['user']['openid'];
                        $user_id1 = $v2['user']['user_id'];
                        $userinfo = User::find()->where(['user_id' => $user_id1])->one();
                        $loaninfo = User_loan::find()->where(['user_id' => $user_id1])->orderBy('create_time desc')->one();
                        if (empty($loaninfo) && !empty($userinfo)) {
//没有借款
                            if ($userinfo->status == 3) {
//已认证
                                $zstatus = 0;
                            } else {
//未认证
                                $zstatus = 1;
                            }
                        } else {
                            if ($loaninfo->status == 5 || $loaninfo->status == 6 || $loaninfo->status == 9 || $loaninfo->status == 11 || $loaninfo->status == 12 || $loaninfo->status == 13) {
//借款中
                                $zstatus = 2;
//$amount = number_format($loaninfo->amount, 2, ".", "");
                            } else if ($loaninfo->status == 8) {
//已还款 显示3天
                                if (time() - (strtotime($loaninfo->create_time) ) <= 3 * 24 * 3600) {
                                    $zstatus = 3;
                                } else {
                                    $zstatus = 0;
                                }
                            } else if ($loaninfo->status == 3) {
                                if ($userinfo->status == 3) {
                                    $zstatus = 0;
                                } else {
                                    $zstatus = 1;
                                }
                            } else if ($loaninfo->status == 1) {
                                $zstatus = 1;
                            } else {
                                $zstatus = 0;
                            }
                        }
                        $userwx = Userwx::find()->where(['openid' => $openid1])->one();
                        if (empty($userwx) && !isset($userwx)) {
                            $heads = '/images/bigFace.png';
                        } else {
                            $heads = $userwx->head;
                        }
                        $haoyone[$k]['two'][$k2]['heads'] = $heads;
                        $haoyone[$k]['two'][$k2]['zstatus'] = $zstatus;
                    }
                }
            }
            $user_ids = $v['user_id'];
            $userinfo = User::find()->where(['user_id' => $user_ids])->one();
            $loaninfo = User_loan::find()->where(['user_id' => $user_ids])->orderBy('create_time desc')->one();
            if (empty($loaninfo) && !empty($userinfo)) {
//没有借款
                if ($userinfo->status == 3) {
//已认证
                    $zstatus = 0;
                } else {
//未认证
                    $zstatus = 1;
                }
            } else {
                if ($loaninfo->status == 6 || $loaninfo->status == 9 || $loaninfo->status == 11 || $loaninfo->status == 12 || $loaninfo->status == 13) {
//借款中
                    $zstatus = 2;
//$amount = number_format($loaninfo->amount, 2, ".", "");
                } else if ($loaninfo->status == 8) {
//已还款 显示3天
                    if (time() - (strtotime($loaninfo->create_time) ) <= 3 * 24 * 3600) {
                        $zstatus = 3;
                    } else {
                        $zstatus = 0;
                    }
                } else if ($loaninfo->status == 3) {
                    if ($userinfo->status == 3) {
                        $zstatus = 0;
                    } else {
                        $zstatus = 1;
                    }
                } else if ($loaninfo->status == 1) {
                    $zstatus = 1;
                } else {
                    $zstatus = 0;
                }
            }
            $userwx = Userwx::find()->where(['openid' => $openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/bigFace.png';
            } else {
                $heads = $userwx->head;
            }
            $haoyone[$k]['heads'] = $heads;
            $haoyone[$k]['user']['zstatus'] = $zstatus;
        }
//print_r($haoyone);
//exit;
        $haoywei = Webunion_user_list::find()->joinWith('user')->where(['top_user_id' => $user_id, 'type' => 1, User::tableName() . '.status' => array(1, 2, 4, 5, 6, 7)])->all();
        foreach ($haoywei as $k => $v) {
            $openid = $v->user->openid;
//这里判断用户的状态
            $user_id = $v->user->user_id;

            $userwx = Userwx::find()->where(['openid' => $openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/bigFace.png';
            } else {
                $heads = $userwx->head;
            }
            $haoywei[$k]['user']['company'] = $heads;
//$haoywei[$k]['user']['zstatus'] = $zstatus;
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "我的好友";
        return $this->render('mainyous', [
                    'haoyou1' => $haoyou1,
                    'haoyou2' => $haoyou2,
                    'haoyone' => $haoyone,
                    'haoywei' => $haoywei,
                    'jsinfo' => $jsinfo
// 'haoytwo' => $haoytwo,
// 'haoywei' => $haoywei,
        ]);
    }

    public function actionOne() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $haoyou1 = Webunion_user_list::find()->where(['type' => 1, 'parent_user_id' => $user_id])->count();
        $pages = new Pagination(['totalCount' => $haoyou1, 'pageSize' => '50']);
        $haoyone = Webunion_user_list::find()->where(['type' => 1, 'parent_user_id' => $user_id])->offset($pages->offset)->limit($pages->limit)->all();
        foreach ($haoyone as $k => $v) {
            $openid = $v->user->openid;
            $user_ids = $v->user_id;
            $userinfo = User::find()->where(['user_id' => $user_ids])->one();
            $loaninfo = User_loan::find()->where(['user_id' => $user_ids])->orderBy('create_time desc')->one();
            if (empty($loaninfo) && !empty($userinfo)) {
//没有借款
                if ($userinfo->status == 3) {
//已认证
                    $zstatus = 0;
                } else {
//未认证
                    $zstatus = 1;
                }
            } else {
                if ($loaninfo->status == 6 || $loaninfo->status == 9 || $loaninfo->status == 11 || $loaninfo->status == 12 || $loaninfo->status == 13) {
//借款中
                    $zstatus = 2;
//$amount = number_format($loaninfo->amount, 2, ".", "");
                } else if ($loaninfo->status == 8) {
//已还款 显示3天
                    if (time() - (strtotime($loaninfo->create_time) ) <= 3 * 24 * 3600) {
                        $zstatus = 3;
                    } else {
                        $zstatus = 0;
                    }
                } else if ($loaninfo->status == 3) {
                    if ($userinfo->status == 3) {
                        $zstatus = 0;
                    } else {
                        $zstatus = 1;
                    }
                } else if ($loaninfo->status == 1) {
                    $zstatus = 1;
                } else {
                    $zstatus = 0;
                }
            }
            $userwx = Userwx::find()->where(['openid' => $openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/bigFace.png';
            } else {
                $heads = $userwx->head;
            }
            $haoyone[$k]['user']['company'] = $heads;
            $haoyone[$k]['user']['status'] = $zstatus;
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "一级好友";
        return $this->render('one', [
                    'haoyone' => $haoyone,
                    'pages' => $pages,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionTwo() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $haoyou1 = Webunion_user_list::find()->where(['top_user_id' => $user_id, 'type' => 2])->count();
        $pages = new Pagination(['totalCount' => $haoyou1, 'pageSize' => '50']);
        $haoytwo = Webunion_user_list::find()->where(['type' => 2, 'top_user_id' => $user_id])->offset($pages->offset)->limit($pages->limit)->all();

        foreach ($haoytwo as $k => $v) {
            $openid = $v->user->openid;
            $user_ids = $v->user_id;
            $userinfo = User::find()->where(['user_id' => $user_ids])->one();
            $loaninfo = User_loan::find()->where(['user_id' => $user_ids])->orderBy('create_time desc')->one();
            if (empty($loaninfo) && !empty($userinfo)) {
//没有借款
                if ($userinfo->status == 3) {
//已认证
                    $zstatus = 0;
                } else {
//未认证
                    $zstatus = 1;
                }
            } else {
                if ($loaninfo->status == 6 || $loaninfo->status == 9 || $loaninfo->status == 11 || $loaninfo->status == 12 || $loaninfo->status == 13) {
//借款中
                    $zstatus = 2;
//$amount = number_format($loaninfo->amount, 2, ".", "");
                } else if ($loaninfo->status == 8) {
//已还款 显示3天
                    if (time() - (strtotime($loaninfo->create_time) ) <= 3 * 24 * 3600) {
                        $zstatus = 3;
                    } else {
                        $zstatus = 0;
                    }
                } else if ($loaninfo->status == 3) {
                    if ($userinfo->status == 3) {
                        $zstatus = 0;
                    } else {
                        $zstatus = 1;
                    }
                } else if ($loaninfo->status == 1) {
                    $zstatus = 1;
                } else {
                    $zstatus = 0;
                }
            }
            $userwx = Userwx::find()->where(['openid' => $openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/bigFace.png';
            } else {
                $heads = $userwx->head;
            }
            $haoytwo[$k]['user']['company'] = $heads;
            $haoytwo[$k]['user']['status'] = $zstatus;
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "二级好友";
        return $this->render('two', [
                    'haoytwo' => $haoytwo,
                    'pages' => $pages,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionDetial() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }

        $user_id = $_GET['user_id'] + 0;
//echo $user_id;
        $userinfo = User::find()->where(['user_id' => $user_id])->one();
        $loaninfo = User_loan::find()->where(['user_id' => $user_id])->orderBy('create_time desc')->one();
        $amount = 0;
        if (empty($loaninfo) && !empty($userinfo)) {
//没有借款
            if ($userinfo->status == 3) {
//已认证
                $zstatus = 0;
            } else {
//未认证
                $zstatus = 1;
            }
        } else {
            if ($loaninfo->status == 6 || $loaninfo->status == 9 || $loaninfo->status == 11 || $loaninfo->status == 12 || $loaninfo->status == 13) {
//借款中
                $zstatus = 2;
                $amount = number_format($loaninfo->amount, 2, ".", "");
            } else if ($loaninfo->status == 8) {
//已还款 显示3天
                if (time() - (strtotime($loaninfo->create_time) ) <= 3 * 24 * 3600) {
                    $zstatus = 3;
                } else {
                    $zstatus = 0;
                }
            } else if ($loaninfo->status == 3) {
                if ($userinfo->status == 3) {
                    $zstatus = 0;
                } else {
                    $zstatus = 1;
                }
            } else if ($loaninfo->status == 1) {
                $zstatus = 1;
            } else {
                $zstatus = 0;
            }
        }


        $open_ids = $userinfo->openid;
        $userwx = Userwx::find()->where(['openid' => $open_ids])->one();

        $jsinfo = $this->getWxParam();
        $this->getView()->title = "好友详情";
        return $this->render('detial', [
                    'userinfo' => $userinfo,
                    'userwx' => $userwx,
                    'zstatus' => $zstatus,
                    'amount' => $amount,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionThree() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $haoythree = Webunion_user_list::find()->joinWith('user')->where(['type' => 3, 'top_user_id' => $user_id])->all();
//print_r($haoyone);exit;
        foreach ($haoythree as $k => $v) {
            $openid = $v->user->openid;
            $user_ids = $v->user->user_id;
            $userinfo = User::find()->where(['user_id' => $user_ids])->one();
            $loaninfo = User_loan::find()->where(['user_id' => $user_ids])->orderBy('create_time desc')->one();
            if (empty($loaninfo) && !empty($userinfo)) {
//没有借款
                if ($userinfo->status == 3) {
//已认证
                    $zstatus = 0;
                } else {
//未认证
                    $zstatus = 1;
                }
            } else {
                if ($loaninfo->status == 6 || $loaninfo->status == 9 || $loaninfo->status == 11 || $loaninfo->status == 12 || $loaninfo->status == 13) {
//借款中
                    $zstatus = 2;
//$amount = number_format($loaninfo->amount, 2, ".", "");
                } else if ($loaninfo->status == 8) {
//已还款 显示3天
                    if (time() - (strtotime($loaninfo->create_time) ) <= 3 * 24 * 3600) {
                        $zstatus = 3;
                    } else {
                        $zstatus = 0;
                    }
                } else if ($loaninfo->status == 3) {
                    if ($userinfo->status == 3) {
                        $zstatus = 0;
                    } else {
                        $zstatus = 1;
                    }
                } else if ($loaninfo->status == 1) {
                    $zstatus = 1;
                } else {
                    $zstatus = 0;
                }
            }
            $userwx = Userwx::find()->where(['openid' => $openid])->one();
            if (empty($userwx) && !isset($userwx)) {
                $heads = '/images/bigFace.png';
            } else {
                $heads = $userwx->head;
            }
            $haoythree[$k]['user']['company'] = $heads;
            $haoythree[$k]['user']['status'] = $zstatus;
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "三级好友";
        return $this->render('three', [
                    'haoythree' => $haoythree,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionWithdraw() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $accountinfo = Webunion_account::find()->where(['user_id' => $user_id])->one();
//验证用户是否为限制用户===================================
        $limitStatus = 0;
        $userInfo = User::find()->where(['user_id' => $user_id])->one();
        if ($userInfo->status == '5') {
            $limitStatus = 1; //黑名单用户
        }
        $userLoan = User_loan::find()->where(['user_id' => $user_id, 'status' => array('11', '12', '13')])->all();
        if (!empty($userLoan) && $limitStatus == 0) {
            $limitStatus = 2; //有未还款借款
        }

        //春节期间，禁止提现
        $start_time = '2016-02-05 12:00:00';
        $end_time = '2016-02-15 10:00:00';
        $now_time = date('Y-m-d H:i:s');
        if ($now_time >= $start_time && $now_time <= $end_time) {
            $limitStatus = 4;
        }
//查一下 银行卡
        $user_bank = User_bank::find()->where(['user_id' => $user_id, 'status' => 1])->all();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "提现";
        return $this->render('withdraw', [
                    'user_id' => $user_id,
                    'user_bank' => $user_bank,
                    'accountinfo' => $accountinfo,
                    'limitStatus' => $limitStatus,
                    'start_time' => $start_time,
                    'end_time' => $end_time,
                    'now_time' => $now_time,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionWithlist() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $id = $_GET['id'] + 0;
//echo $id;exit;
        $userinfo = Account_settlement::find()->where(['id' => $id])->one();
//$qianbao = Webunion_account::find()->where(['user_id' => $user_id])->one();
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "提现详情";
        return $this->render('withlist', [
// 'haoythree' => $haoythree,
                    'userinfo' => $userinfo,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionLiuliang() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $accountinfo = Webunion_account::find()->where(['user_id' => $user_id])->one();
        //验证用户是否为限制用户===================================
        $limitStatus = 0;
        $userInfo = User::find()->where(['user_id' => $user_id])->one();
        if ($userInfo->status == '5') {
            $limitStatus = 1; //黑名单用户
        }
        $userLoan = User_loan::find()->where(['user_id' => $user_id, 'status' => array('11', '12', '13')])->all();
        if (!empty($userLoan) && $limitStatus == 0) {
            $limitStatus = 2; //有未还款借款
        }
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "流量";
        return $this->render('liuliang', [
                    'user_id' => $user_id,
                    'accountinfo' => $accountinfo,
                    'limitStatus' => $limitStatus,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionLiulist() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $liuinfo = Webunion_flow_settlement::find()->where(['user_id' => $user_id, 'status' => 'SUCCESS'])->all();
// print_r($liulinfo);exit;
        $jsinfo = $this->getWxParam();
        $this->getView()->title = "流量列表";
        return $this->render('liulist', [
// 'haoythree' => $haoythree,
                    'liuinfo' => $liuinfo,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionInformation() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $bobao = Webunion_profit_detail::find()->where(['user_id' => $user_id, 'profit_type' => 2])->all();
        $userinfo = array();
        $loaninfo = array();
        $investinfo = array();
//$total11 = 0;
        foreach ($bobao as $k => $v) {
//$total11+=$v->profit_amount;
            if ($v->type == 1 || $v->type == 2) {
//为好友id
//echo '1';
                $userinfo[] = Webunion_profit_detail::find()->joinWith('user')->where([Webunion_profit_detail::tableName() . '.type' => array(1, 2)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
            } else if ($v->type == 3 || $v->type == 4) {
//为借款id
//echo '2';
                $loan_id = $v->profit_id;
                $loaninfo[$k] = Webunion_profit_detail::find()->joinWith('loan')->where([Webunion_profit_detail::tableName() . '.type' => array(3, 4)])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $loaninfo[$k]['loan']['user_id'];

                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $loaninfo[$k]['loan']['user_id'] = 'bob';
                } else {
                    $loaninfo[$k]['loan']['user_id'] = $usfo->realname;
                }
            } else if ($v->type == 5) {
//投资id
// echo '3';
                $invest_id = $v->profit_id;
                $investinfo[$k] = Webunion_profit_detail::find()->joinWith('invest')->where([Webunion_profit_detail::tableName() . '.type' => 5])->andFilterWhere(['IN', Webunion_profit_detail::tableName() . '.`profit_id`', $v->profit_id])->asarray()->one();
                $user_id = $investinfo[$k]['invest']['user_id'];
                $usfo = User::find()->select(array('realname'))->where(['user_id' => $user_id])->one();
                if (empty($usfo) && !isset($usfo)) {
                    $investinfo[$k]['invest']['user_id'] = 'bob';
                    $investinfo[$k]['invest']['amount'] = 0.00;
                } else {
                    $investinfo[$k]['invest']['user_id'] = $usfo->realname;
                }
            }
        }

        $gao = Webunion_notice::find()->all();
        $jsinfo = $this->getWxParam();
        return $this->render('information', [
// 'haoythree' => $haoythree,
                    'bobao' => $bobao,
                    'gao' => $gao,
                    'userinfo' => $userinfo,
                    'loaninfo' => $loaninfo,
                    'investinfo' => $investinfo,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionQuxie() {
        $open_id = $this->getVal('openid');
        if (empty($open_id)) {
            return $this->redirect('https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . self::$_appid . '&redirect_uri=' . Yii::$app->params['app_url'] . '/dev/reg/login&response_type=code&scope=snsapi_base&state=xhh123#wechat_redirect');
        }
        $user = User::find()->select(array('user_id'))->where(['openid' => $open_id])->one();
        $user_id = $user->user_id;
        $accountinfo = Webunion_account::find()->where(['user_id' => $user_id])->one();
        if (empty($accountinfo) && !isset($accountinfo)) {
            $total_history_interest = 0.00;
            $total_on_interest = 0.00;
        } else {
            $total_history_interest = $accountinfo->total_history_interest;
            $total_on_interest = $accountinfo->total_on_interest;
        }
        //print_r($accountinfo);exit;
        $jsinfo = $this->getWxParam();
        return $this->render('quxie', [
                    'total_history_interest' => $total_history_interest,
                    'total_on_interest' => $total_on_interest,
                    'jsinfo' => $jsinfo
        ]);
    }

    public function actionLiusave() {
        $mobile = $_POST['mobile'];
        //$mobile = '15101151220';
        $arr = Http::mobileHome($mobile, 'json');
        if (!empty($arr)) {
            if ($arr['catName'] == '中国移动') {
                //$yd = array(10, 30, 70);
                $yd = array(30, 70, 150);
                echo json_encode($yd);
                exit;
            } else if ($arr['catName'] == '中国联通') {
                $lt = array(20, 50, 100);
                echo json_encode($lt);
                exit;
            } else if ($arr['catName'] == '中国电信') {
                //$dx = array(5, 50, 100);
                $dx = array(30, 50, 100);
                echo json_encode($dx);
                exit;
            } else {
                $lt = array(00, 00, 00);
                echo json_encode($lt);
                exit;
            }
        } else {
            $lt = array(00, 00, 00);
            echo json_encode($lt);
            exit;
        }
    }

    public function actionLweb() {
        Logger::errorLog(print_r($_POST, true), 'web_settlement_post');
        $msg_id = $_POST['msg_id'];
        $result_code = $_POST['exec_result'];

        if (empty($msg_id)) {
            $ret = array('ret' => 1, 'msg' => '参数错误');
            echo json_encode($ret);
            exit;
        }

        $weblist = Webunion_flow_settlement::find()->where(['flow_id' => $msg_id])->one();
        if (!empty($weblist) && isset($weblist)) {
            if ($result_code == '0') {
                //return $msg_id;
                //更新流量表 状态为成功

                $sql = "update " . Webunion_flow_settlement::tableName() . " set status='SUCCESS' ,version=version+1 where flow_id=" . $msg_id;
                Yii::$app->db->createCommand($sql)->execute();

                $ret = array('ret' => 0, 'msg' => '成功');
                echo json_encode($ret);
                exit;
            } else {
                $sql = "update " . Webunion_flow_settlement::tableName() . " set status='FAILED' where flow_id=" . $msg_id;
                Yii::$app->db->createCommand($sql)->execute();
                $sql = "update " . Webunion_account::tableName() . " set total_on_flow = total_on_flow-$weblist->flow_amount ,version=version+1  where user_id= $weblist->user_id";
                Yii::$app->db->createCommand($sql)->execute();
                $ret = array('ret' => 2, 'msg' => '失败');
                echo json_encode($ret);
                exit;
            }
        } else {
            $ret = array('ret' => 5, 'msg' => '该msg_id不存在');
            echo json_encode($ret);
            exit;
        }
    }

    //提取流量
    public function actionLiuincome() {
        $user_id = $_POST['user_id'];
        $flow_amount = $_POST['flow_amount'];
        $mobile = $_POST['mobile'];

        $begin_time = '2016-04-30 00:00:00';
        $end_time = '2016-04-30 23:59:59';
        $now_time = date('Y-m-d H:i:s');
        if ($now_time < $begin_time || $now_time > $end_time) {
            $ret = array('ret' => 1, 'msg' => '因银行系统升级维护，可提现时间暂时调整为4月30日00:00-24:00，给您造成不便，请您谅解！');
            echo json_encode($ret);
            exit;
        }

        $arr = Http::mobileHome($mobile, 'json');
        if ($arr['catName'] == '中国移动') {
            $type = 1;
        } else if ($arr['catName'] == '中国联通') {
            $type = 2;
        } else if ($arr['catName'] == '中国电信') {
            $type = 3;
        } else {
            $type = 0;
        }

        if (empty($user_id) || empty($flow_amount) || empty($mobile)) {
            $ret = array('ret' => 1, 'msg' => '请返回重试！');
            echo json_encode($ret);
            exit;
        }

        //每个用户每天只能操作1次
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $count = Webunion_flow_settlement::find()->where("user_id=$user_id and create_time >= '$begintime' and create_time <= '$endtime'")->count();
        if ($count >= 2) {
            $ret = array('ret' => 3, 'msg' => '您今天已经提过2次了，请明天再来~~');
            echo json_encode($ret);
            exit;
        }

        $model = new Webunion_flow_settlement();
        $model->settlement_id = date('Ymdhis') . rand(1000, 9999);
        $model->user_id = $user_id;
        $model->mobile_type = $type;
        $model->flow_amount = $flow_amount;
        $model->mobile = $mobile;
        $model->status = "INIT";
        $model->create_time = date('Y-m-d H:i:s');
        $model->last_modify_time = date('Y-m-d H:i:s');
        $model->version = 1;
        if ($model->save()) {
            //流量充值
            $postdata = array(
                'mobile' => $mobile,
                'package' => $flow_amount,
                'type' => 1
            );
            $openApi = new Apihttp;
            $res = $openApi->mobileRecharge($postdata);
            $id = Yii::$app->db->getLastInsertID();

            if ($res['res_code'] == '0000') {
                $msg_id = $res['res_msg']['msg_id'];
                //更新流量表 状态为成功
                $sql = "update " . Webunion_flow_settlement::tableName() . " set status='PROCESS',flow_id=$msg_id ,version=version+1 where id=" . $id;
                Yii::$app->db->createCommand($sql)->execute();
                $sql = "update " . Webunion_account::tableName() . " set total_on_flow = total_on_flow+$flow_amount ,version=version+1  where user_id= $user_id";
                Yii::$app->db->createCommand($sql)->execute();
                $ret = array('ret' => 0, 'msg' => '成功');
                echo json_encode($ret);
                exit;
            } else {
                //打款失败，修改l流量记录状态
                $sql = "update " . Webunion_flow_settlement::tableName() . " set status='FAILED' where id=" . $id;
                Yii::$app->db->createCommand($sql)->execute();
                $ret = array('ret' => 2, 'msg' => '请稍候再试~~');
                echo json_encode($ret);
                exit;
            }
        } else {

            $ret = array('ret' => 2, 'msg' => '系统错误');
            echo json_encode($ret);
            exit;
        }
    }

    //提现收益
    public function actionOutincome() {
        $user_id = $_POST['user_id'];
        $outincome = $_POST['outincome'];
        $bank_id = $_POST['bank_id'];
        $shouxf = $_POST['shouxf'];

        $begin_time = '2016-04-30 00:00:00';
        $end_time = '2016-04-30 23:59:59';
        $now_time = date('Y-m-d H:i:s');
        if ($now_time < $begin_time || $now_time > $end_time) {
            $ret = array('ret' => 1, 'msg' => '因银行系统升级维护，可提现时间暂时调整为4月30日00:00-24:00，给您造成不便，请您谅解！');
            echo json_encode($ret);
            exit;
        }

        if (empty($user_id) || $outincome < 10) {
            $ret = array('ret' => 1, 'msg' => '请返回重试！');
            echo json_encode($ret);
            exit;
        }
//每个用户每天只能操作1次
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $count = Account_settlement::find()->where("user_id=$user_id and create_time >= '$begintime' and create_time <= '$endtime' and type=4")->count();
        if ($count >= 1) {
            $ret = array('ret' => 3, 'msg' => '您今天已经提过了，请明天再来~~');
            echo json_encode($ret);
            exit;
        }

        $time_1 = "00:00";
        $time_2 = "07:00";
        if (date('H:i') > $time_1 && date('H:i') < $time_2) {
            $ret = array('ret' => 11, 'msg' => '0点至6点暂停提现业务');
            echo json_encode($ret);
            exit;
        }
//1.生成提现记录
        $model = new Account_settlement();
        $model->settlement_id = date('Ymdhis') . rand(1000, 9999);
        $model->user_id = $user_id;
        $model->bank_id = $bank_id;
        $model->amount = $outincome;
        $model->fee = $shouxf;
        $model->type = 4;
        $model->status = "INIT";
        $model->create_time = date('Y-m-d H:i:s');
        if ($model->save()) {
            $account_settlement_id = $model->attributes['id'];
//2.调中信出款接口
            $userinfo = User::find()->where(['user_id' => $user_id])->one();
            $userbank = User_bank::find()->where(['user_id' => $user_id, 'id' => $bank_id])->one();
            $user_mobile = $userinfo->mobile;
            $user_name = $userinfo->realname;
//持卡人姓名
            $guest_account_name = $userinfo->realname;
//银行卡号
            $guest_account = $userbank->card;
            $guest_account_bank = $userbank->bank_name;
            $guest_account_province = '北京市';
            $guest_account_city = '北京市';
            $guest_account_bank_branch = $userbank->bank_name;
            $account_type = 0;
            $settle_amount = $outincome - $shouxf;
            $order_id = date('Ymdhis') . rand(100000, 999999);
            if ($settle_amount >= 500) {
                $loan_id = $account_settlement_id;
                $admin_id = -1;
                $settle_request_id = $order_id;
                $real_amount = $settle_amount;
                $settle_fee = 0;
                $rsp_code = '0000';
                $remit_status = 'INIT';
                $create_time = date('Y-m-d H:i:s', time());

                $sql = "insert into " . User_remit_list::tableName() . "(loan_id,admin_id,settle_request_id,real_amount,settle_fee,settle_amount,rsp_code,remit_status,create_time,bank_id,type,user_id,order_id) ";
                $sql .= "value('" . $loan_id . "',$admin_id,'$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$remit_status','$create_time','$bank_id',5,'$user_id','$order_id')";
                $retinsert = Yii::$app->db->createCommand($sql)->execute();

                if ($retinsert >= 0) {
                    //打款成功，修改收益提现记录状态
                    $sql = "update " . Account_settlement::tableName() . " set status='SUCCESS' ,version=version+1 where id=" . $account_settlement_id;
                    Yii::$app->db->createCommand($sql)->execute();
                    $sql = "update " . Webunion_account::tableName() . " set total_on_interest = total_on_interest+$outincome ,version=version+1  where user_id= $user_id";
                    Yii::$app->db->createCommand($sql)->execute();
                    $ret = array('ret' => 0, 'msg' => '成功');
                    echo json_encode($ret);
                    exit;
                } else {
                    //记录一下日志,出款记录日志
                    Logger::errorLog($sql, 'ccount_settlement_failed');
                    $ret = array('ret' => 0, 'msg' => '成功咯');
                    echo json_encode($ret);
                    exit;
                }
            } else {
                $res = Http::balance($order_id, $user_mobile, $user_name, $settle_amount, $guest_account_name, $guest_account, $guest_account_bank, $guest_account_province, $guest_account_city, $guest_account_bank_branch, $account_type);
                if (($res->rsp_code == '0000') && ($res->remit_status == 'INIT')) {
                    //更新收益提现记录表状态
                    $loan_id = $account_settlement_id;
                    $admin_id = -1;
                    $settle_request_id = $res->settle_request_id;
                    $real_amount = $res->real_amount;
                    $settle_fee = $res->settle_fee;
                    $settle_amount = $res->settle_amount;
                    $rsp_code = $res->rsp_code;
                    $remit_status = $res->remit_status;
                    $create_time = date('Y-m-d H:i:s', time());
                    //给数据库的user_remit_list 插入一条数据
                    $sql = "insert into " . User_remit_list::tableName() . "(loan_id,admin_id,settle_request_id,real_amount,settle_fee,settle_amount,rsp_code,remit_status,create_time,bank_id,type,user_id,order_id) ";
                    $sql .= "value('" . $loan_id . "',$admin_id,'$settle_request_id','$real_amount ','$settle_fee','$settle_amount','$rsp_code','$remit_status','$create_time','$bank_id',5,'$user_id','$order_id')";
                    $retinsert = Yii::$app->db->createCommand($sql)->execute();

                    if ($retinsert >= 0) {
                        //打款成功，修改收益提现记录状态
                        $sql = "update " . Account_settlement::tableName() . " set status='SUCCESS' ,version=version+1 where id=" . $account_settlement_id;
                        Yii::$app->db->createCommand($sql)->execute();
                        $sql = "update " . Webunion_account::tableName() . " set total_on_interest = total_on_interest+$outincome ,version=version+1  where user_id= $user_id";
                        Yii::$app->db->createCommand($sql)->execute();
                        $ret = array('ret' => 0, 'msg' => '成功');
                        echo json_encode($ret);
                        exit;
                    } else {
                        //记录一下日志,出款记录日志
                        Logger::errorLog($sql, 'ccount_settlement_failed');
                        $ret = array('ret' => 0, 'msg' => '成功咯');
                        echo json_encode($ret);
                        exit;
                    }
                } else {
                    //打款失败，修改收益提现记录状态
                    $sql = "update " . Account_settlement::tableName() . " set status='FAILED' where id=" . $account_settlement_id;
                    Yii::$app->db->createCommand($sql)->execute();
                    $ret = array('ret' => 2, 'msg' => '请稍候再试~~');
                    echo json_encode($ret);
                    exit;
                }
            }
        } else {
            $ret = array('ret' => 3, 'msg' => '请检查你的网络');
            echo json_encode($ret);
            exit;
        }
    }

}
