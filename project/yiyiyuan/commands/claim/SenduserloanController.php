<?php
namespace app\commands\claim;

use app\commands\BaseController;
use app\commonapi\Apidepository;
use app\commonapi\Logger;
use app\models\news\PushUserLoan;
use yii\helpers\ArrayHelper;

class SenduserloanController extends BaseController {
    private $limit = 200;
    private $debug = false;

    public function actionIndex() {
        $countNum = 0;
        $successNum = 0;
        $where = [
            'send_status' => 0
        ];
        $sql = (new PushUserLoan())->find()->where($where)->orderBy('status desc');
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            if ($this->debug && $i > 0) {
                break;
            }
            $o_user_loan = $sql->limit($this->limit)->all();
            if (empty($o_user_loan)) {
                break;
            }
            $ids = ArrayHelper::getColumn($o_user_loan, 'id');
            (new PushUserLoan())->updateAll(['send_status' => '1', 'last_modify_time' => date('Y-m-d H:i:s')], ['id' => $ids, 'send_status' => '0']);
            $countNum += count($o_user_loan);
            $lists = $this->doArray($o_user_loan);
            $result = $this->doSend($lists);
            if (!$result) {
                continue;
            }
            $successNum += $result;
        }
        Logger::dayLog('script/claim/senduserloan', date('Y-m-d H:i:s'), '在贷需处理总数：' . $countNum, '成功：' . $successNum);
        exit('count:' . $countNum . ';success:' . $successNum);
    }

    private function doArray($o_user_loan) {
        $list = [];
        if (empty($o_user_loan)) {
            return $list;
        }
        foreach ($o_user_loan as $key => $value) {
            if (empty($value) || !is_object($value)) {
                break;
            }
            $list[$key]['user_id'] = $value->user_id;
            $list[$key]['loan_id'] = $value->loan_id;
            $list[$key]['amount'] = $value->amount;
            $list[$key]['status'] = $value->status;
        }
        return $list;
    }

    private function doSend($lists) {
        if (empty($lists) || !is_array($lists)) {
            return 0;
        }
        $apiDep = new Apidepository();
        $ret = $apiDep->sendUserLoan($lists);
        $result = json_decode($ret, true);
        if (isset($result['rsp_code']) && $result['rsp_code'] != '0000') {
            return 0;
        }
        $success_list = [];
        $fail_list = [];
        foreach ($result as $item) {
            if ($item['rsp_code'] == '0000') {
                $success_list[] = $item['loan_id'];
            } else {
                $fail_list[] = isset($item['loan_id']) ? $item['loan_id'] : '';
            }
        }
        (new PushUserLoan())->updateAll(['send_status' => '2', 'last_modify_time' => date('Y-m-d H:i:s')], ['loan_id' => $success_list, 'send_status' => '1']);
        if (!empty($fail_list)) {
            Logger::dayLog('script/claim/senduserloan', '推送失败记录', $fail_list);
        }
        return count($success_list);
    }
}