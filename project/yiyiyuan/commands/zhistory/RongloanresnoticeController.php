<?php
/**
 * 融360审核数据收集
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/18
 * Time: 19:29
 */
namespace app\commands;

use app\models\news\RongLoan;
use app\models\news\User_loan;
use app\models\news\YiLoanNotify;
use app\models\news\Loan_mapping;
use Yii;
use yii\console\Controller;

class RongloanresnoticeController extends Controller
{

    private $appId = 3300063;
    // 命令行入口文件
    public function actionIndex()
    {
        $limit = 500;
        //30分钟区间
        $modefy_start_time = date("Y-m-d H:i:s", strtotime("-10 minutes"));
        $modefy_end_time = date("Y-m-d H:i:s", time());
        $time_in = date("Y-m-d H:i:s", strtotime("-1 day"));
        $status = array(3,6,7,9);
        $where_config = [
            'AND',
            [User_loan::tableName().".source" => 8],
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
        $order_mapping_info = RongLoan::find()->where(["loan_id"=>$loaninfo->loan_id])->one();
        if (empty($order_mapping_info) || empty($order_mapping_info->loan_id)) return false;

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
            'channel_loan_id' => strval($order_mapping_info->r_loan_id),
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