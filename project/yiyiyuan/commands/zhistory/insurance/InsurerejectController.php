<?php

/**
 * 投保驳回
 */

namespace app\commands\insurance;

use app\models\news\Insurance;
use app\models\news\Insure;
use app\models\service\UserloanService;
use yii\console\Controller;
use app\models\news\User_loan;
use app\commonapi\Logger;
use Yii;

set_time_limit(0);
ini_set('memory_limit', '-1');

class InsurerejectController extends Controller {

    public function actionRejectinitial()
    {
        $this->listNotify($type = 0,$time = 24);
    }

    public function actionRejectoverdue()
    {
        $this->listNotify($type = 1,$time = 24);
    }

    public function actionRejectfail()
    {
        $this->listNotify($type = 2,$time = 2);
    }

    public function listNotify($is_chk,$time) {
        //如果状态是2 48小时驳回 状态0、1 24小时驳回
        $limit = 500;
        $time_start = date("Y-m-d H:i:s", strtotime("-3 day"));
        $time_end = date("Y-m-d H:i:s", strtotime("-".$time." hours"));
        $where = [
            'AND',
            ["BETWEEN", Insurance::tableName() . ".create_time", $time_start,$time_end],
            [Insurance::tableName() . '.is_chk' => $is_chk],
            [User_loan::tableName() . '.status' => 6],
        ];
        $insurance = Insurance::find()->joinWith('loan', true, 'LEFT JOIN')->where($where);
        $total = $insurance->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $insure_info = $insurance->offset($i * $limit)->limit($limit)->all();
            if (!empty($insure_info)) {
                $res = $this->reject($insure_info,$is_chk);
                echo "success:" . $res;
            }
        }
    }

    /**
     * 驳回（yi_insure）
     * @param $insure_info
     * @return string
     */
    private function reject($insure_info,$is_chk) {
        $success = 0;
        foreach ($insure_info as $k => $v) {
            if($is_chk == 1 || $is_chk == 2){
                $req = (new Insure)->getDateByReqId($v->req_id);
                if(!empty($req)){
                    continue;
                }
            }
            $res = (new UserloanService())->tbReject($v->loan_id);
            if($res){
                $success++;
            }else{
                Logger::dayLog('Insurere', '驳回失败：', $v->loan_id);
            }
        }
        return $success;
    }

}
