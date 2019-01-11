<?php
namespace app\modules\api\controllers\controllers310;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Insurance;
use app\models\news\Insure;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class BuyinsuranceController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $loan_id = Yii::$app->request->post('loan_id');
        $source = Yii::$app->request->post('source', 2);
        $is_chk = Yii::$app->request->post('is_chk');
        if (empty($version) || empty($loan_id) || empty($source) || empty($is_chk)) {
            exit($this->returnBack('99994'));
        }
        //判断借款是否存在
        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (empty($loaninfo)) {
            exit($this->returnBack('10052'));
        }
        //判断核保信息是否存在
        $insureInfo = (new Insurance())->getDateByLoanId($loan_id);
        if (empty($insureInfo)) {
            exit($this->returnBack('10108'));
        }
        $in_result = (new Insure())->find()->where(['loan_id' => $loan_id, 'status' => [0, -1, 1]])->one();
        if ($in_result) {
            exit($this->returnBack('10110'));
        }
        //勾选不勾选
        $up_res = $insureInfo->updateData(['is_chk' => $is_chk]);
        if (!$up_res) {
            exit($this->returnBack('99999'));
        }
        if ($is_chk == 2) {
            $array['redirect_url'] = '';
            exit($this->returnBack('0000', $array));
        }
        //添加支付记录
        $order_id = $this->addInsurance($insureInfo, $source);
        if (!$order_id) {
            exit($this->returnBack('99999'));
        }
        //投保支付
        $url = $this->policypay($insureInfo, $order_id);
        if (!$url) {
            exit($this->returnBack('10109'));
        }

        $array['redirect_url'] = $url;
        exit($this->returnBack('0000', $array));
    }

    private function policypay($insureInfo, $order_id)
    {
        $contacts = [
            'req_id' => $insureInfo->req_id,//请求序号
            'client_id' => $order_id,
            'callbackurl' => Yii::$app->params['policypay_notify_url'],//回调地址
        ];
        $api = new Apihttp();
        $result = $api->policypay($contacts);
        if ($result['res_code'] != 0) {
            Logger::dayLog('installment/policypay', '投保支付失败', 'insure ID：' . $insureInfo->id, $contacts, $result);
            return false;
        }
        if (isset($result['res_data']) && isset($result['res_data']['url'])) {
            $redirect_url = (string)$result['res_data']['url'];
            if (empty($redirect_url)) {
                return false;
            }
            return $redirect_url;
        } else {
            return false;
        }
    }

    private function addInsurance($insureInfo, $source)
    {
        $data = [
            'req_id' => $insureInfo->req_id,
            'loan_id' => $insureInfo->loan_id,
            'user_id' => $insureInfo->user_id,
            'order_id' => date('YmdHis') . $insureInfo->loan_id,
            'money' => $insureInfo->money,
            'source' => $source,
            'version' => 0,
        ];
        $res = (new Insure())->saveData($data);
        if (!$res) {
            return false;
        }
        return $data['order_id'];
    }
}
