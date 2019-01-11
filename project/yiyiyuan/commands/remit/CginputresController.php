<?php

namespace app\commands\remit;

use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\news\Cg_remit;
use app\models\news\Cm_loans;

/**
 * 存管债权推送结果查询
 */

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class CginputresController extends BaseController {
    private $limit = 500;

    // 命令行入口文件
    public function actionIndex() {
        $start_time = date('Y-m-d H:i:00', time() - 3600 * 2);
        $end_time = date('Y-m-d H:i:00', time() - 3600);
        $where = [
            'AND',
            ['type' => 2],
            ['status' => 3],
            ['between', 'last_modify_time', $start_time, $end_time],
        ];
        $total = Cm_loans::find()->where($where)->count();
        $pages = ceil($total / $this->limit);
        $succ = 0;
        $err = 0;
        $cgModel = new Cg_remit();
        for ($i = 0; $i < $pages; $i++) {
            $data = Cm_loans::find()->where($where)->offset($i * $this->limit)->limit($this->limit)->all();
            if (empty($data)) {
                break;
            }
            foreach ($data as $item) {
                $cgRemit = $cgModel->getByLoanId($item['loan_id']);
                if(!$cgRemit || $cgRemit->remit_status != 'WAITREMIT'){
                    $err++;
                    continue;
                }
                $res = $cgRemit->claimFail('1', 'claimsendfail');
                if(!$res){
                    Logger::dayLog('inputres/cg_input_res', "loan_id：" . $item['loan_id'] . '---切换通道失败');
                    $err++;
                }else{
                    $succ++;
                }
            }
        }
        Logger::dayLog('inputres', '总数：' . $total . '；成功：' . $succ . '；失败：' . $err);
        exit('总数：' . $total . '；成功：' . $succ . '；失败：' . $err);
    }

}
