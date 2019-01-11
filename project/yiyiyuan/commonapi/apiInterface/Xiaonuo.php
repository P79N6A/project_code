<?php

namespace app\commonapi\apiInterface;

use app\commonapi\Apihttp;
use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use ReflectionClass;
use Yii;

class Xiaonuo extends Apihttp {

    /**
     * @abstract 小诺出款接口
     * @param [aid,req_id]
     * @return [true,false]
     * */
    public function outXiaonuo($params) {
        $param_map = [
            'name', //借款人姓名
            'tel', //借款人手机号
            'sex', //性别 0男 1女
            'idNumber', //身份证号
            'bidNum', //进件编号
            'loanPeriod', //期数,
            'loanAmount', //借款金额,
            'loanPurpose', //借款用途（1生活消费，2教育消费，3家庭医疗，4日常消费，5其他消费，6货物采买，7店铺运营）
            'loanPurposeDesc', //借款用途描述
            'bankCard', //银行卡号
            'bankName', //开户行名称
            'bankMobile', //银行预留手机号
            'liveAddrDetail', //居住地址（现住址）
            'company', //工作单位名称
            'companyPhone', //单位电话（区号-电话号-分机号）
            'isRepeatLoan', //是否复借
            'marryType', //婚姻状况（1未婚，2已婚，3离异，4丧偶）
            'hukouAddrDetail', //户籍地址
            'emergencyContactName1', //紧急联系人1姓名
            'emergencyContactRelation1', //紧急联系人1关系
            'emergencyContactPhone1', //紧急联系人1手机号
            'gpsInfo', //gps定位
            'equipmentNum', //设备号
            'loanIp', //设备IP
            'applyTime', //申请时间
            'faceRecognition', //人脸识别比对结果
        ];
        Logger::dayLog("xiaonuo", $params);
        if (!$this->validParamMap($param_map, $params)) {
            $ret = ['res_code' => '-999', 'res_msg' => '参数不匹配'];
            return $ret;
        }
        $url = 'xnremit/receive';
        $openApi = new ApiClientCrypt;

        $res = $openApi->sent($url, $params, 2);
        $result = $openApi->parseResponse($res);
        Logger::dayLog("xiaonuo", $res);
        Logger::errorLog($params['bidNum'] . "--" . print_r($result, true), 'Xiaonuoremit');

        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

}
