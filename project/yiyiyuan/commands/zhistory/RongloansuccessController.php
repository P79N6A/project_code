<?php
/**
 * 融360 获取借  169.款放款失败   170放款成功
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/19
 * Time: 10:31
 */
namespace app\commands;

use app\models\news\RongLoan;
use app\models\news\User_loan;
use app\models\news\YiLoanNotify;
use Yii;
use yii\console\Controller;

class RongloansuccessController extends Controller
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
        $status = array(7, 9);
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
        if (!$status) return false;
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
        //放款成功
        if ($loan_status == 9 && $extend_status=="SUCCESS")
        {
            return 9;
        }
        //放款失败
        if ($extend_status == "FAIL" || $extend_status=="REJECT")
        {
            return 10;
        }
        return false;
    }
}