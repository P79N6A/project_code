<?php
/**
 * 获取运营商通话详单
 */
namespace app\modules\api\controllers;

use app\models\BasicOperator;
use app\models\JxlStat;
use Yii;
use app\modules\api\common\ApiController;
use app\common\Logger;
use app\common\Func;

class BasicoperatorController extends ApiController {
    private $env;
    private $operatorModel;
    private $operatorData;
    protected $server_id = 9;

    public function init() {
        parent::init();
        $this->operatorData = $this->reqData;
        $this->env = YII_ENV_DEV ? 'dev' : 'prod';
    }

    /**
     * 拉取运营商详单接口
     *
     * @return string
     */
    public function actionDetail(){
        //参数校验
        if(!isset($this->operatorData) || empty($this->operatorData)){
            return $this->resp('55001','参数不能为空');
        }
        $source_data = isset($this->operatorData['data'])?$this->operatorData['data']:'';//运营商原始数据
        $source_data = json_decode($source_data, true);
        $calls = isset($source_data['calls'])?$source_data['calls']:'';//通话记录
        $datasource = isset($source_data['datasource'])?$source_data['datasource']:'';//号码类型（移动，联通，电信）
        $mobile = isset($source_data['mobile'])?$source_data['mobile']:'';//用户手机号
        $from = isset($source_data['from'])?$source_data['from']:'';//运营商数据来源

        if((!$source_data)||(!$calls)||(!$datasource)||(!$mobile)||(!$from)){
            return $this->resp('55002','运营商原始数据有误');
        }

//        //如果最近请求次数超过5次，直接认定失败
        $this->operatorModel = new BasicOperator();

        //存到请求表中
        $operator_data['mobile'] = $mobile;
        $operator_data['from'] = $from;
        $operator_data['status_detail'] = BasicOperator::STATUS_INIT;
        $request_id = $this->operatorModel->saveRequest($operator_data);
        if(!$request_id){
            Logger::dayLog(
                'operatorDetail',
                'saveDetailrequest:入库失败',
                '提交数据', $operator_data,
                '失败原因', $this->operatorModel->errors
            );
            return $this->resp('55004','运营商详单存储失败');
        }

        //最近四个月内使用历史数据
        $jxl_info = new JxlStat();

        $history_info =$jxl_info->getHistoryNew($mobile);
        if(empty($history_info)){
            //存储运营商通话记录详单信息
            $result = $this->saveDetailInfo($calls,$mobile,$datasource,$from,$request_id);

            if(empty($result)){
                $upd_status['status_detail'] = BasicOperator::STATUS_FAIL;
                $this->operatorModel->updateDetailrequest($request_id, $upd_status);
                return $this->resp('55004','运营商详单存储失败');
            }
        }else{
            $result = [
                'requestid'=>$history_info['requestid'],
                'source'=>$history_info['source']
            ];
        }
        //更新请求表状态
        $upd_status['status_detail'] = BasicOperator::STATUS_OK;
        $this->operatorModel->updateDetailrequest($request_id, $upd_status);
        return $this->resp('0', $result);
    }

    /**
     * 拉取运营商数据
     *
     * @param $calls
     * @param $mobile
     * @param $datasource
     * @param $request_id
     * @param $from
     * @return array
     */
    private function saveDetailInfo($calls,$mobile,$datasource,$from,$request_id){
        //格式化json格式（仿照聚信立格式）
        $format_data = $this->formatJxlData($calls);
        //存储json详单
        $file_path = $this->saveJson($request_id, $from, json_encode($format_data));
        if(stristr($file_path,'_detail')){
            $file_path = str_ireplace('_detail','',$file_path);
        }
        //存到stat表中
        return $this->saveJdqStat($mobile,$datasource,$request_id,$file_path);
    }

    /**
     * 格式化json格式（仿照聚信立格式）
     *
     * @param $data
     * @return array
     */
    private function formatJxlData($data){
        //呼叫信息
        $calls = [];
        if (!empty($data)){
            foreach($data as $key=>$value){
                $calls[] = [
                    "update_time" => $value['update_time'],
                    "start_time" => $value['start_time'],               //通话时间
                    "init_type" => $value['init_type'],                 //呼叫类型
                    "use_time"=> (int)$value['use_time'],               //通话时长
                    "place" =>  $value['place'],                        //通话地点
                    "other_cell_phone" => $value['other_cell_phone'],   //对方号码
                    "cell_phone" => $value['cell_phone'],               //绑卡手机号码
                    "subtotal" => (float)$value['subtotal'],            //费用
                    "call_type" => $value['call_type']                  //通信类型
                ];
            }
        }
        $detail_data['raw_data']['members']['transactions'][0]['calls'] = $calls;
        return $detail_data;
    }

    /**
     * 存储运营商详单
     *
     * @param $id
     * @param $content
     * @return string
     */
    private function saveJson($id, $from, $content){
        $path = '/ofiles/jxl/' . date('Ym/d/') . $from . '_' .$id . '_detail.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $content);
        return $path;
    }

    /**
     * 存到stat表中
     *
     * @param $mobile
     * @param $datasource
     * @param $file_path
     * @return array
     */
    private function saveJdqStat($mobile,$datasource,$request_id,$file_path){
        $postData = [
            'aid' =>  0,
            'requestid' =>  $request_id,
            'name' =>'',
            'idcard' => '',
            'source'=>5,
            'phone' => $mobile, // 必填
            'website' => $datasource,
            'create_time' => date("Y-m-d H:i:s", time()),
            'url' => $file_path, // 必填
        ];
        $jxl_info = new JxlStat();
        //保存到stat表中
        $ret = $jxl_info->saveStat($postData);
        if(!$ret){
            Logger::dayLog(
                'operatorDetail',
                'saveStat:入库失败',
                '提交数据', $postData,
                '失败原因', $jxl_info->errors
            );
            return [];
        }
        return [
            'requestid' =>  $postData['requestid'],
            'source'    =>  $postData['source']
        ];
    }

    /**
     * 拉取运营商报告接口
     * @return array
     */
    public function actionReport(){
        //参数校验
        $source_data = isset($this->operatorData['data'])?$this->operatorData['data']:'';
        if(!$source_data){
            return $this->resp('56001','请求数据不能为空');
        }

        $operator_data = json_decode($source_data,true);
        $report_info = isset($operator_data['operatorData'])?$operator_data['operatorData']:'';
        $report_info = json_decode($report_info,true);
        $in_netdate = isset($report_info['basicInfo']['inNetDate'])?$report_info['basicInfo']['inNetDate']:'';
        $from = isset($operator_data['from'])?$operator_data['from']:'';//运营商数据来源

        if((!$operator_data)||(!$report_info)||(!$in_netdate)||(!$from)){
            return $this->resp('56002','运营商报告原始数据有误');
        }

        $requestId = isset($operator_data['requestId'])?$operator_data['requestId']:'';
        $this->operatorModel = new BasicOperator();
        $request_data = $this->operatorModel->getRequestById($requestId);
        if(!$request_data){
            return $this->resp('0','运营商数据已存储');
        }
        //存储运营商报告
        $this->saveReport($requestId, $from, $source_data);
        $upd_status['status_report'] = BasicOperator::STATUS_OK;
        $result = $this->operatorModel->updateDetailrequest($requestId, $upd_status);
        if(!$result){
            return $this->resp('56005','运营商报告存储状态更新失败');
        }
        return $this->resp('0','运营商报告存储成功');
    }

    /**
     * 存储运营商报告
     * @param $id
     * @param $content
     * @return string
     */
    private function saveReport($id, $from, $content){
        $path = '/ofiles/jxl/' . date('Ym/d/') . $from . '_' .$id . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $content);
        return $path;
    }
}