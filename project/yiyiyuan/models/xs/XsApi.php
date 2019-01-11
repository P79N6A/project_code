<?php

namespace app\models\xs;

use app\commonapi\Apihttp;
use yii\helpers\ArrayHelper;
use app\commonapi\Logger;
/**
 * 统一对外开放接口
 */
class XsApi {
    /**
     * 注册事件
     */
    public function runReg($post_data) {
        //1. 注册事件数据录入
        $oBasic = (new XsApiSetBasic)->setBasic('reg', $post_data);
        if (!$oBasic) {
            return $this->error("20101", "数据保存出错");
        }

        //2. 注册事件运算结果
        $oRun = new XsApiReturn;
        $res = $oRun->getReg($oBasic);
        if (!$res) {
            return $this->error("20102", "计算结果出错");
        }
        //3. 判断是否有百度信息
        if (!isset($res['bd']) || empty($res['bd'])) {
            $res['bd'] = $this->getBaiduRisk($post_data,$res);
        }
        $res = $this->decSelf($res);
        return $this->success($res);
    }
    /**
     * 借款事件
     */
    public function runLoan($post_data) {
        //1. 借款事件数据录入
        $oBasic = (new XsApiSetBasic)->setBasic('loan', $post_data);
        if (!$oBasic) {
            return $this->error("20201", "数据保存出错");
        }

        //2. 借款事件运算结果
        $oRun = new XsApiReturn;
        $res = $oRun->getLoan($oBasic);
        if (!$res) {
            return $this->error("20203", "计算结果出错");
        }
        //3. 判断是否有百度信息
        if (!isset($res['bd']) || empty($res['bd'])) {
            $res['bd'] = $this->getBaiduRisk($post_data,$res);
        }
        $res = $this->decSelf($res);
        return $this->success($res);

    }
    /**
     * 需减去自身的变量
     * @return data
     */
    private function decSelf(&$data){
        $decs = [
            //关系减自身
            'ip_devices',
            'device_ips',
            'ip_users',
            'device_users',

            // 多投减自身
            // "mph_y",
            // "mph_fm",
            // "mph_other",
            // "mph_br",
            // "mid_y",
            // "mid_fm",
            // "mid_other",
            // "mid_br ",

            // 高频借款减自身
            'loan_num_1',
            'loan_num_7',

            // 当月同一设备借款用户数限制减自身
            'device_loan_month',
        ];
        foreach ($decs as $key_dec) {
            if( isset($data[$key_dec]) ){
                $data[$key_dec] = $data[$key_dec] > 0 ? $data[$key_dec] -1 : 0;
            }
        }
        return $data;
    }
    /**
     * 目前仅限于一亿元黑名单录入
     */
    public function setBlack($phone, $idcard) {
        //1. 身份证
        $result = false;
        if ($idcard) {
            $data = [
                "idcard" => $idcard,
                "bid_y" => 1,
            ];
            $oBlackIdcard = new XsBlackIdcard;
            $result = $oBlackIdcard->setBlack($data);
            if (!$result) {
                return false;
            }
        }

        //2. 手机号录入
        if ($phone) {
            $data = [
                "phone" => $phone,
                "bph_y" => 1,
            ];
            $oBlackPhone = new XsBlackPhone;
            $result = $oBlackPhone->setBlack($data);
            if (!$result) {
                return false;
            }
        }

        return $result;
    }
    /**
     * 目前仅限于一亿元黑名单取消
     */
    public function unSetBlack($phone, $idcard) {
        //1. 身份证
        $result = false;
        if ($idcard) {
            $data = [
                "idcard" => $idcard,
                "bid_y" => 0,
            ];
            $oBlackIdcard = new XsBlackIdcard;
            $result = $oBlackIdcard->unSetBlack($data);
        }

        //2. 手机号录入
        if ($phone) {
            $data = [
                "phone" => $phone,
                "bph_y" => 0,
            ];
            $oBlackPhone = new XsBlackPhone;
            $result = $oBlackPhone->unSetBlack($data);
        }

        return true;
    }
    /**
     * 同盾数据录入
     */
    public function setFM($post_data) {
        //1. 保存同盾数据
        $model = new XsApiSetFM;
        $res = $model->setFM($post_data);
        if (!$res) {
            return $this->error("20201", "数据保存出错");
        }

        //2 参数
        $event = $model->oFM->event;
        $basic_id = $model->oFM->basic_id;
        if (!$basic_id) {
            // 仅保存成功, 但不计算规则
            return $this->success([]);
        }

        //3 计算规则
        $oRun = new XsApiReturn;
        $res = $oRun->get($basic_id);
        if (!$res) {
            return $this->error("20102", "计算结果出错");
        }
        $res = $this->decSelf($res);
        return $this->success($res);
    }
    /**
     * 黑名单导入
     * 用于一亿元, 其它黑名单和百融
     */
    public function importBlack($post_data) {
        //1. 身份证
        $oYArray = new YArray;
        $result = false;
        if ($post_data['idcard']) {
            $data = $oYArray->getByKeys($post_data, [
                'idcard',
                'bid_y',
                'bid_other',
                'bid_br',
            ], 0);

            $oBlackIdcard = new XsBlackIdcard;
            $result = $oBlackIdcard->setBlack($data);
            if (!$result) {
                return false;
            }
        }

        //2. 手机号录入
        if ($post_data['phone']) {
            $data = $oYArray->getByKeys($post_data, [
                'phone',
                'bph_y',
                'bph_other',
                'bph_br',
            ], 0);
            $oBlackPhone = new XsBlackPhone;
            $result = $oBlackPhone->setBlack($data);
            if (!$result) {
                return false;
            }
        }

        return $result;
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
    /**
     * 请求百度金融接口
     * @param  obj $aid         
     * @return []
     */
    private function getBaiduRisk($oBasic,$res)
    {
        //请求前本地记录 
        $baidurisk = new XsBaidurisk();
        $oBd = $baidurisk->saveData($oBasic,$res);
        if (!$oBd) {
            Logger::dayLog('result/getBaiduRisk', '百度请求记录失败', $oBasic,$baidurisk->errors);
        }
        //请求百度金融接口
        $params = [
            'name' => $oBasic['name'],
            'idcard' => $oBasic['idcard'],
            'phone' => $oBasic['phone'],
        ];
        $api = new Apihttp();
        $baidu_result = $api->BaiduRiskApi($params);
        //更新数据
        $res = $baidurisk->updateBdInfo($baidu_result);
        if (!$res) {
            Logger::dayLog('result/getBaiduRisk', '百度请求更新失败', $oBasic,$baidurisk->errors);
        }
        if (isset($baidu_result['retCode']) && $baidu_result['retCode'] !== 0  ) {
            Logger::dayLog('result/riskLoanValid', '请求数据异常', $params,$baidu_result);
        }
        return $baidu_result;
    }
}
