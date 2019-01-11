<?php

namespace app\models;
use app\common\Logger;
use Yii;

class Rule {

    private $error_codes = [
            '0000' => '成功',
            '10001' => '黑名单',
            '10002' => '请求超频',
            '10003' => '暂不支持该银行卡',
            '10004' => '金额超限或者次数超限',
            '10005' => '保存订单失败',
            '10006' => '暂无支持通道'
        ];

    /**
     * 获取支持的银行卡
     * @param  [] $postData 
     * @return  [res_code, res_data]
     */
    public function getBankRoute($postData) {
        // 进行防入侵验证
        $result = $this->intrusionPrevention($postData['userip'],$postData['identityid']);
        if ($result['res_code'] != '0000') {
            return $result;
        }

        // 选择一个银行卡
        $supportBank = $this->getSupportBank($postData);
        if (empty($supportBank)) {
            return $this->error('10004');
        }elseif (isset($supportBank['res_code'])) {
        	return $supportBank;
        }

        return $this->success($supportBank);

    }

//支付宝、微信平台路由
    public function getRoute($postData) {
        // 进行防入侵验证
        $result = $this->intrusionPrevention($postData['userip'],$postData['identityid']);
        if ($result['res_code'] != '0000') {
            return $result;
        }
        // 选择支持的通道
        Logger::dayLog("repay/wa", 'info',$postData['business_code']);
        $channels = (new ChannelBank)->getChannelInfo($postData['business_code']);
        if (empty($channels)) {
            return $this->error('10006');
        }

        $support = null;
        foreach ($channels as $key => $chan) {
            // 验证时间段是否正确
            $result = $this->timeVerification($chan);
            if (!$result) {
                continue;
            }
            // 验证单笔限额
            if ($postData['amount'] > $chan->limit_max_amount*100) {
                continue;
            }
            
            $support = $chan;
            break;
        }

        if (empty($support)) {
            return $this->error('10006');
        }

        return $this->success($support);

    }


    /**
     * 获取支持的银行卡
     * @param  str $business_code
     * @param  str $bankname
     * @return obj
     */
    private function getSupportBank($postData) {
        // 获得支持的银行卡列表
        $banks = (new ChannelBank)->getBanks($postData['business_code'], $postData['bankname'],$postData['card_type']);
        if (empty($banks)) {
            return $this->error('10003');
        }
        // 筛选符合支付条件的银行卡
        $supportBank = null;
        foreach ($banks as $key => $bank) {
            // 验证时间段是否正确
            $result = $this->timeVerification($bank);
            if (!$result) {
                continue;
            }
            // 验证单比限额或者日限额或者日限数是否超上限
            $result = $this->checkQuota($bank, $postData['cardno'], $postData['amount']);
            if (!$result) {
                continue;
            }
            $supportBank = $bank;
            break;
        }
        return $supportBank;
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

    private function timeVerification($bank) {
        if (empty($bank)) {
            return false;
        }
        switch ($bank->limit_type) {
        case '0':
            return true;
            break;

        case '1':
            $time = date('Y-m-d H:i:s');
            if ($time >= $bank->limit_start_time && $time <= $bank->limit_end_time) {
                return false;
            }
            return true;
            break;

        case '2':
            $time = date('H:i:s');
            if ($time >= $bank->limit_start_time && $time <= $bank->limit_end_time) {
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
     * 额度控制
     * @param  str $bank   
     * @param  str $cardno 
     * @param  int $amount 
     * @return bool
     */
    private function checkQuota($bank, $cardno, $amount) {
        // 单笔超限额
        if ($amount > $bank->limit_max_amount*100) {
            return false;
        }
        // 获得当前渠道充值金额及笔数
        $quota = (new Payorder)->getQuota($bank->channel_id, $cardno, $amount);
        if (isset($quota['amount']) && ($quota['amount'] + $amount) > $bank->limit_day_amount * 100) {
            return false;
        }
        if (isset($quota['count']) && $quota['count'] >= $bank->limit_day_total) {
            return false;
        }

        return true;
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