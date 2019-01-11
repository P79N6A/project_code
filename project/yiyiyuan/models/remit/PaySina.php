<?php

/**
 * 新浪自动出款
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\models\remit;

use app\commonapi\apiInterface\Sinaremit;
use Yii;
use yii\helpers\ArrayHelper;

class PaySina {

    /**
     * 调用接口
     * @param obj $oRemit user_remit_list对象
     * @return  ['rsp_code', rsp_msg]
     */
    public function pay($oRemit) {
        $order_id = $oRemit->order_id;
        $user = $oRemit->user;
        $bank = $oRemit->bank;
        $settle_amount = $oRemit->settle_amount;

        $ip = '';
        if ($oRemit->loanExtend && $oRemit->loanExtend->userIp) {
            $ip = explode(',', $oRemit->loanExtend->userIp);
            $ip = ArrayHelper::getValue($ip, 'ip');
        }
        if (empty($ip)) {
            $ip = "120.55.108.133";
        }

        $params = [
            'req_id' => $order_id,
            'user_id' => $user->user_id,
            'cardno' => $bank->card,
            'settle_amount' => $settle_amount,
            'callbackurl' => Yii::$app->params['sina_remit'],
            'ip' => $ip,
        ];

        //@todo 用于测试
        if (SYSTEM_ENV != 'prod') {
            return ['res_code' => '0000', 'res_msg' => '成功了!'];
            return ['res_code' => '13003', 'res_msg' => '失败了!'];
            return ['res_code' => '2222', 'res_msg' => '中断了!'];
        }

        $apihttp = new Sinaremit();
        $res = $apihttp->outBlance($params);
        return $res;
    }

    /**
     * 明确错误码
     * @return []
     */
    public function getFails() {
        return [
            '150201',
            '150202',
            '150203',
            '150204',
            '150205',
            '150206',
            '150207',
            '150208',
            '150209',
            '150210',
        ];
    }

}
