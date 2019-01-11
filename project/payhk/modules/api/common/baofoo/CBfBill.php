<?php
namespace app\modules\api\common\baofoo;

use app\common\Logger;
use app\common\Func;
use yii\helpers\ArrayHelper;
use app\models\baofoo\BfPayOrder;
use app\models\policy\ZhanPolicy;
use app\models\policy\PolicyBfBill;
use Yii;

/**
 * @desc 宝付转账账单下载
 */
class CBfBill{
    private $bfChannel = 114;//转账通道ID
    /**
     * @desc 获取此通道对应的配置
     * @param  int $channel_id 通道
     * @return str dev | prod102
     */
    private function getCfg($channel_id) {
        $is_prod = SYSTEM_PROD ? true : false;
        $is_prod = true;
        $cfg = $is_prod ? "prod{$channel_id}" : 'dev';
        return $cfg;
    }
    /**
     * @desc 按aid取不同的配置
     * @param  int  $aid 用于区分不同的商编
     * @return RbApi
     */
    public function getApi($channel_id) {
        static $map = [];
        if (!isset($map[$channel_id])) {
            $cfg = $this->getCfg($channel_id);
            $map[$channel_id] = new BaofooApi($cfg);
        }
        return $map[$channel_id];
    }
    /**
     * Undocumented function
     * 获取宝付账单
     * @param [type] $bill_date
     * @return void
     */
    public function runPaybill($bill_date){
        if(empty($bill_date)) return false;
        $bfApiObj = $this->getApi($this->bfChannel);
        $result = $bfApiObj->tranPayBill($bill_date);
        if(empty($result)) return false;
    
        $resp_body = ArrayHelper::getValue($result,'3');
        Logger::dayLog('bftranbill',$resp_body);
        $resp_body = base64_decode($resp_body);      
        $filePath = $this->getFilePath($bill_date);
        $res = file_put_contents($filePath, $resp_body);
        if($res){
            $this->getZipFile($filePath);
        }

    }
    private function getFilePath($bill_date){
        $path = '/ofiles/bfbill/'.date('Ym').'/'.$bill_date.'.zip';
        $filePath = Yii::$app->basePath . '/web' . $path;
        Func::makedir(dirname($filePath));
        return $filePath;
    }
    /**
     * Undocumented function
     * 获取zip文件内容
     * @param [type] $filePath
     * @return void
     */
    private function getZipFile($filePath){
        $zip = new \ZipArchive;
        $res = $zip->open($filePath);
        if ($res === TRUE) {
            $textdata = $zip->getFromIndex(0);
            $result = $this->parseData($textdata);
            $zip->close();
            
        }
        return $filePath;
    }
    /**
     * Undocumented function
     * 解析zip文件内容
     * @param [type] $textdata
     * @return void
     */
    private function parseData($textdata){
        if(empty($textdata)) return false;
        $textdata = explode(PHP_EOL,$textdata);
        //汇总信息
        $header = empty($textdata[1])?[]:$textdata[1];
        Logger::dayLog('policybfbill','头部汇总信息',$header);
        $bodydata = array_slice($textdata,4);
        if(empty($bodydata)) return false;
        foreach($bodydata as $k=>$v){
            $postdata = $this->getBillData($v);
            $model = new PolicyBfBill;
            $client_id = ArrayHelper::getValue($postdata,'client_id');//商户订单号
            $data_type = ArrayHelper::getValue($postdata,'data_type');//交易类型
            if($data_type!='00104') continue;
            if(empty($client_id)) continue;
            $oBill = $model->getBillByClientId($client_id);
            if($oBill){
                $res = $oBill->saveData($postdata);
            }else{
                $res = $model->saveData($postdata);
            }
            if(!$res){
                Logger::dayLog('policy/cbfbill','保存宝付账单失败',$postdata,$model->errinfo);
            }
        }
        return true;
    }
    /**
     * Undocumented function
     * 获取账单数据
     * @param [type] $v
     * @return void
     */
    private function getBillData($v){
        $data = explode("|",$v);
        $data_type = empty($data[2])?'':$data[2];//交易类型
        $client_id = empty($data[5])?'':$data[5];//商户交易号
        $bf_orderid = empty($data[4])?'':$data[4];//宝付订单号
        $settle_amount = empty($data[9])?'':$data[9];//交易金额
        $settle_fee = empty($data[10])?'':$data[10];//手续费
        $status = empty($data[8])?'0':$data[8];//状态
        $settle_time = empty($data[7])?'':$data[7];//结算时间
        $postdata = [
            'client_id'     => $client_id,
            'bf_orderid'    => $bf_orderid,
            'settle_amount' => $settle_amount,
            'settle_fee'    => $settle_fee,
            'status'        => $status,
            'settle_time'   => $settle_time,
            'data_type'     => $data_type
        ];
        return $postdata;
    }
}
