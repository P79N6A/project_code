<?php
namespace app\commands\claim;

/**
 * 还款--结束债权
 */
use app\commands\BaseController;
use app\commonapi\ApiSign;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Exchange;
use app\models\news\User_loan;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class RepayloanoverController extends BaseController
{
    private $limit = 100;

    // 到期刚兑
    public function actionIndex()
    {
        $end_date = date('Y-m-d 00:00:00', strtotime('+1 days'));
        $where = [
            'AND',
            [User_loan::tableName() . '.end_date' => $end_date],
            [Exchange::tableName() . '.type' => 1],
            [Exchange::tableName() . '.exchange' => 0],
        ];
        $total = User_loan::find()->joinWith('exchange', 'TRUE', 'LEFT JOIN')->where($where)->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $loan_list = User_loan::find()->joinWith('exchange', 'TRUE', 'LEFT JOIN')->where($where)->offset($i * $this->limit)->limit($this->limit)->all();
            if (empty($loan_list)) {
                break;
            }
            foreach ($loan_list as $key => $value) {
                $this->doLoan($value['loan_id']);
            }
        }
    }

    private function doLoan($loan_id)
    {
        $loan = User_loan::findOne($loan_id);
        $data = [
            'type' => !empty($loan->status == 8) ? 1 : 0, //1：完成还款；0：未完成还款
            'loan_id' => $loan_id,
            'over_time' => $loan->end_date,
        ];
        $signData = (new ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        $api = 'loan/loanoverreally';
        $url = Yii::$app->params['exchange_url'] . $api;
        $result = Http::interface_post($url, $signData);
        Logger::dayLog('depository/claim/repayloanover', $loan_id, $result);
        $res = json_decode($result, TRUE);
        if ($res) {
            $data_msg = json_decode($res['data'], TRUE);
            if ($data_msg['rsp_code'] == '0000') {
                Logger::dayLog('depository/claim/repayloanover_success', $signData, $res);
            } else {
                Logger::dayLog('depository/claim/repayloanover_error', $signData, $res);
            }
        }
    }
}
