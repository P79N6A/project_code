<?php

namespace app\commands\stageshistory;

use app\models\news\OverdueLoan;
use app\models\news\OverdueLoanFlows;
use yii\console\Controller;

/**
 * 同步借款表逾期数据到逾期账单表  每天一次0点执行
 * Class CharuserloanController
 * @package app\commands
 * 测试  D:\phpStudy\php\php-7.0.12-nts\php.exe D:\work\yiyiyuanOnline\yii charuserloan
 * C:\wamp64\bin\php\php7.0.0\php.exe C:\wamp64\www\yiyiyuan\yii stageshistory/charuserloan/index
 */
class ResetController extends Controller {

    public function actionIndex() {
        $limit = 2000;
        $total = (new OverdueLoanFlows())->find()->count();
        $pages = ceil($total / $limit);
        $id    = 0;
        echo "共" . $total . '条数据';
        for ($i = 0; $i < $pages; $i ++) {
            $where = [
                "AND",
                ['>', 'id', $id]
            ];
            $info    = OverdueLoanFlows::find()->where($where)->indexBy('id')->orderBy('id')->limit($limit)->all();
            if (empty($info)) {
                exit();
            }
            $id = max(array_keys($info));
            foreach ($info as $key => $val) {
                $res = OverdueLoan::find()->where(['loan_id' => $val['loan_id']])->one();
                if ($res) {
                    $res->is_push = 0;
                    $res->save();
                }
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
