<?php

/**
 * 24小时未购买加速卡借款驳回
 */

namespace app\commands\Signalling;

use app\models\news\User_loan;
use app\models\service\UserloanService;
use yii\console\Controller;
use app\models\news\User_loan_extend;
use app\commonapi\Logger;
use Yii;

set_time_limit(0);
ini_set('memory_limit', '-1');

class SignalrejectController extends Controller {

    public function actionIndex($time) {
        //24小时驳回
        $limit = 500;
        $time_start = date("Y-m-d H:i:s", strtotime("-3 day"));
        $time_end = date("Y-m-d H:i:s", strtotime("-".$time." hours"));
        $where = [
            'AND',
            ["BETWEEN", User_loan_extend::tableName() . ".last_modify_time", $time_start,$time_end],
            [User_loan_extend::tableName() . '.status' => 'TB-SUCCESS'],
            [User_loan::tableName() . '.status' => 6],
        ];
        $list = User_loan_extend::find()->joinWith('loan', true, 'LEFT JOIN')->where($where);
        $total = $list->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $list_info = $list->offset($i * $limit)->limit($limit)->all();
            if (!empty($list_info)) {
                $res = $this->reject($list_info);
                echo "success:" . $res;
            }
        }
    }

    /**
     * 驳回
     * @param $list_info
     * @return int
     */
    private function reject($list_info) {
        $success = 0;
        foreach ($list_info as $k => $v) {
            $res = (new UserloanService())->tbReject($v->loan_id);
            if($res){
                $success++;
            }else{
                Logger::dayLog('Signal', '驳回失败：', $v->loan_id);
            }
        }
        return $success;
    }

}
