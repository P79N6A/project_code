<?php
/**
 * 数立银行流水逻辑处理程序
 * @author 孙瑞
 */
namespace app\modules\api\common\slbank;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\slbank\SlbankRequest;
use app\modules\api\common\slbank\SlbankApi;
use app\modules\api\common\slbank\SlbankNotify;

class SlbankService{

    private $oSlbankRequest;
    private $oSlbankApi;
    private $oSlbankNotify;

    public function __construct(){
        $this->oSlbankRequest = new SlbankRequest();
        $this->oSlbankApi = new SlbankApi();
        $this->oSlbankNotify = new SlbankNotify();
    }

    // 获取需要采集的数据
    public  function runAll() {
        $dataList = $this->oSlbankRequest->getRequestList();
        return $this->runCollection($dataList);
    }

    // 执行采集操作
    private function runCollection($dataList) {
        if(!$dataList){
            Logger::dayLog('slbank/Collect', 'logging 未获取到需要采集的数据列表');
            return 0;
        }
        // 锁定状态为抓取中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->oSlbankRequest->lockStatus($ids);
        if (!$ups) {
            Logger::dayLog('slbank/Collect', 'error 数据加锁失败');
            return 0;
        }
        // 循环执行抓取任务
        $num = 0;
        foreach($dataList as $value){
            // 执行单条拉取任务
            $turnoverData = $this->oSlbankApi->saveBankTurnover($value->attributes);
            // 重试状态
            if($turnoverData === false){
                continue;
            }
            // 成功状态
            if($turnoverData == SlbankRequest::STATUS_SUCCESS){
                $num++;
            }
            // 执行推送
            Logger::dayLog('slbank/Collect', 'logging 开始对id为'.$value->id.'的请求进行通知操作');
            (new SlbankNotify())->runOne($value->id);
        }
        logger::dayLog('slbank/Collect','collectDone 数据采集成功条数:'.$num.' 成功数据ID为:'.json_encode($ids));
        return $num;
    }

    /**
     * 查询此用户是否成功获取过流水信息
     * @param1 array postData 请求数据
     * @param2 int number 有效天数  默认为90天
     * @return int -1为错误请求 -2为正在获取 -3为已获取成功 >0为添加成功
     */
    public function saveRequestInfo($postData,$number = 90){
        if(!$postData){
            return -1;
        }
        $data = $this->oSlbankRequest->find()->where(['user_id'=>$postData['user_id']])->orderBy('create_time desc')->one();
        // 不存在数据时添加新数据
        if(!$data){
            if($insertId = $this->oSlbankRequest->insertRequestInfo($postData)){
                return $insertId;
            }
            return -1;
        }
        Logger::dayLog('slbank/Request','index/logging 记录查询数据结果:'.json_encode($data->attributes));
        // 判断数据请求生命周期进行返回
        $request_status = ArrayHelper::getValue($data,'request_status');
        if($this->oSlbankRequest->isDoingRequest($request_status)){
            return -2;
        }
        if($this->oSlbankRequest->isFailRequest($request_status)){
            if($insertId = $this->oSlbankRequest->insertRequestInfo($postData)){
                return $insertId;
            }
            return -1;
        }
        // 判断已获得的数据有效期是否超过规定时间
        $nowTime = time();
        $modify_time = strtotime(ArrayHelper::getValue($data,'modify_time'));
        if(($modify_time+(86400*$number))<$nowTime){
            if($insertId = $this->oSlbankRequest->insertRequestInfo($postData)){
                return $insertId;
            }
            return -1;
        }
        return -3;
    }
}
?>