<?php
/**
 * 融360运营商获取
 */
namespace app\modules\api\controllers;

use app\models\RongOperatorModel;
use app\models\JxlStat;
use Yii;
use app\modules\api\common\ApiController;
use app\common\Logger;
use app\common\AES128;
use app\common\Func;
use app\common\Http;
use yii\helpers\ArrayHelper;

class RongoperatorController extends ApiController {
    private $env;
    private $rongModel;
    private $operator_data;
    protected $server_id = 9;

    private $yiyiyuan_url = 'http://weixin.xianhuahua.com/guide/juxinlitice';//回调通知接口

    public function init() {
        parent::init();
        $this->operator_data = $this->reqData;
        $this->env = YII_ENV_DEV ? 'dev' : 'prod';
    }

    //拉取融360运营商详单接口
    public function actionRongdetail(){
        //参数记录日志
//        Logger::dayLog('rong360','融360运营商详单请求参数：'.json_encode($this->operator_data));
        //参数校验
        if(!isset($this->operator_data) || empty($this->operator_data)){
            return $this->resp('65001','参数错误');
        }
        $biz_data = isset($this->operator_data['data'])?$this->operator_data['data']:'';

        if(!$biz_data){
            return $this->resp('65002','请求数据不能为空');
        }
        $data_array = json_decode($biz_data,true);
        $orderNo = isset($data_array['data']['orderInfo']['order_no'])?$data_array['data']['orderInfo']['order_no']:'';
        if(!$orderNo){
            return $this->resp('65008','订单号不能为空');
        }

        $addInfo_mobile = isset($data_array['data']['addInfo']['mobile'])?$data_array['data']['addInfo']['mobile']:''; //运营商信息
        $teldata = isset($addInfo_mobile['tel'][0]['teldata'])?($addInfo_mobile['tel'][0]['teldata']):'';//通过记录
        $user_info = isset($addInfo_mobile['user'])?$addInfo_mobile['user']:'';//用户基本信息
        $mobile = isset($user_info['phone'])?$user_info['phone']:''; //手机号
        $mobile_type = isset($user_info['user_source'])?strtolower($user_info['user_source']):''; //号码类型

        //存到请求表中
        $this->rongModel = new RongOperatorModel();
        $request_data = $this->rongModel->getRongrequestByNo($orderNo);
        if($request_data){
            $request_id = $request_data['id'];
            //更新最近请求时间
            $this->rongModel->updateRongrequest($request_id);
        }else{
            $data['mobile'] = $mobile;
            $data['order_no'] = $orderNo;
            $data['status'] = 'INIT'; //运营商报告的拉取状态 INIT:未拉取 SUCCESS:拉取成功
            $request_id = $this->rongModel->saveRongrequest($data);
            if(!$request_id){
                Logger::dayLog('rong360','saveRongrequest:入库失败');
                return $this->resp('650016','拉取运营商请求入库失败');
            }
        }
        if((!$addInfo_mobile)||(!$teldata)||(!$mobile)||(!$user_info)||(!$mobile_type)){
            return $this->resp('65005','运营商信息不能为空');
        }
        //最近四个月内使用历史数据
        $jxl_info = new JxlStat();

        $history_info =$jxl_info->getHistoryNew($mobile);
        if(empty($history_info)){
            //存储运营商通话记录详单信息
            $result = $this->saveRongInfo($teldata,$mobile,$mobile_type,$request_id);

            if($result != 'success'){
                return $this->resp('65003','存储运营商详单失败');
            }
        }
        return $this->resp('65004','获取运营商数据成功');
    }

    //拉取融360运营商报告接口
    public function actionRongreport(){
        //参数记录日志
//        Logger::dayLog('rong360','融360运营商报告请求参数：'.json_encode($this->operator_data));
//        参数校验
        $biz_data = isset($this->operator_data['data'])?$this->operator_data['data']:'';
        if(!$biz_data){
            return $this->resp('65002','请求数据不能为空');
        }
        $data_array = json_decode($biz_data,true);

        $report_info = isset($data_array['biz_data'])?$data_array['biz_data']:'';
        if(!$report_info){
            return $this->resp('65002','请求数据不能为空');
        }
        $order_no = isset($report_info['order_no'])?$report_info['order_no']:'';
        if(!$order_no){
            return $this->resp('65008','订单号不能为空');
        }
        //
        $in_netdate = isset($report_info['basic_info']['reg_time'])?$report_info['basic_info']['reg_time']:'';
        if(!$in_netdate){
            return $this->resp('65016','入网时间不能为空');
        }
        $basicInfo = ['basicInfo'=>['inNetDate'=>date('Y年m月d日',strtotime($in_netdate))]];
        $operatorData = ['operatorData'=>json_encode($basicInfo), 'from'=>'rong360'];
        $biz_data = ArrayHelper::merge($data_array, $operatorData);
        $biz_data = json_encode($biz_data);
        //存储融360运营商报告
        $this->rongModel = new RongOperatorModel();
        $request_data = $this->rongModel->getRongrequestByNo($order_no);
        $request_id = isset($request_data['id'])?$request_data['id']:'';
        if(!$request_id){
            return $this->resp('65014','订单号有误');
        }
        $this->saveJsonSource($request_id,$biz_data);
        $upd_status['status'] = 'SUCCESS';
        $result_report = $this->rongModel->updateRongrequest($request_id,$upd_status);
        if(!$result_report){
            Logger::dayLog('rong360','ReportStatus:更新状态失败');
            return $this->resp('65015','运营商报告存储失败');
        }
        return $this->resp('65013','运营商报告存储成功');
    }

    //拉取融360运营商数据
    private function saveRongInfo($teldata,$mobile,$mobile_type,$id){
        if(!$teldata){
            return $this->resp('65006','通话记录不能为空');
        }
        if(!$mobile){
            return $this->resp('65007','电话号不能为空');
        }
        //格式化json格式（仿照聚信立格式）
        $format_data = $this->formatJxlData($teldata,$mobile);
        //存储json详单
        $file_path = $this->saveJson($id, json_encode($format_data));
        if(stristr($file_path,'_detail')){
            $file_path = str_ireplace('_detail','',$file_path);
        }
        //存到stat表中
        return $this->saveRongStat($mobile,$mobile_type,$file_path);
    }

    //格式化json格式（仿照聚信立格式）
    private function formatJxlData($data,$mobile){
        //呼叫信息
        $calls = [];
        if (!empty($data)){
            foreach($data as $key=>$value){
                switch ($value['trade_type']){
                    case '1':
                        $trade_type = '本地';
                        break;
                    case '2':
                        $trade_type = '漫游国内';
                        break;
                    default:
                        $trade_type = '其他';
                }
                switch ($value['call_type']){
                    case '1':
                        $call_type = '主叫';
                        break;
                    case '2':
                        $call_type = '被叫';
                        break;
                    default:
                        $call_type = '未识别状态';
                }
                $calls[] = [
                    "update_time" => date("Y-m-d H:i:s", time()),
                    "start_time" => $value['call_time'],
                    "init_type" => $call_type,
                    "use_time"=> (int)$value['trade_time'],
                    "place" =>  $value['trade_addr'],
                    "other_cell_phone" => $value['receive_phone'],
                    "cell_phone" => $mobile,
                    "subtotal" => (float)$value['fee'],
                    "call_type" => $trade_type
                ];
            }
        }
        $detail_data['raw_data']['members']['transactions'][0]['calls'] = $calls;
        return $detail_data;
    }

    //存储运营商详单
    private function saveJson($id, $content){
        $path = '/ofiles/jxl/' . date('Ym/d/') . 'r_'.$id . '_detail.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $content);
        return $path;
    }
    //存储运营商报告
    private function saveJsonSource($id, $content){
        $path = '/ofiles/jxl/' . date('Ym/d/') . 'r_'.$id . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $content);
        return $path;
    }

    //存到stat表中
    private function saveRongStat($mobile,$mobile_type,$file_path){
        if(!$mobile){
            return $this->resp('65007','电话号不能为空');
        }
        $postData = [
            'aid' =>  0,
            'requestid' =>  0,
            'name' =>'',
            'idcard' => '',
            'source'=>5,
            'phone' => $mobile, // 必填
            'website' => $mobile_type,
            'create_time' => date("Y-m-d H:i:s", time()),
            'url' => $file_path, // 必填
        ];
        $jxl_info = new JxlStat();

        //保存到stat表中
        $ret = $jxl_info->saveStat($postData);
        if(!$ret){
            Logger::dayLog('rong360','saveStat:入库失败');
            return 'fail';
        }
        $this->callbackJxl($mobile);
        return 'success';
    }

    /**
     * @param $jxl_string
     * @return array|mixed
     */
    private function configData($jxl_string)
    {
        $data =  [
            'rongshut' => [
                'code' => '1419',
                'key' => '45d33781abc4aa0308b3bb55d56e2372',
            ],
        ];
        return empty($data[$jxl_string]) ? [] : $data[$jxl_string];
    }

    /**
     * 回调
     */
    private function callbackJxl($mobile){
        $config_data = $this->configData("rongshut");
        if (!empty($config_data)) {
            $data_aes = AES128::encode(json_encode(['mobile' => $mobile]), $config_data['key']);
            $juxinli_data = [
                'code' => $config_data['code'],
                'data' => $data_aes,
            ];
            Logger::errorLog(print_r($juxinli_data, true), 'juxinliticedata');
            $ret = Http::interface_post($this->yiyiyuan_url, $juxinli_data);
            Logger::errorLog(print_r($ret, true), 'juxinlitice');
        }
    }
}