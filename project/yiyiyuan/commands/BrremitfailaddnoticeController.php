<?php

namespace app\commands;

use app\models\news\User_loan;
use app\models\news\User_loan_extend;
use app\models\news\YiLoanNotify;
use app\models\news\Loan_mapping;
use app\commonapi\Common;
use Yii;
use yii\console\Controller;

/**
 * 百融出款失败信息添加  定时任务
 * 定时逻辑：
 *      1.取user_loan表中状态为7，9并且extend状态为REJECT的信息
 *      2.通知表中如果存在审核通知则添加出款失败通知，如果不存在审核通知则添加驳回通知
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class BrremitfailaddnoticeController extends Controller {

    private $appId = 1419;

    // 命令行入口文件
    public function actionIndex() {
        $limit = 500;
        //20分钟区间
        $modefy_start_time = date("Y-m-d H:i:s", strtotime("-25 minutes"));
        $modefy_end_time = date("Y-m-d H:i:s", strtotime("-5 minutes"));
        $time_in = date("Y-m-d H:i:s", strtotime("-4 day"));
        $status = 7;
        $where_config = [
            'AND',
            [">", User_loan::tableName() . ".create_time", $time_in],
            ['BETWEEN', User_loan::tableName() . '.last_modify_time', $modefy_start_time, $modefy_end_time],
            [User_loan::tableName() . ".status" => 7],
            [User_loan::tableName() . ".source" => 6],
        ];

        $user_loan_sql = User_loan::find()->where($where_config);
        $total = $user_loan_sql->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $loan_info = $user_loan_sql->offset($i * $limit)->limit($limit)->all();
            if (!empty($loan_info)) {
                foreach ($loan_info as $key => $value) {
                    $this->setLoanNotifyByDB($value);
                }
            }
        }
    }

    /**
     * 记录借款结果通知表（yi_loan_notify）
     * @param $loaninfo
     * @return bool
     */
    private function setLoanNotifyByDB($loaninfo) {
        $order_mapping_info = (new Loan_mapping())->newestLoanmapping($loaninfo->loan_id);
        if (empty($order_mapping_info) || empty($order_mapping_info->order_id))
            return false;
        $get_where = [
            'loan_id' => $loaninfo->loan_id,
            'channel' => strval($this->appId),
        ];
        $notify_info = YiLoanNotify::find()->select('status')->andWhere($get_where)->asArray()->all();
        $data_set = array();
        if(empty($notify_info)){
            $data_set = [
                'loan_id' => $loaninfo->loan_id,
                'channel_loan_id' => strval($order_mapping_info->order_id),
                'status' => 7,
                'notify_num' => 1,
                'channel' => strval($this->appId),
                'remit_status' => 'INIT',
            ];
            $ret = (new YiLoanNotify())->saveNotify($data_set);
            return $ret;
        }
        $notify_info = Common::ArrayToString($notify_info, 'status');
        $notify_info_status = explode(',', $notify_info);
        if (in_array(9, $notify_info_status) && in_array(7, $notify_info_status))
            return false;
        if (!in_array(6, $notify_info_status) && !in_array(7, $notify_info_status)) {
            $data_set = [
                'loan_id' => $loaninfo->loan_id,
                'channel_loan_id' => strval($order_mapping_info->order_id),
                'status' => 7,
                'notify_num' => 1,
                'channel' => strval($this->appId),
                'remit_status' => 'INIT',
            ];
        } elseif (in_array(6, $notify_info_status) && !in_array(9, $notify_info_status)) {
            $data_set = [
                'loan_id' => $loaninfo->loan_id,
                'channel_loan_id' => strval($order_mapping_info->order_id),
                'status' => 9,
                'notify_num' => 1,
                'channel' => strval($this->appId),
                'remit_status' => 'FAIL',
            ];
        }
        if (!empty($data_set)) {
            $ret = (new YiLoanNotify())->saveNotify($data_set);
            return $ret;
        }
        return TRUE;
    }

}
