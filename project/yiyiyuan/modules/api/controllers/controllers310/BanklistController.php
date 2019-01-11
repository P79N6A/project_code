<?php
namespace app\modules\api\controllers\controllers310;

use app\commonapi\Keywords;
use app\models\news\Loan_repay;
use app\models\news\Payaccount;
use app\models\news\User;
use app\models\news\User_bank;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class BanklistController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $type = empty(Yii::$app->request->post('type')) ? 0 : Yii::$app->request->post('type');
        $borrow_or_pay = Yii::$app->request->post('borrow_or_pay') === NULL ? 2 : Yii::$app->request->post('borrow_or_pay') == '' ? 2 : Yii::$app->request->post('borrow_or_pay');
        $type = 0;//全部强制只查储蓄卡
        if (empty($version) || empty($user_id) || !isset($type)) {
            exit($this->returnBack('99994'));
        }

        $user = new User();
        $userinfo = $user->getUserinfoByUserId($user_id);
        if (empty($userinfo)) {
            exit($this->returnBack('10001'));
        }
        $bank = new User_bank();
        if ($type == 0) {
            $order = 'default_bank desc,last_modify_time desc';
        } else {
            $order = 'last_modify_time desc';
        }
        $user_bank = $bank->getBankByUserId($user_id, $type, $order);
        $cardlimit_info = (new User_bank())->limitCardsSort($user_id, $borrow_or_pay, $type);
        $array = $this->reback($user_bank, '', $cardlimit_info, $user_id, $borrow_or_pay);
        $total_user_bank = $bank->getBankByUserId($user_id);
        $count = count($total_user_bank);
        $allow = ($count >= 10) ? 0 : 1;
        $array['allow'] = $allow;
        $array['style'] = Keywords::isOpenBank();//301,1：新流程页面；2：旧流程页面
        $array['detail_url'] = Yii::$app->request->hostInfo . "/new/bank/cgdetail?user_id=".$user_id;
        exit($this->returnBack('0000', $array));
    }

    private function reback($bank, $notice, $cardlimit_info, $user_id, $borrow_or_pay)
    {
        //判断用户卡是否有存管开户卡
        $payAccount = new Payaccount();
        $payAccountObj = $payAccount->getPaysuccessByUserId($user_id, 2, 1);

        $res = array();
        $array['notice'] = !empty($notice) ? $notice->content : '';
        $array['is_open'] = !empty($payAccountObj) ? 1 : 2;
        $array['banklist'] = array();
        if (!empty($cardlimit_info)) {
            $userLoanModel = new User_loan();
            $userLoanId = $userLoanModel->getHaveinLoan($user_id);
            $userLoanObj = $userLoanModel->getLoanById($userLoanId);
            $payCg = (new Loan_repay())->payCg($userLoanObj);
            foreach ($cardlimit_info as $key => $val) {
                $mark = 1;//默认：银行卡可用
                if ($borrow_or_pay == 1 && $payCg && isset($payAccountObj) && $payAccountObj->card != $val['id']) {
                    $mark = 0;
                } else if (!$payCg && (empty($val['bank_name']) || empty($val['bank_abbr']))) {
                    $mark = 0;
                }
                $array['banklist'][$key]['bank_id'] = $val['id'];
                $array['banklist'][$key]['type'] = empty($val['bank_name']) ? '' : $val['type'];
                $array['banklist'][$key]['bank_name'] = empty($val['bank_name']) ? '银行卡' : trim($val['bank_name'], " ");
                $array['banklist'][$key]['card'] = substr($val['card'], strlen($val['card']) - 4, 4);
                $array['banklist'][$key]['bank_abbr'] = $val['bank_abbr'];
                $array['banklist'][$key]['mark'] = $mark;//($val['sign'] == 1) ? 0 : 1;
                $array['banklist'][$key]['bank_icon_url'] = $this->getImageUrl($val['bank_abbr']);
                $array['banklist'][$key]['default_card'] = $val['default_bank'] == 0 ? 2 : $val['default_bank'];
            }
        }
        return $array;
    }

    private function getImageUrl($abbr)
    {
        $bankAbbr = [
            'ABC',
            'BCCB',
            'BCM',
            'BOC',
            'CCB',
            'CEB',
            'CIB',
            'CMB',
            'CMBC',
            'ECITIC',
            'GDB',
            'HXB',
            'ICBC',
            'PAB',
            'PSBC',
            'SPDB'
        ];
        if (!empty($abbr) && in_array($abbr, $bankAbbr)) {
            $abbr_url = $abbr;
        } else {
            $abbr_url = 'ICON';
        }
        $url = Yii::$app->params['app_url'];
        return $url . "/images/bank_logo/" . $abbr_url . ".png";
    }
}
