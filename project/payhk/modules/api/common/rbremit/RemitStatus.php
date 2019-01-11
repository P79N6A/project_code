<?php

/**
 * 中信状态解析类
 * 由于中信状态有很多.出款状态和响应状态都不太一致
 * 故做一个类做映射关系
 * 输入字段为httpstatus, xml, data 即remitapi返回的结果
 * 输出字段为status, rsp_status, rsp_status_txt
 * @author lijin
 */

namespace app\modules\api\common\rbremit;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\rongbao\Remit;

class RemitStatus {

    public $remit_status;
    public $rsp_status;
    public $rsp_status_text;

    /**
     * 解析查询接口的响应结果
     *  remit_status : DOING-> FAILURE, DOING, SUCCESS(暂不考虑)
     * @return bool
     */
    public function parseQueryStatus($response) {
        //1 判断格式是否正确
        $content = ArrayHelper::getValue($response, 'content');
        $this->rsp_status = ArrayHelper::getValue($response, 'result_code', '_TIMEOUT');
        $this->rsp_status_text = ArrayHelper::getValue($response, 'result_msg', '无响应');
        
        //2 解析响应结果
        if($this->rsp_status == '_TIMEOUT'){
            $this->remit_status = Remit::STATUS_DOING;
            return true;
        }
        if ($this->rsp_status != '0001') {
            $this->remit_status = Remit::STATUS_DOING;
            //$this->remit_status = Remit::STATUS_FAILURE;
            return true;
        }

        //3 处理状态, 注意这个不一定是最终的响应状态
        $array_result = explode(',', $content);
        $this->remit_status = $array_result[count($array_result) - 2] == '成功' ? Remit::STATUS_SUCCESS : ( $array_result[count($array_result) - 2] == '失败' ? Remit::STATUS_FAILURE : Remit::STATUS_DOING);
        $this->rsp_status_text = $array_result[count($array_result) - 2].','.$array_result[count($array_result) - 1];
        return true;
    }

    /**
     * 解析异步结果
     * @param $response  $res[data]
     * @return bool
     */
    public function parseQueryNotityStatus($content) {
        if (empty($content)) {
            return false;
        }

        //3 处理状态, 注意这个不一定是最终的响应状态
        $array_result = explode(',', $content);
        $this->remit_status = $array_result[count($array_result) - 2] == '成功' ? Remit::STATUS_SUCCESS : ( $array_result[count($array_result) - 2] == '失败' ? Remit::STATUS_FAILURE : Remit::STATUS_DOING);
        $this->rsp_status_text = $array_result[count($array_result) - 2].','.$array_result[count($array_result) - 1];
        return true;
    }
}
