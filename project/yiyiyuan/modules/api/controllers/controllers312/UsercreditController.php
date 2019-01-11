<?php
namespace app\modules\api\controllers\controllers312;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class UsercreditController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $userId = Yii::$app->request->post('user_id');
        $amount = Yii::$app->request->post('amount');
        if (empty($userId) || empty($amount)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $userObj = User::findOne($userId);
        if (empty($userObj)) {
            $array = $this->returnBack('10001');
            echo $array;
            exit;
        }
        $result = (new Apihttp())->getUserCredit(['mobile' => $userObj->mobile]);
        //1:未测评;2已测评不可借;3:评测中;4:已测评未购买;5:已测评已购买;6:已过期;7：存在未支付的白条
        if (empty($result['rsp_code']) || $result['rsp_code'] !== '0000' || empty($result['user_credit_status'])) {
            Logger::dayLog('app/usercredit', '获取用户信用失败', $userId, $result);
            $array = $this->returnBack('10119');
            echo $array;
            exit;
        }
        //评测中、已测评未购买
        if (in_array($result['user_credit_status'], [3, 4])) {
            $array = $this->returnBack('10116');
            echo $array;
            exit;
        }
        //存在未支付的白条
        if ($result['user_credit_status'] == 7) {
            exit($this->returnBack('10212'));
        }
        //评测驳回
        if($result['user_credit_status'] == 2 && !empty($result['credit_invalid_time'])){
            $borrowing = (new User_loan())->getBorrowingByTime($userId,$result['credit_invalid_time']);
            if(!$borrowing){
                exit($this->returnBack('10121'));
            }
        }
        //退卡
        if ($result['user_credit_status'] == 8) {
            exit($this->returnBack('10122'));
        }
        //已测评已购买
        if ($result['user_credit_status'] == 5) {
            if ($result['order_amount'] != $amount) {
                $array = $this->returnBack('10117');
                echo $array;
                exit;
            }
        }
        $array['status'] = $result['user_credit_status'];
        $array = $this->returnBack('0000',$array);
        echo $array;
        exit;
    }
    
}
