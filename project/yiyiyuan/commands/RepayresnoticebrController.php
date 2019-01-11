<?php

namespace app\commands;


use app\commonapi\Common;
use app\commonapi\Logger;
use app\commonapi\RSA;
use app\models\news\User_loan;
use app\models\news\Loan_repay;
use app\commonapi\Http;
use app\models\news\Loan_mapping;
use app\models\news\YiLoanNotify;
use Yii;
use yii\console\Controller;

/**
 * 百融补充推送定时 RepayresnoticebrController.php  定时任务
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii loanresnotice > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe d:\www\yiyiyuan\yii loanresnotice/index
 */

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class RepayresnoticebrController extends Controller {

    public $appId = 1419;
    public function init() {
        parent::init();
    }
    // 命令行入口文件
    public function actionIndex()
    {
        $limit = 500;
        //一天之内
        $modefy_start_time = date("Y-m-d 00:00:00", strtotime("-1 day"));
        $modefy_end_time = date("Y-m-d 23:59:59", strtotime("-1 day"));
        $where_config = [
            'AND',
            [User_loan::tableName().".source" => 6],
            [User_loan::tableName().".status" => 8],
            [Loan_repay::tableName().".status" => 1],
            ['!=', Loan_repay::tableName().".source", 7],
            ['BETWEEN', User_loan::tableName().'.repay_time', $modefy_start_time, $modefy_end_time],
//            [">", User_loan::tableName().".create_time", '2017-07-05 00:00:00'],//处理结束删除此条件
        ];
        $repay_info = Loan_repay::find()
                ->select(Loan_repay::tableName().".loan_id")
                ->distinct()
                ->leftJoin(User_loan::tableName(), User_loan::tableName() . ".loan_id = " . Loan_repay::tableName() . ".loan_id")
                ->where($where_config)
                ->all();
        if (!empty($repay_info)){
            foreach($repay_info as $key => $value){
                $this->setLoanNotifyByDB($value);
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
        $loan_info = User_loan::find()->where(['loan_id'=>$repayinfo->loan_id])->one();
        if (empty($loan_info)) return false;
        $order_mapping_info = (new Loan_mapping())->newestLoanmapping($repayinfo->loan_id);
        if (empty($order_mapping_info) || empty($order_mapping_info->order_id)) return false;
        $get_where_config = [
            'loan_id'=>$repayinfo->loan_id,
            'status' => 8,
            'channel' => strval($this->appId),
            'remit_status' => 'SUCCESS',
        ];
        $loan_notify = new YiLoanNotify();
        $notify_info = $loan_notify->find()->where($get_where_config)->one();
        if (!empty($notify_info)) return false;
        $data_set = [
            'loan_id'=>$loan_info->loan_id,
            'channel_loan_id' => strval($order_mapping_info->order_id),
            'status' => 8,
            'notify_num' => 1,
            'channel' => strval($this->appId),
            'remit_status'=> "SUCCESS",
        ];
        $ret = $loan_notify->saveNotify($data_set);
        return $ret;
    }
}
