<?php
/**
 * 天行数科学信网查询
 */
namespace app\models\txsk;

use app\common\Logger;
use app\models\txsk\TxskApi;
use yii\helpers\ArrayHelper;
use yii\db\Command;

class TxskServer
{

    /**
     * 天行API
     */
    private $txskApi;

    /**
     * TxskServer constructor.
     */
    public function __construct()
    {
        $this->txskApi = new TxskApi;
    }

    /**
     * 查询学信网数据
     * @param $bindcardData
     * @return array
     */
    public function getXxwEdu($data)
    {
        //1 数据检测
        if (empty($data) || !is_array($data)) {
            return $this->error('20401','数据异常');
        }

        //2 查询本地数据
        $get_res = $this->txskApi->getTxskedu($data);
        if (!empty($get_res)) {
            return $this->success($get_res);
        }

        //3 无数据，记录请求
        $edu = new Txskedu();
        $set_res = $edu->saveData($data);

        //4 请求接口
        $edu_res = $this->txskApi->QueryApiEdu($data);
        if (!$edu_res) {
            return $this->error('20401',$this->txskApi->errinfo);
        }

        //5 更新数据
        $up_res = $edu->updateXxwInfo($edu_res);

        //6 返回结果
        if (isset($edu_res['success']) && !$edu_res['success']) {
            return $this->error('20402',$edu_res['errorDesc']);
        }
        $edu_data = $edu_res['data'];
        return $this->success($edu_data);
    }

    /**
     * 返回成功json
     * @param $res_data
     * @return json
     */
    private function success($res) {
        if (is_array($res)) {
            $res['res_code'] = '0';
        } else {
            $res = [
                'res_code' => '0',
                'res_data' => $res,
            ];
        }
        return $res;
        //return json_encode($res, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 返回错误json
     * @param $res_code
     * @param $res_data
     * @return json
     */
    private function error($rsp_code, $res_data) {
        return [
            'res_code' => (string) $rsp_code,
            'res_data' => $res_data,
        ];
    }
}
