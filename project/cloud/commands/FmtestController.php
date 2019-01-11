<?php
/**
 *  定时同步jxl_stat报告信息到mycat里
 */
namespace app\commands;

use Yii;
use yii\web\Controller;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\xs\XsFraudmetrix;
use app\common\Fmdown;


/**
 * 同步报告手机标签数据
 * 本地测试：D:\phpstudy\php55\php.exe  D:\phpstudy\WWW\cloud_ssdb\yii fmtest runFm
 * /usr/local/bin/php /data/wwwroot/test/cloud/yii  fmtest runFm
 */
class FmtestController extends BaseController
{   
    private static $db_open;
    private static $allRun = 2000;
    private static $worker = 1;
    private static $step = 10;
    private $file_path;

    public function init()
    {
        $this->file_path = Yii::$app->basePath . '/commands/rundata/';
    }

    /**
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    private function readCsv($key)
    {
        $path_arr = ['1'=>'aaa.csv','2'=>'b.csv','3'=> 'c.csv','4'=>'d.csv'];
        $path = $path_arr[$key];
        $file_path = $this->file_path.$path;
        $file = fopen($file_path,'r');
        $n = 0;
        $value = [];
        while ($data = fgetcsv($file)) { //每次读取CSV里面的一行内容
            if ($n === 0) {
                $key = $data;
            } else {
                $arr = [
                    $key['0'] => $data['0'],
                    $key['1'] => $data['1'],
                    $key['2'] => $data['2'],
                    $key['3'] => $data['3'],
                    // $key['2'] => str_replace('/', '-', $data['2']),
                ];
                $value[] = $arr;
            }
            $n++;
        }
        $count = count($value);
        return $value;
    }

    public function runFm($key = '1')  
    {   
        $baseList = $this->readCsv($key);
        if (empty($baseList)) {
            Logger::dayLog('fmtest/runTag', 'nothing to deal with');
            die('nothing to deal with');
        }
        $path = $this->file_path.'fm_multi'.$key.'.csv';
        if (file_exists($path)) {
            unlink($path);
        }
        $fp = fopen($path,'a');
        $base_num= 0;
        foreach ($baseList as $base) {
            $phone = ArrayHelper::getValue($base,'mobile','');
            $identity_id = ArrayHelper::getValue($base,'user_id','');
            $create_time = ArrayHelper::getValue($base,'create_time','');
            if (empty($phone) || empty($identity_id)) {
                continue;
            }
            # 获取用户seqID
            $seq_id = $this->getSeqId($phone,$identity_id,$create_time);
            # 获取同盾数据
            $fm_data = (new Fmdown)->analysis($seq_id);
            # 获取七天和一个月时间
            # 七天
            $seven_detail_json = ArrayHelper::getValue($fm_data,'mid_fm_seven_d_detail','');
            $seven_detail_arr = json_decode($seven_detail_json,true);
            $seven_Arr = [
                '7天身份证总数' => ArrayHelper::getValue($seven_detail_arr,'借款人身份证个数',0),
                '7天身份证P2P网贷' => ArrayHelper::getValue($seven_detail_arr,'借款人身份证详情.0.P2P网贷',0),
                '7天身份证小额贷款公司' => ArrayHelper::getValue($seven_detail_arr,'借款人身份证详情.0.小额贷款公司',0),
                '7天身份证大型消费金融公司' => ArrayHelper::getValue($seven_detail_arr,'借款人身份证详情.0.大型消费金融公司',0),
                '7天身份证一般消费分期平台' => ArrayHelper::getValue($seven_detail_arr,'借款人身份证详情.0.一般消费分期平台',0),
                '7天手机总数' => ArrayHelper::getValue($seven_detail_arr,'借款人手机个数',0),
                '7天手机P2P网贷' => ArrayHelper::getValue($seven_detail_arr,'借款人手机详情.0.P2P网贷',0),
                '7天手机小额贷款公司' => ArrayHelper::getValue($seven_detail_arr,'借款人手机详情.0.小额贷款公司',0),
                '7天手机大型消费金融公司' => ArrayHelper::getValue($seven_detail_arr,'借款人手机详情.0.大型消费金融公司',0),
                '7天手机一般消费分期平台' => ArrayHelper::getValue($seven_detail_arr,'借款人手机详情.0.一般消费分期平台',0),
            ];
            # 一个月
            $one_mouth_detail_json = ArrayHelper::getValue($fm_data,'mid_fm_one_m_detail','');
            $one_mouth_detail_arr = json_decode($one_mouth_detail_json,true);
            $one_mouth_Arr = [
                '一个月身份证总数' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证个数',0),
                '一个月身份证P2P网贷' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证详情.0.P2P网贷',0),
                '一个月身份证小额贷款公司' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证详情.0.小额贷款公司',0),
                '一个月身份证大型消费金融公司' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证详情.0.大型消费金融公司',0),
                '一个月身份证一般消费分期平台' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人身份证详情.0.一般消费分期平台',0),
                '一个月手机总数' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机个数',0),
                '一个月手机P2P网贷' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机详情.0.P2P网贷',0),
                '一个月手机小额贷款公司' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机详情.0.小额贷款公司',0),
                '一个月手机大型消费金融公司' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机详情.0.大型消费金融公司',0),
                '一个月手机一般消费分期平台' => ArrayHelper::getValue($one_mouth_detail_arr,'借款人手机详情.0.一般消费分期平台',0),
            ];
            $allArr = array_merge($base,$seven_Arr,$one_mouth_Arr);
            clearstatcache();
            if (file_exists($path) && filesize($path) == 0) {
                $keys = array_keys($allArr);
                $head = implode(',',$keys).PHP_EOL;
                fwrite($fp, $head);
            }
            $data_arr = array_values($allArr);
            $data_str = implode(',',$data_arr).PHP_EOL;
            fwrite($fp, $data_str);
            $base_num++;
            echo "user_id : ".$identity_id.PHP_EOL;
        }
        fclose($fp);
        Logger::dayLog('fmtest', 'base_num ', $base_num);
        return true;
    }

    private function getSeqId($phone,$identity_id,$create_time) {
        $time = date('Y-m-d H:i:s',strtotime($create_time)+86400);
        $where = ['and',['phone'=>$phone],['identity_id'=>$identity_id],['<=','create_time',$time]];
        $seq_id = XsFraudmetrix::find()->where($where)->select('seq_id')->orderBy('ID DESC')->asArray()->one();
        if (empty($seq_id)) {
            return false;
        }
        return ArrayHelper::getValue($seq_id,'seq_id','');
    }
}