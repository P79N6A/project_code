<?php

namespace app\commands\picup;

/**
 * 驳回订单
 * linux : sudo -u www /data/wwwroot/yiyiyuan/yii mall/orderreject
 * windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii mall/orderreject
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\Loan_pic;
use app\models\news\User_loan;
use Exception;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class GetpicController extends BaseController {

    public $a;

    public function actionIndex() {
        $this->a = 1;
        $successNum = 0;
        $errNum = 0;
        $start_time = date('Y-m-d 00:00:00', strtotime("+1 days"));
        $where = ['status' => [0, 3]];
        $total = Loan_pic::find()->where($where)->count();
        $limit = 200;
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $datas = Loan_pic::find()->where($where)->limit($limit)->all();
            if (empty($datas)) {
                break;
            }
            $ids = ArrayHelper::getColumn($datas, 'id');
            (new Loan_pic())->lockAll($ids);
            foreach ($datas as $key => $value) {
                $result = $this->todo($value);
                Logger::dayLog('renewamount', $value->loan_id, $result);
                if ($result) {
                    $successNum++;
                } else {
                    $errNum++;
                }
            }
            Logger::dayLog('getpic', 'success' . $successNum, 'error' . $errNum);
        }
        echo date('Y-m-d H:i:s') . ' all: ' . $total . ' success: ' . $successNum . ' err: ' . $errNum;
    }

    public function actionLoanid($loan_id) {
        $successNum = 0;
        $errNum = 0;
        $value = Loan_pic::find()->where(['loan_id' => $loan_id, 'status' => 0])->one();
        $result = $this->todo($value);
        Logger::dayLog('renewamount', $value->loan_id, $result);
        if ($result) {
            $successNum++;
        } else {
            $errNum++;
        }
        echo date('Y-m-d H:i:s') . ' success: ' . $successNum . ' err: ' . $errNum;
    }

    private function todo($loanpic) {
        $loan = User_loan::findOne($loanpic->loan_id);
        $loanpic->lock();
        if (SYSTEM_ENV == 'prod') {
            $pageUrl = 'http://localhost:8081/borrow/loanpic/pagepic?loan_id=' . $loanpic->loan_id;
            $js_url = '/data/wwwroot/weixin/web/buypic/js/runImage.js';
        } else {
            $pageUrl = 'http://yyytest2.xianhuahua.com/borrow/loanpic/pagepic?loan_id=' . $loanpic->loan_id;
            $js_url = '/data/wwwroot/yiyiyuan/web/buypic/js/runImage.js';
        }
        $pic = "/" . date('Ym/d', strtotime($loan->create_time)) . '/' . $loanpic->loan_id . $this->a . '.png';
        $this->a++;
        $picUrl = '/data/wwwroot/biz' . $pic;
        Logger::createdir(dirname($picUrl));
        try {
            system("/usr/local/phantomjs/bin/phantomjs $js_url $pageUrl $picUrl");
            $loanpic->refresh();
            if (file_exists($picUrl)) {
                Logger::dayLog('getpic', file_exists($picUrl), $loanpic->loan_id);
                $result = $loanpic->savePic($pic);
//                $result = $loanpic->savRepeat();
            } else {
                Logger::dayLog('getpic', 'fail', $loanpic->loan_id);
                $result = $loanpic->savRepeat();
                return false;
            }
            sleep(3);
        } catch (Exception $ex) {
            Logger::dayLog('getpic', 'catch', $loanpic->loan_id);
            $result = $loanpic->savRepeat();
            return false;
        }
    }

}
