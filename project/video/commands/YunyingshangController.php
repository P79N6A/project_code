<?php
namespace app\commands;
/**
 * 百融运营商获取
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/8
 * Time: 15:52
 */
use app\common\Func;
use app\common\Http;
use app\common\Logger;
use app\common\AES128;
use app\models\BrYunyingshang;
use app\models\JxlStat;
use Yii;
use yii\helpers\BaseArrayHelper;

class YunyingshangController extends BaseController
{
    // private $yiyiyuan_url = 'http://yyytest.xianhuahua.com/guide/juxinlitice';//回调通知接口
    private $yiyiyuan_url = 'http://weixin.xianhuahua.com/guide/juxinlitice';//回调通知接口
    public function actionIndex()
    {
        $yunyingshang_info = $this->getGrabData();
        if (!empty($yunyingshang_info)){
            foreach($yunyingshang_info as $key=>$value){
                //判断是否为四个月内修改过
                $jxl_info = new JxlStat();
                $history_info =$jxl_info->getHistoryNew($value['mobile']);
                if ($history_info){
                    continue;
                }
                $ret = $this->jxlInfo($value);
                Logger::dayLog('br/notify', 'MOBILE:'.$value['mobile'].'  RESULT:'.$ret);
                if($ret == 'success'){
                    $value->status = "DOING";
                }else if ($ret == 'url_error'){
                    $value->status = "FAIL";
                }elseif($ret == 'data_null'){
                    $value->status = "LOCK";
                }else{
                    $value->status = "LOCK";
                }
                $value->last_modify_time = date("Y-m-d H:i:s", time());
                $value->save();
            }
            BrYunyingshang::updateAll(['status' => 'SUCCESS'], ['status' => 'DOING']);
        }
        BrYunyingshang::updateAll(['status' => 'INIT'], ['status' => 'FAIL']);
    }

    private function getGrabData(){
        $limit = 50;
        $where_config = [
            'status'=>['INIT','LOCK'],
        ];
    
        $queryTime = date('Y-m-d H:i:s', time() - 7200);
        $yunyingshang_info = BrYunyingshang::find()->where($where_config)
                            ->andWhere(['>=', 'create_time', $queryTime])->orderBy('id DESC')->limit($limit)->all();
        $mobile_ids = BaseArrayHelper::getColumn($yunyingshang_info, 'mobile');
        BrYunyingshang::updateAll(['status' => 'LOCK'], ['mobile' =>$mobile_ids]);
        return $yunyingshang_info;
    }
    //拉取百荣json数据
    private function jxlInfo($data){
        $operatorData = $this->operatorData($data->resourceUrl);
        $status = isset($operatorData['status'])?$operatorData['status']:0;
        if($status == 2){//拉取中
             return 'url_error';
        }
        if(!isset($operatorData['operatorData']) || empty($operatorData['operatorData'])){
            Logger::dayLog('br/notify', 'jxlInfo:  operatorData为空');
            return 'data_null';
        }
        $operator_data = json_decode($operatorData['operatorData'],true);
        if (!isset($operator_data['callDetailList']) || empty($operator_data['callDetailList']) || !isset($operator_data['basicInfo']['phoneNumber'])){
            Logger::dayLog('br/notify', 'jxlInfo:  callDetailList为空');
            return 'data_null';
        }
        $operatorDetail = $operator_data['callDetailList'];
        $phone = $operator_data['basicInfo']['phoneNumber'];
        $format_data = $this->formatJxlData($operatorDetail,$phone);
        $this->saveJson($data->id, json_encode($format_data));//存储改造后json详单
        $file_path = $this->saveSourceJson($data->id, json_encode($operatorData));//存储原json数据
        return $this->formatShangData($data, $file_path, $format_data);
    }
    private function operatorData($resourceUrl)
    {
        if (empty($resourceUrl)) return [];
        $data = Http::getCurl($resourceUrl);//通过自定义函数getCurl得到https的内容
        $resultArr = json_decode($data, true);//转为数组
        return $resultArr;
    }

    private  function mobileHome($mobile) {
        $url = "http://tcc.taobao.com/cc/json/mobile_tel_segment.htm";
        $curlPost = 'tel=' . $mobile;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = iconv('GB2312', "UTF-8", $data);
        preg_match_all("/(\w+):'([^']+)/", $data, $m);
        $arr = array_combine($m[1], $m[2]);
        return $arr;
    }

    /**
     * 按月分组
     * @param $id
     * @param $content
     * @return string
     */
    public function saveJson($id, $content) {
        $path = '/ofiles/jxl/' . date('Ym/d/') . 'br_'.$id . '_detail.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $content);
        return $path;
    }
    public function saveSourceJson($id, $content) {
        $path = '/ofiles/jxl/' . date('Ym/d/') . 'br_'.$id . '.json';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        file_put_contents($filePath, $content);
        return $path;
    }

    private function formatShangData($shang_data, $file_path)
    {
        $postData = [
            'aid' =>  0,
            'requestid' =>  0,
            'name' =>'',
            'idcard' => '',
            'source'=>5,
            'phone' => $shang_data->mobile, // 必填
            'website' => '',
            'create_time' => date("Y-m-d H:i:s", time()),
            'url' => $file_path, // 必填
        ];
        $jxl_info = new JxlStat();
        $ret = $jxl_info->saveStat($postData);
        //用于更新一亿元聚信力(yi_juxinli)
        if ($ret){
            $config_data = $this->configData("rongshut");
            if (!empty($config_data)) {
                $data_aes = AES128::encode(json_encode(['mobile' => $shang_data->mobile]), $config_data['key']);
                $juxinli_data = [
                    'code' => $config_data['code'],
                    'data' => $data_aes,
                ];
                Logger::errorLog(print_r($juxinli_data, true), 'juxinliticedata');
                $ret = Http::interface_post($this->yiyiyuan_url, $juxinli_data);
                Logger::errorLog(print_r($ret, true), 'juxinlitice');
            }
        }
        return 'success';
    }
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

    private function formatJxlData($data,$phone)
    {
        $update_time = date("Y-m-d H:i:s");
        //呼叫信息
        $calls = [];
        if (!empty($data)){
            foreach($data as $key=>$value){
                $calls[] = [
                    "update_time" => $update_time,
                    "start_time" => date('Y-m-d H:i:s',strtotime($value['time'])),
                    "init_type" => $value['dialType'],
                    "use_time"=> (int)$value['durationSec'],
                    "place" =>  $value['location'],
                    "other_cell_phone" => $value['peerNumber'],
                    "cell_phone" => $phone,
                    "subtotal" => (float)$value['fee'],
                    "call_type" => $value['commType']
                ];
            }
        }
        $detail_data['raw_data']['members']['transactions'][0]['calls'] = $calls;
        return $detail_data;
    }


    private function updateDataYear($data) {
        if(!isset($data)){
            return false;
        }
        $year = date("Y");
        foreach($data as $key=>$value){
            $startTime = substr($value['startTime'],0,2);
            if($startTime != '20'){
                $startTime = $year."-".$value['startTime'];
                $data[$key]['startTime'] = $startTime;
            }

        }
        return $data;
    }



}