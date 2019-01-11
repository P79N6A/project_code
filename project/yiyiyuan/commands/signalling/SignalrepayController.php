<?php

namespace app\commands\Signalling;

/**
 *  智融钥匙完成还款推送数据保存
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii signalling/signalrepay
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii signalling/signalrepay
 */
use app\commands\BaseController;
use app\models\news\Push_yxl;
use app\models\news\User_loan;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SignalrepayController extends BaseController {

    private $limit = 200;

    public function actionIndex() {
        $start_date = date('Y-m-d H:i:00', strtotime('-10 minutes'));
        $end_date = date('Y-m-d H:i:00');
        $where = [
            'AND',
            ['>=', User_loan::tableName() . '.last_modify_time', $start_date],
            ['<', User_loan::tableName() . '.last_modify_time', $end_date],
            [User_loan::tableName() . '.status' => 8],
            ['IN', User_loan::tableName() . '.business_type', [1, 4, 5, 6]]
        ];
        $sql = User_loan::find()->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $pushYxl = $sql->offset($i * $this->limit)->limit($this->limit)->all();
            if (!empty($pushYxl)) {
                $result = $this->addYxl($pushYxl, 2, 5);
                echo "成功插入" . $result . "条";
            }
        }
    }

    public function addYxl($userLoan, $type, $status) {
        $pushYxlModel = new Push_yxl();
        foreach ($userLoan as $k => $v) {
            $res = $pushYxlModel->getYxlInfo($v->loan_id, $type, $status);
            if ($v->settle_type == 2 || !empty($res)) {
                continue;
            }
            $nowTime = date('Y-m-d H:i:s');
            $condition[] = [
                'user_id' => $v->user_id,
                'loan_id' => $v->parent_loan_id,
                'loan_status' => $status,
                'type' => $type,
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
