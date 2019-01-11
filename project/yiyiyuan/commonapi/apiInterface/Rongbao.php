<?php

namespace app\commonapi\apiInterface;

use app\common\Api7ClientCrypt;
use app\common\ApiClientCrypt;
use app\commonapi\Apihttp;
use app\commonapi\Logger;

class Rongbao extends Apihttp {

    /**
     * @abstract 融宝出款接口
     * @param [aid,req_id]
     * @return [true,false]
     * */
    public function outBlance($params, $loan = '') {
        $param_map = ['req_id', 'remit_type', 'identityid', 'user_mobile', 'guest_account_name', 'guest_account_bank', 'guest_account_province', 'guest_account_city', 'guest_account', 'settle_amount', 'callbackurl'];
        if (!$this->validParamMap($param_map, $params)) {
            $ret = ['res_code' => '-999', 'res_msg' => '参数不匹配'];
        }
        $url = "rbremit";
        if (!empty($loan) && $loan->business_type == 10) {
            $openApi = new Api7ClientCrypt;
        } else {
            $openApi = new ApiClientCrypt;
        }
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::dayLog('rongbao', $params, $url, $result);
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

}
