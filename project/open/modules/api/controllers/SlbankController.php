<?php
/**
 * 数立银行流水数据接口
 * @author 孙瑞
 * @actionIndex 数据请求入库及H5页面链接生成
 * @actionSavebizno 获取授权后的平台流水号
 *
 */
namespace app\modules\api\controllers;

use YII;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\modules\api\common\ApiController;
use app\modules\api\common\slbank\SlbankApi;
use app\modules\api\common\slbank\SlbankService;
use app\models\slbank\SlbankRequest;

header("Content-type: text/html; charset=utf-8");
set_time_limit(0);

class SlbankController extends ApiController{

    protected $server_id = 105;
    private $errorCode=array(
        // 错误码说明
        105101 => '请求参数数据不全',
        105102 => '请求数据保存失败',
        105103 => '该用户数据正在获取中',
        105104 => '该用户数据已获取未失效,请勿重新获取',
        105105 => '授权H5页面Url生成失败',
        105106 => '授权H5页面Url生成成功',
        105110 => '请求过于频繁,请稍后再试',

        105107 => '获取数据对象失败',
        105108 => '授权数据保存失败',
        105109 => '授权数据保存成功',
    );

    public function init() {
        parent::init();
    }

    /**
     * 数据请求入库及H5页面链接生成
     * @params
     * user_id          用户id
     * show_url         回显地址
     * callback_url     回调地址
     * @return
     * request_id       请求id
     * jump_url         H5跳转地址
     */
    public function actionIndex(){
        // 获取请求业务数据
        $reqData = $this->reqData;
        Logger::dayLog('slbank/Request','index/logging 记录请求数据'.json_encode($reqData));
        $userId = ArrayHelper::getValue($reqData,'user_id');
        $postData=array(
            'user_id' => $userId,
            'show_url' => ArrayHelper::getValue($reqData,'show_url'),
            'callback_url' => ArrayHelper::getValue($reqData,'callback_url'),
            'aid' => $this->appData['id'],
        );
        // 校验请求参数是否完整
        array_walk($postData, function ($item, $key) use($postData){
            if(!!empty($item)){
                return $this->resp(105101, array('reason'=>$this->errorCode[105101]));
            }
        });

        $oSlbankRequest = new SlbankRequest;
        // 判断请求是否过于频繁
        $requestFrequency = $oSlbankRequest->isOverMaxRequest($userId);
        if(!$requestFrequency){
            return $this->resp(105110, array('reason'=>$this->errorCode[105110]));
        }
        // 保存请求数据 $result 为-1时是数据添加失败 为-2时是正在获取 为-3时是已获取成功
        $oSlbankService = new SlbankService();
        $result = $oSlbankService->saveRequestInfo($postData);
        if($result<0){
            $msgCode = abs($result)+105101;
            return $this->resp($msgCode, array('reason'=>$this->errorCode[$msgCode]));
        }
        // 生成H5跳转地址
        $oSlbankApi = new SlbankApi();
        $jump_info = $oSlbankApi->getH5Url($postData['show_url'],$result);
        if(!$jump_info){
            return $this->resp(105105, array('reason'=>$this->errorCode[105105]));
        }
        Logger::dayLog('slbank/Request','index/success '.$this->errorCode[105106].' url:'. json_encode($jump_info));
        return $this->resp(0, $jump_info);
    }

    /**
     * 获取授权后的平台流水号
     * @params
     * request_id       请求id
     * org_biz_no       商户生成的流水号
     * biz_no           授权后平台流水号
     * @return 成功/失败
     */
    public function actionSavebizno(){
        // 获取请求业务数据
        $reqData = $this->reqData;
        Logger::dayLog('slbank/Request','savebizno/logging 记录请求数据'.json_encode($reqData));
        $postData=array(
            'request_id' => ArrayHelper::getValue($reqData,'request_id'),
            'org_biz_no' => ArrayHelper::getValue($reqData,'org_biz_no'),
            'biz_no' => ArrayHelper::getValue($reqData,'biz_no'),
        );
        // 校验请求参数是否完整
        array_walk($postData, function ($item, $key) use($postData){
            if(!!empty($item)){
                return $this->resp(105101, array('reason'=>$this->errorCode[105101]));
            }
        });
        // 保存请求数据
        $oSlbankRequest = new SlbankRequest();
        $requestObj = $oSlbankRequest->getOne(ArrayHelper::getValue($postData,'request_id'));
        if(!$requestObj){
            return $this->resp(105107, array('reason'=>$this->errorCode[105107]));
        }
        $result = $oSlbankRequest->saveBizNo($postData,$requestObj);
        if(!$result){
            return $this->resp(105108, array('reason'=>$this->errorCode[105108]));
        }
        return $this->resp(0, array('reason'=>$this->errorCode[105109]));
    }
}
?>