<?php

namespace app\commands\Signalling;

/**
 *  有信令是推送数据保存
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii signalling/signalpush
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii signalling/signalpush
 */
use app\commands\BaseController;
use app\commonapi\Keywords;
use app\models\news\Push_yxl;
use app\models\news\User_loan_extend;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SignalpushController extends BaseController {

    private $limit = 200;

    public function actionIndex($status = 3) {
        if ($status == 1) {
            $type = 2;
            $extend_status = 'SUCCESS';
        } elseif ($status == 2) {
            $type = 2;
            $extend_status = 'REJECT';
        } else {
            $type = 1;
            $extend_status = 'TB-SUCCESS';
        }
        $start_date = date('Y-m-d H:i:00', strtotime('-5 minutes'));
        $end_date = date('Y-m-d H:i:00');
        $where = [
            'AND',
            ['>=', User_loan_extend::tableName() . '.last_modify_time', $start_date],
            ['<', User_loan_extend::tableName() . '.last_modify_time', $end_date],
            [User_loan_extend::tableName() . '.status' => $extend_status],
        ];
        $sql = User_loan_extend::find()->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $pushYxl = $sql->offset($i * $this->limit)->limit($this->limit)->all();
            if (!empty($pushYxl)) {
                $result = $this->addYxl($pushYxl, $type, $status);
                echo "成功插入" . $result . "条";
            }
        }
    }

    public function addYxl($loanExtend, $type, $status) {
        $pushYxlModel = new Push_yxl();
        $h5_open = Keywords::h5Open();
        foreach ($loanExtend as $k => $v) {
            $res = $pushYxlModel->getYxlInfo($v->loan_id, $type, $status);
            if (!empty($res)) {
                continue;
            }
            $notify_status = 0;
            if($h5_open==2&&$type==1){
                $notify_status = 4;
            }
            $nowTime = date('Y-m-d H:i:s');
            $condition[] = [
                'user_id' => $v->user_id,
                'loan_id' => $v->loan_id,
                'loan_status' => $status,
                'type' => $type,
                'notify_status' => $notify_status,
                'notify_time' => $nowTime,
                'last_modify_time' => $nowTime,
                'create_time' => $nowTime
            ];
        }
        $res = null;
        if (!empty($condition)) {
            $res = $pushYxlModel->insertBatch($condition);
        }
        return $res;
    }

}
