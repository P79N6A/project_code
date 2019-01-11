<?php

namespace app\models;
use Yii;

/**
 * 支付宝路由规则
 */
class AlipayRule {

    private $error_codes = [
            '0000' => '成功',
            '10001' => '黑名单',
            '10002' => '请求超频',
            '10003' => '金额超限或者次数超限',
            '10004' => '保存订单失败',
            '10005' => '支付宝账户类型参数缺失',
            '10006' => '无可用支付宝账户',
            '10007' => '单笔额度超限',
            '10008' => '超出时间段限制'
        ];
    /**
     * Undocumented function
     * 获取支付宝路由规则
     * @param [type] $postData
     * @return void
     */
    public function getAlipayRule($postData){
        // 进行防入侵验证
        $result = $this->intrusionPrevention($postData['userip'],$postData['identityid']);
        if ($result['res_code'] != '0000') {
            return $result;
        }
        //是否是逾期     
        if(!isset($postData['is_yq'])){
            $this->error('10005');
        }
        //查询支付宝账户
        $is_yq = $postData['is_yq'];
        $accountInfo = (new AlipayAccount)->getAlipayAccount($is_yq);
        if(empty($accountInfo)){
            $this->error('10006');
        }
        //单笔限额
        if($postData['amount']>$accountInfo->limit_max_amount*100){
            $this->error('10007');
        }
        //时间段限制
        $result = $this->timeVerification($accountInfo);
        if(empty($result)){
            $this->error('10008');
        }
        return $this->success($accountInfo);
    }
    
    /**
     * 防入侵
     * @return [type] [description]
     */
    private function intrusionPrevention($ip,$identityid) {
        // 如果为黑名单用户，则拒绝访问
        $ret = (new BlackIp)->isBlackIp($ip);
        if ($ret) {
            return $this->error('10001');
        }

        // 判断是否是超频请求,超频则拒绝访问
        $ret = (new Payorder)->isOften($identityid);
        if ($ret) {
            return $this->error('10002');
        }
        return $this->success('success');
    }
    /**
     * Undocumented function
     * 时间限制
     * @param [type] $accountInfo
     * @return void
     */
    private function timeVerification($accountInfo) {
        if (empty($accountInfo)) {
            return false;
        }
        switch ($accountInfo->limit_type) {
        case '0':
            return true;
            break;

        case '1':
            $time = date('Y-m-d H:i:s');
            if ($time >= $accountInfo->limit_start_time && $accountInfo <= $bank->limit_end_time) {
                return false;
            }
            return true;
            break;

        case '2':
            $time = date('H:i:s');
            if ($time >= $accountInfo->limit_start_time && $time <= $accountInfo->limit_end_time) {
                return false;
            }
            return true;
            break;

        default:
            return false;
            break;
        }
    }

    /**
     * 返回成功json
     * @param $res_data
     * @return json
     */
    private function success($res) {
        return [
            'res_code' => '0000',
            'res_data' => $res,
        ];
    }
    /**
     * 返回错误json
     * @param $res_code
     * @param $res_data
     * @return json
     */
    private function error($res_code) {
        $res_data = $this->getcode($res_code);
        return [
            'res_code' => (string) $res_code,
            'res_data' => $res_data,
        ];
    }

    /**
     * 错误码
     * @param  str $error_code 
     * @return str
     */
    private function getcode($error_code) {
        return isset($this->error_codes[$error_code]) ? $this->error_codes[$error_code] : 'UNKNOWN';
    }
}