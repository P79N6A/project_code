<?php

/**
 * 生成出款
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii rongbaopayremit > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands\remit;

use app\models\news\User_loan_extend;
use app\models\news\User_loan;
use app\models\news\User_remit_list;
use app\commonapi\Logger;
use Yii;
use yii\helpers\ArrayHelper;

class CreateremitController extends \app\commands\BaseController {

    //命令行入口
    public function actionIndex() {
        //1 获取待出款的出款数据
        $last_modify_time = date('Y-m-d H:i:s', time() - 86400 * 2);
        $now_time = date('Y-m-d H:i:s');
        $sucess = 0;
        $where = [
            'and',
            [
                User_loan_extend::tableName() . '.status' => 'WILLREMIT',
            ],
            ['>=', User_loan_extend::tableName() . '.last_modify_time', $last_modify_time],
        ];

        $loan_list = User_loan_extend::find()->joinWith('loan', true, 'LEFT JOIN')->where($where)->limit(200)->all();
        $nums = is_array($loan_list) ? count($loan_list) : 0;
        Logger::dayLog('createremit', 'index', $last_modify_time . ' 至 ' . $now_time, '1共获取出款条数', $nums);
        if (empty($loan_list)) {
            return 0;
        }

        //2 锁定状态,避免下次重复处理，先将user_loan_extend的状态改为出款中状态DOREMIT
        $user_loan_extend = new User_loan_extend();
        $loan_extend_nums = $user_loan_extend->setAllDoremit($loan_list);
        Logger::dayLog('createremit', 'index', $last_modify_time . ' to ' . $now_time, '2 锁定user_loan_extend.status=DOREMIT条数', $loan_extend_nums);

        //3 循环往出款表中添加初始的出款记录
        foreach ($loan_list as $value) {
            $ret = $value->doRemit();
            if (!$ret) {
                Logger::dayLog("createremit", $value->loan_id, "无法锁定, 可能已被其它进程处理");
                continue;
            }

            $result = $this->saveOrder($value);
            if (!$result) {
                Logger::dayLog("createremit", $value->loan_id, "保存出款记录失败");
                continue;
            }

            $sucess++;
        }
        Logger::dayLog('createremit', 'index', $last_modify_time . ' to ' . $now_time, '4出款记录条数', $sucess);
        return true;
    }

    /**
     * 处理单条记录
     */
    private function saveOrder($value) {
        $user_remit_list = new User_remit_list();

        $count = $user_remit_list->getRemitCount($value->loan_id);
        if ($count > 0) {
            Logger::dayLog("createremit", $value->loan_id, "存在重复的出款记录");
            return false;
        }

        $user_loan = new User_loan();
        $settle_amount = $user_loan->getActualAmount($value->loan->is_calculation, $value->loan->amount, $value->loan->withdraw_fee);
        $postData = [
            'loan_id' => $value->loan_id,
            'admin_id' => '-1',
            'settle_request_id' => '',
            'settle_amount' => $settle_amount,//实际出款金额
            'real_amount' => $value->loan->amount,//借款金额
            'bank_id' => $value->loan->bank_id,
            'user_id' => $value->loan->user_id,
            'type' => 1,
            'fund' => $value->fund,
            'payment_channel' => $value->payment_channel,
        ];

        $result = $user_remit_list->saveRemit($postData);

        return $result;
    }

}
