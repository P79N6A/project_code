<?php

namespace app\modules\newdev\controllers;

use app\common\ApiClientCrypt;
use app\commonapi\ErrorCode;
use app\commonapi\Logger;
use app\models\news\OverdueLoan;
use app\models\news\User;
use app\models\news\User_loan;
use Yii;

class LoannumController extends NewdevController {

    public $enableCsrfValidation = false;

    public function behaviors() {
        return [];
    }

    public function actionIndex() {
        $openApi = new ApiClientCrypt;
        $data = $this->post('data');
        $parr = $openApi->parseReturnData($data);
        Logger::dayLog('loannum', $parr);
        $required = ['mobile'];
        $this->verify($required, $parr['res_data']);
//        $parr['res_data'] = ['mobile'=>17600264966];
        $this->getLoanNum($parr['res_data']);
    }

    private function getLoanNum($parr){
        $userModel = new User();
        $userInfo = $userModel->getUserinfoByMobile($parr['mobile']);
        if(empty($userInfo)){
            $data_msg = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => 0];
            exit(json_encode($data_msg));
        }
        $where = [
            'AND',
            [User_loan::tableName() . '.user_id' => $userInfo->user_id],
            [User_loan::tableName() . '.status' => 8],
            ['IN', User_loan::tableName() . '.business_type', [1, 4, 5, 6]],
            ['NOT IN', User_loan::tableName() . '.settle_type', [2]],
        ];

        $loanIdArr = User_loan::find()->select('loan_id')->where($where)->asArray()->all();
        if(empty($loanIdArr)){
            $data_msg = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => 0];
            exit(json_encode($data_msg));
        }
        foreach ($loanIdArr as $v){
            $loanIds[] = $v['loan_id'];
        }
        $loanNum = count($loanIds);
        $overNum = OverdueLoan::find()->where(['loan_id' => $loanIds])->count();
        $total = $loanNum - $overNum;
        $total = intval($total);
        $total = $total < 0 ? 0 : $total;
        $data_msg = ['res_code' => '0000', 'res_msg' => '成功', 'res_data' => $total];
        exit(json_encode($data_msg));
    }

    /**
     * 只判断参数是否必传
     * @param array $required
     * @param type $httpParams
     */
    public function verify($required = [], $httpParams = [])
    {
        $errorCodeModel = new ErrorCode();
        if (empty($httpParams) || !is_array($httpParams) || !is_array($required)) {
            $array = $errorCodeModel->geterrorcode('99994');
            exit(json_encode($array));
        }
        foreach ($required as $key => $val) {
            if (!isset($httpParams[$val]) || $httpParams[$val] == '' || $httpParams[$val] == NULL) {
                $msg = $val . '不能为空';
                $array = $errorCodeModel->geterrorcode('99994', $msg);
                exit(json_encode($array));
            }
        }
    }
}
