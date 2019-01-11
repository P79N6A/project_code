<?php

namespace app\commands;

use app\models\news\User_loan;
use app\models\news\YiLoanNotify;
use app\models\news\Loan_mapping;
use Yii;
use yii\console\Controller;

/**
 * 4.1.5借款结果通知(Loan results notice) LoanresnoticeController.php  定时任务
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii loanresnotice > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe d:\www\yiyiyuan\yii loanresnotice/index
 * 定时逻辑：
 *      1.取loan表中修改时间前一天区间为30分钟内的状态为6的借款　
 *      2.对借款(loan)的status状态进行榕树通知并记录在yi_loan_notify表中
 */

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class LoanresnoticeController extends Controller {

    private $appId = 1419;
    // 命令行入口文件
    public function actionIndex()
    {
        $limit = 500;
        //30分钟区间
        $modefy_start_time = date("Y-m-d H:i:s", strtotime("-15 minutes"));
        $modefy_end_time = date("Y-m-d H:i:s", strtotime("-5 minutes"));
        $time_in = date("Y-m-d H:i:s", strtotime("-1 day"));
        $status = array(3,6,7,9);
        $where_config = [
            'AND',
            [User_loan::tableName().".source" => 6],
            ['BETWEEN', User_loan::tableName().'.last_modify_time', $modefy_start_time, $modefy_end_time],
            [">",User_loan::tableName().".create_time", $time_in],
            ['IN',User_loan::tableName().'.status' ,$status],
        ];
        
        $user_loan_sql = User_loan::find()->where($where_config);
        $total = $user_loan_sql->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $loan_info = $user_loan_sql->offset($i * $limit)->limit($limit)->all();
            if (!empty($loan_info)){
                foreach($loan_info as $key => $value){
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
    private function setLoanNotifyByDB($loaninfo)
    {
        $status = $this->statusInterfactUrl($loaninfo);
        $order_mapping_info = (new Loan_mapping())->newestLoanmapping($loaninfo->loan_id);
        if (empty($order_mapping_info) || empty($order_mapping_info->order_id)) return false;

        $get_where_config = [
            'loan_id'=>$loaninfo->loan_id,
            'status' => $status,
            'channel' => strval($this->appId),
        ];
        $loan_notify = new YiLoanNotify();
        $notify_info = $loan_notify->find()->where($get_where_config)->one();
        if (!empty($notify_info)) return false;
        $data_set = [
            'loan_id'=>$loaninfo->loan_id,
            'channel_loan_id' => strval($order_mapping_info->order_id),
            'status' => $status,
            'notify_num' => 1,
            'channel' => strval($this->appId),
            'remit_status'=>'INIT',
        ];

        $ret = $loan_notify->saveNotify($data_set);
        return $ret;
    }

    /**
     * 判断借款状态值
     * @param $loaninfo
     * @return int
     */
    private function statusInterfactUrl($loaninfo)
    {
        $loan_status = empty($loaninfo->status) ? '' : $loaninfo->status;
        $extend_status = empty($loaninfo->loanextend->status) ? '':$loaninfo->loanextend->status;
        //审核通过
        $audited_loan_status = [6, 9];
        $audited_extend_status = ['AUTHED', 'PREREMIT', 'WAITREMIT', 'WILLREMIT', 'DOREMIT'];
        if (in_array($loan_status, $audited_loan_status)){
            if (in_array($extend_status, $audited_extend_status)){
                return 6;
            }
        }
        //审核失败
        $audit_failure_loan_status = [7, 3];
        if (in_array($loan_status, $audit_failure_loan_status)){
            return 7;
        }
        return 6;
    }

}
