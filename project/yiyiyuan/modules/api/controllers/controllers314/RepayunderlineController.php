<?php

namespace app\modules\api\controllers\controllers314;

use app\commonapi\Common;
use app\models\news\Coupon_list;
use app\models\news\Loan_repay;
use app\models\news\RepayCouponUse;
use app\models\news\User;
use app\models\news\User_loan;
use app\modules\api\common\ApiController;
use Yii;

class RepayunderlineController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $pic_url = Yii::$app->request->post('pic_url');
        $loan_id = Yii::$app->request->post('loan_id');
        $paybill = Yii::$app->request->post('paybill');
        $coupon_id = Yii::$app->request->post('coupon_id');
        $source = Yii::$app->request->post('source');

        if (empty($version) || empty($pic_url) || empty($loan_id) || empty($paybill)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $loaninfo = User_loan::find()->where(['loan_id' => $loan_id])->one();
        if (!empty($loaninfo)) {
            if (!in_array($loaninfo->status, [9, 10, 12, 13])) {
                $array = $this->returnBack('10023');
                echo $array;
                exit;
            }
            $user = User::find()->where(['user_id' => $loaninfo['user_id']])->one();
            $user_id = $user['user_id'];

            //优惠卷
            $coupon_val = 0;
            if (!empty($coupon_id)) {
                $coupon_result = (new Coupon_list())->chkCoupon($user->mobile, $coupon_id, $loan_id);
                if ($coupon_result['rsp_code'] != '0000') {
                    $array = $this->returnBack($coupon_result['rsp_code']);
                    echo $array;
                    exit;
                }
                $coupon_val = $coupon_result['data']->val;
            }

            $transaction = Yii::$app->db->beginTransaction();
            $nowtime = date('Y-m-d H:i:s');
            $loan_repay = new Loan_repay();
            $condition = [
                'repay_id' => ' ',
                'user_id' => $user_id,
                'paybill'=>$paybill,
                'loan_id' => $loan_id,
                'source' => $source,
                'money' => 0,
            ];
            $pic = explode(',', $pic_url);
            foreach ($pic as $name => $up_info) {
                if (!empty($up_info)) {
                    $name = 'pic_repay' . ($name + 1);
                    $condition[$name] = $up_info;
                }
            }
            $condition['status'] = 3;
            $loan_result = $loan_repay->save_repay($condition);
            if (!$loan_result) {
                $array = $this->returnBack('99999');
                echo $array;
                exit;
            }
            //修改借款记录的状态为11
            $status = 11;
            $ret = $loaninfo->changeStatus($status);
            if (!$ret) {
                $array = $this->returnBack('99999');
                echo $array;
                exit;
            }
            //优惠卷使用
            if (!empty($coupon_id)) {
                $coupon_result = $this->couponUse($user_id, $loan_id, $coupon_id, $loan_repay->id, 0, $coupon_val, $repay_status = -1);
                if (!$coupon_result) {
                    $transaction->rollBack();
                    $array = $this->returnBack('99999');
                    echo $array;
                    exit;
                }
            }
            $transaction->commit();
            $array = $this->returnBack('0000');
            echo $array;
            exit;
        } else {
            $array = $this->returnBack('10052');
            echo $array;
            exit;
        }
    }
    /**
     * 优惠卷使用保存
     * @param $userId
     * @param $loan_id
     * @param $couponId
     * @param $repay_id
     * @param $repay_amount
     * @param $couponVal
     * @return bool
     * @author 王新龙
     * @date 2018/7/25 9:42
     */
    private function couponUse($userId, $loan_id, $couponId, $repay_id, $repay_amount, $couponVal, $repay_status = 0) {
        if (empty($couponId) || empty($couponVal)) {
            return false;
        }
        $condition = [
            'user_id' => (int)$userId,
            'loan_id' => (int)$loan_id,
            'discount_id' => (int)$couponId,
            'repay_id' => (int)$repay_id,
            'repay_amount' => $repay_amount,
            'repay_status' => 0,
            'coupon_amount' => $couponVal,
            'repay_status' => $repay_status
        ];
        $result = (new RepayCouponUse())->addRecord($condition);
        if (empty($result)) {
            return false;
        }
        return true;
    }
}
