<?php

namespace app\commands;

use app\models\news\User_quota;
use Yii;
use app\commonapi\Logger;
use yii\console\Controller;
use yii\helpers\ArrayHelper;
use app\models\news\User_quota_list;

set_time_limit(0);
ini_set('memory_limit', '-1');

class UserquotaController extends Controller {

    public function actionIndex() {
        $limit = 500; //每次循环处理数量
        $quota_model = User_quota_list::find()->joinWith('userquota', TRUE, 'LEFT JOIN')->where([User_quota_list::tableName() . '.status' => 1]);
        $total = $quota_model->count();
        $pages = ceil($total / $limit);
        $success = 0;
        $error = 0;
        $user_quota_model = new User_quota();
        for ($i = 0; $i < $pages; $i++) {
            $quota_list = $quota_model->limit($limit)->all();
            if (empty($quota_list)) {
                break;
            }
            $td_id_data = ArrayHelper::getColumn($quota_list, 'id');
            User_quota_list::updateAll(['status' => 2], ['id' => $td_id_data]);
            foreach ($quota_list as $value) {
                
                $res = $user_quota_model->updateNewQuota($value->userquota,$value->user_id, $value->quota, 'BI批量降额');
                if ($res) {
                    $value->updateStatus($value->user_id, 3);
                    $success++;
                } else {
                    $value->updateStatus($value->user_id, 4);
                    $error++;
                    Logger::dayLog('quota/error', 'error：修改提现额度失败:user_id : ' . $value->user_id);
                }
            }
        }
        echo "成功更改提额条数" . $success . ",失败条数" . $error;
    }

}
