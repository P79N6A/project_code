<?php
/**
 * 易宝一键支付回调接口 内部错误码范围2800-2899
 * 易宝投资通回调接口 内部错误码范围2900-2999
 */
namespace app\modules\api\controllers;

use app\common\ApiServerCrypt;
use app\common\Http;
use app\models\App;
use app\models\JxlRequestModel;
use app\models\JxlStat;
use app\modules\api\common\ApiController;
//use app\modules\api\common\juxinli\JxlRequest;

class JuxinlibackController extends ApiController {
    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
    }

    /**
     * 回调
     */
    public function actionCallurl() {
        // 1 保存回调的数据
        $jsonString = $this->post('data');
        //$this->dayLog('juxinliback', 'original', $jsonString);
        // 2 数据校验
        if (!SYSTEM_PROD) {
            //仅用于测试
            //$jsonString = $this->testdata();// @todo
        }

        $data = json_decode($jsonString, true);
        if (!is_array($data)) {
            return json_encode(["code" => "400", "note" => "json数据解析为空"]);
        }

        //3 获取必备参数
        $requestid = isset($data['LOAN_APP_ID']) ? $data['LOAN_APP_ID'] : 0;
        //$name = isset($data['CUST_NAME']) ? $data['CUST_NAME'] : '';
        //$idcard = isset($data['APP_IDCARD_NO']) ? $data['APP_IDCARD_NO'] : '';
        $phone = isset($data['APP_PHONE_NO']) ? $data['APP_PHONE_NO'] : '';

        if (!$phone) {
            return json_encode(["code" => "400", "note" => "json数据没有手机号"]);
        }
        if (!$requestid) {
            return json_encode(["code" => "400", "note" => "LOAN_APP_ID不存在"]);
        }

        // 若结果为空那么重新获取
        if (!isset($data['JSON_INFO']) || empty($data['JSON_INFO'])) {
            $this->dayLog('juxinliback', 'report_info', 'is empty', $requestid, $jsonString);
            return json_encode(["code" => "400", "note" => "JSON_INFO为空"]);
        }

        //4 查询数据库模型
        $oModel = JxlRequestModel::findOne($requestid);
        if (!$oModel) {
            $this->dayLog('juxinliback', 'JxlRequestModel', 'is empty', $requestid, $jsonString);
            return json_encode(["code" => "400", "note" => "没有纪录"]);
        }
        //zhangfei新加同步状态码
        $oModel->process_code = '10008';
        $oModel->save();

        //5 保存到文件中
        $oJxlStat = (new JxlStat)->getByRequestid($requestid);
        if (!$oJxlStat) {
            $oJxlStat = new JxlStat;
        }

        //zhangfei新增过滤
        /*$oHistory = $oJxlStat->getHistoryNew($oModel->phone);
        if ($oHistory) {
            $this->dayLog('juxinliback', 'JxlStat', '数据已有', $requestid);
            return json_encode(["code" => "400", "note" => "JSON数据已有"]);
        }*/

        $url = $oJxlStat->saveJson($requestid, $jsonString);

        //4 组合数据
        $postData = [
            'aid' => $oModel->aid,
            'requestid' => $oModel->id,
            'name' => $oModel->name,
            'idcard' => $oModel->idcard,
            'phone' => $oModel->phone,
            'website' => $oModel->website,
            'url' => $url,
            'source' => $oModel->source,
        ];

        //5 保存到DB中
        $result = $oJxlStat->saveStat($postData);
        if (!$result) {
            $this->dayLog('juxinliback', 'saveStat', '保存失败', $postData);
            return json_encode(["code" => "400", "note" => "保存成功"]);
        }

        //6 保存详情
        $jxlRequest = new JxlRequest($oModel->source);
        $details = $jxlRequest->accessRawDataByToken($oModel->token, $oModel->website, true);

        $detailurl = str_replace(".json", "_detail.json", $url);
        $oJxlStat->saveDetail($detailurl, $details);

        // 6 返回数据
        return json_encode([
            "code" => "200",
            "note" => "接收成功",
        ]);
    }

}
