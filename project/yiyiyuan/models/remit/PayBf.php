<?php

/**
 * 融宝自动出款
 */

namespace app\models\remit;

use app\commonapi\apiInterface\Baofu;
use Yii;

class PayBf {

    /**
     * 调用接口
     * @param obj $oRemit user_remit_list对象
     * @return  ['rsp_code', rsp_msg]
     */
    public function pay($oRemit) {
        $user = $oRemit->user;
        $bank = $oRemit->bank;
        $order_id = $oRemit->order_id;
        $settle_amount = $oRemit->settle_amount;
        $remit_type = 1; //代表借款
        $channel_id = $oRemit->payment_channel;
        $params = [
            'req_id' => $order_id,
            'remit_type' => $remit_type,
            'identityid' => $user->identity,
            'user_mobile' => $user->mobile,
            'guest_account_name' => $user->realname,
            'guest_account_bank' => $bank->bank_name,
            'guest_account_province' => '北京市',
            'guest_account_city' => '北京市',
            'guest_account_bank_branch' => $bank->bank_name,
            'guest_account' => $bank->card,
            'settle_amount' => $settle_amount,
            'callbackurl' => Yii::$app->params['remit_repay'],
            'channel_id' => $channel_id,
        ];

        //@todo 用于测试
        if (SYSTEM_ENV != 'prod') {
            return ['res_code' => '0000', 'res_msg' => '成功了!'];
            return ['res_code' => '13003', 'res_msg' => '失败了!'];
            return ['res_code' => '2222', 'res_msg' => '中断了!'];
        }

        $apihttp = new Baofu();
        $res = $apihttp->outBlance($params);
        return $res;
    }

    /**
     * 明确错误码
     * @return []
     */
    public function getFails() {
        return [
            '16001',
            '16002',
            '16003',
            '16004',
            '16005',
        ];
    }

}
