<?php
/**
 * 融360还款成功数据
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/7/19
 * Time: 11:56
 */

namespace app\commands;

use app\commonapi\Common;
use app\commonapi\Logger;
use app\commonapi\RSA;
use app\models\news\RongLoan;
use app\models\news\User_loan;
use app\models\news\Loan_repay;
use app\commonapi\Http;
use app\models\news\Loan_mapping;
use app\models\news\YiLoanNotify;
use Yii;
use yii\console\Controller;
set_time_limit(0);
ini_set('memory_limit', '-1');
class RongrepayresnoticeController extends Controller
{

    public $appId = 3300063;

    public function init()
    {
        parent::init();
    }

    // 命令行入口文件
    public function actionIndex()
    {
        $limit = 500;
        //10分钟区间
        $modefy_start_time = date("Y-m-d H:i:s", strtotime("-10 minutes"));
        $modefy_end_time = date("Y-m-d H:i:s", time());
        $time_in = date("Y-m-d H:i:s", strtotime("-1 day"));
        $where_config = [
            'AND',
            [Loan_repay::tableName() . ".source" => 8],
            ['BETWEEN', 'last_modify_time', $modefy_start_time, $modefy_end_time],
            [">", Loan_repay::tableName() . ".createtime", $time_in],
        ];
        $user_repay_sql = Loan_repay::find()->where($where_config)->groupBy('loan_id, status');
        $total = $user_repay_sql->count();
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $repay_info = $user_repay_sql->offset($i * $limit)->limit($limit)->all();
            if (!empty($repay_info)) {
                foreach ($repay_info as $key => $value) {
                    $this->setLoanNotifyByDB($value);
                }
            }
        }
    }

    /**
     * 还款结果通知表（yi_loan_notify）
     * @param $repayinfo
     * @return bool
     */
    private function setLoanNotifyByDB($repayinfo)
    {
        $loan_info = User_loan::find()->where(['loan_id' => $repayinfo->loan_id])->one();
        if (empty($loan_info)) return false;
        $order_mapping_info = RongLoan::find()->where(["loan_id"=>$loan_info->loan_id])->one();
        if (empty($order_mapping_info) || empty($order_mapping_info->loan_id)) return false;
        $get_where_config = [
            'loan_id' => $repayinfo->loan_id,
            'status' => 8,
            'channel' => strval($this->appId),
            'remit_status' => 'SUCCESS',
        ];
        $loan_notify = new YiLoanNotify();
        $notify_info = $loan_notify->find()->where($get_where_config)->one();
        if (!empty($notify_info)) return false;
        if (in_array($repayinfo->status, array('-1','0','4'))){
            $remit_status = "FAIL";
        } elseif ($repayinfo->status == 1 && $loan_info->status == 8) {
            $remit_status = "SUCCESS";
        } else {
            return false;
        }
        $data_set = [
            'loan_id' => $loan_info->loan_id,
            'channel_loan_id' => strval($order_mapping_info->r_loan_id),
            'status' => 8,
            'notify_num' => 1,
            'channel' => strval($this->appId),
            'remit_status' => $remit_status,
        ];
        $ret = $loan_notify->saveNotify($data_set);
        return $ret;
    }
}