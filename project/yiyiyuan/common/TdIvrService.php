<?php

namespace app\common;

use Yii;
use app\common\Curl;

class TdIvrService {

    //发送数据到同盾
    public function pull_data($data) {
        if (!$data) {
            return false;
        }
        $td_param = \Yii::$app->params['td_param'];
        $url      = 'https://api.tongdun.cn/collection/entry/v1';
        $curl     = new Curl();
        $data     = http_build_query($data);
        $response = $curl->post($url, $data, $td_param);
        return $response;
    }

    //获取对账表单信息
    public function getBill($data) {
        if (!$data) {
            return false;
        }
        $td_param  = \Yii::$app->params['td_param'];
        $url       = 'https://api.tongdun.cn/collection/bill/v1';
        $curl      = new Curl();
        $data      = http_build_query($data);
        $billParam = array_slice($td_param, 0, 2);
        $response  = $curl->post($url, $data, $billParam);
        return $response;
    }

    //获取录音文件信息
    public function getVoicefiles($data) {
        //$data = ['date' => '2017-09-09'];  默认形式
        if (!$data) {
            return false;
        }
        $td_param   = \Yii::$app->params['td_param'];
        $Param      = array_slice($td_param, 0, 2);
        $voiceParam = array_merge($Param, $data);
        $url        = 'https://api.tongdun.cn/collection/voicefiles/v1';
        $curl       = new Curl();
        $response   = $curl->get($url, $voiceParam);
        return $response;
    }

    //获取单笔记录信息
    public function getreport($data) {
        //$data = ['collection_id' => 'ssssssss']; 默认形式
        if (!$data) {
            return false;
        }
        $td_param    = \Yii::$app->params['td_param'];
        $Param       = array_slice($td_param, 0, 2);
        $reportParam = array_merge($Param, $data);
        $url         = 'https://api.tongdun.cn/collection/report/v1';
        $curl        = new Curl();
        $response    = $curl->get($url, $reportParam);
        return $response;
    }

    public function getDailyReport($data) {
        //$data = ['date' => '2017-09-09'];  默认形式
        if (!$data) {
            return false;
        }
        $td_param   = \Yii::$app->params['td_param'];
        $Param      = array_slice($td_param, 0, 2);
        $dailyParam = array_merge($Param, $data);
        $url        = 'https://api.tongdun.cn/collection/daily.report.search/v1';
        $curl       = new Curl();
        $response   = $curl->get($url, $dailyParam);
        return $response;
    }

    public static function switch_status($status_code) {
        //获取同盾呼叫状态对应
        $status_arr = self::getStatusArr();
        return isset($status_arr[$status_code])?$status_arr[$status_code]:'无对应状态信息';
    }
    //同盾呼叫状态对应
    public static function getStatusArr(){
        return $status_arr = [
            'CALLFAILED'       => '系统原因,拨号失败',
            'DIALFAILED	'      => '拨号失败',
            'DISABLERETURN'    => '用户暂无还款能力',
            'HUNGUP'           => '用户中途挂断电话',
            'INVALIDNUM'       => '无效号码，拨号失败',
            'LINEBUSY'         => '线路忙，拨号失败',
            'MSGSENDFAILURE'   => '短信发送失败',
            'MSGSENDSUCCESS'   => '短信发送成功',
            'NOANSWER'         => '无应答',
            'NOTTARGET'        => '不是用户本人',
            'PARAM_ERROR'      => '参数异常',
            'PROMISERETURN0'   => '逾期客户承诺当天还款',
            'PROMISERETURN1'   => '逾期客户承诺第二天还款',
            'PROMISERETURN2'   => '逾期客户表明协商还款',
            'RETURNED'         => '逾期客户表明已还款',
            'SUCCESS'          => '呼叫成功',
            'SYSTEM_ERROR'     => 'IVR系统异常',
            'TIMEOUT'          => '对方无输入，系统超时挂断',
            'POWEROFF'         => '关机',
            'CLOSEDOWN'        => '停机',
            'OUTOFREACH'       => '不在服务区',
            'CALLCONNECTFAILD' => '拨号线路原因，未接通',
            'REFUSE'           => '拒接',
            'EXPIREDNUM'       => '号码过期'
        ];
    }

}
