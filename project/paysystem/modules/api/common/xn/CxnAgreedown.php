<?php
/**
 * 小诺出款协议下载
 */
namespace app\modules\api\common\xn;
use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;
use app\models\xn\XnAgreement;
use app\modules\api\common\xn\XnApi;


set_time_limit(0);

class CxnAgreedown {
    private $xnAgreement;
    //成功 未知
    private static $commitProcessCode = 0;
    /**
     * 初始化接口  小诺协议询接口
     */
    public function __construct() {
        $this->xnAgreement = new XnAgreement;
    }

  
    /**
     * 一般是每几分钟执行
     */
    public function runAgreedown() {
        //1 获取需要通知的数据
        $restNum = 100;
        $dataList = $this->xnAgreement->getDownList($restNum);
        return $this->runDown($dataList);
    }
 
    /**
     * 暂时五分钟跑一批:
     * 下载
     */
    public function runDown($dataList) {
        //1 验证
        if (!$dataList) {
            return false;
        }
        //2 锁定状态为处理中
        $ids = ArrayHelper::getColumn($dataList, 'id');
        $ups = $this->xnAgreement->lockDown($ids);
        if (!$ups) {
            return false;
        }
        //4 逐条处理
        $total = count($dataList);
        $success = 0;
        foreach ($dataList as $oAgree) {
            $result = $this->doDown($oAgree);
            if ($result) {
                $success++;
            } 
        }

        //5 返回结果
        return $success;
    }
    /**
     * 下载
     * @param object $oAgree
     * @return bool
     */
    protected function doDown($oAgree) {
        //1 参数验证
        if (!$oAgree) {
            return false;
        }
    
        $loan_url = $oAgree['loan_url'];
        $consulting_url = $oAgree['consulting_url'];
        $entrustment_url = $oAgree['entrustment_url'];

        $file_path = Yii::$app->basePath . '/web/xnagreement/'.date('Ym').'/'.date('d').'/'.$oAgree['bid_no'].'/';
        if (!is_dir($file_path)) mkdir($file_path, 0777,true); 

        $res = $this->HttpClientPost($loan_url);
        $fileName = $oAgree['bid_no'].'_1.pdf';
        $f1 =  file_put_contents($file_path.$fileName,$res);

        $consult = $this->HttpClientPost($consulting_url);
        $consultName = $oAgree['bid_no'].'_2.pdf';
        $f2 =  file_put_contents($file_path.$consultName,$consult);

        $entrustment = $this->HttpClientPost($entrustment_url);
        $entName = $oAgree['bid_no'].'_3.pdf';
        $f3 =  file_put_contents($file_path.$entName,$entrustment);
        if($f1 && $f2 && $f3)
        {
            $res = $oAgree->saveDownSuccess();
            if(!$res){
                Logger::dayLog('xn/cxnagreedown','saveDownSuccess',$oAgree->attributes);
                return false;
            }
        }else{
            $res = $oAgree->saveDownInit();
            if(!$res){
                Logger::dayLog('xn/cxnagreedown','saveDownInit','saveDownInit',$oAgree->attributes);
                return false;
            }
        }    
   

        return true;
    }

    /**
     * @desc 提交数据
     * @param string $url
     * @param string $data
     * @return string
     */
    private function HttpClientPost($url) 
    {
        $ci = curl_init();
        curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ci, CURLOPT_HEADER, false);
        curl_setopt($ci, CURLOPT_URL, $url);
        $response = curl_exec($ci);
        curl_close($ci);
        return $response;

    }
    /**
     * 查询协议列表 获取数据
     */
    public function runAgreefix() {
        //1 获取需要通知的数据
        $restNum = 200;
        $dataList = $this->xnAgreement->getAgreeList($restNum);
        return $this->runFix($dataList);
    }
    private function runFix($dataList){
        if(empty($dataList)) return false;
        foreach ($dataList as $key => $oXnAgree) {
            $this->doFix($oXnAgree);
        }
    }
    /**
     * 拉取协议
     * @param object $oXnAgree
     * @return bool
     */
    private function doFix($oXnAgree) {
        $bodyInfo = [
            'bid_no'=>$oXnAgree->bid_no
        ];
        $xnApiObj = $this->getApi();
        $result = $xnApiObj->getJsonParam($bodyInfo,'agreement');       
        Logger::dayLog('xn/cxnagreefix','拉取协议结果',$result,$oXnAgree->bid_no);
        $res_code = ArrayHelper::getValue($result,'code','');
        $msg = ArrayHelper::getValue($result,'msg','');
        $url = ArrayHelper::getValue($result,'url','');
        if ($res_code == self::$commitProcessCode) {
            //更新数据
            $url_data = $this->getUrl($url);
            $url_data['code']=(int)$res_code;
            $url_data['msg'] = $msg;
            $result = $oXnAgree->updateData($url_data);
            if(!$result){
                Logger::dayLog('xn/cxnagreefix', 'updateData', $url_data,$oXnAgree->bid_no,'保存失败',$oXnAgree->errinfo);
            }
            $this->doDown($oXnAgree);
        }
    }
    /**
     * 配置
     * 
     * @return XnApi
     */
    private function getApi() {
        static $map = '';
        $is_prod = SYSTEM_PROD;
        //$is_prod = true;
        $env = $is_prod ? 'prod' : 'dev';
        $map = new XnApi($env);
        return $map;
    }
    private function getUrl($data)
    {
        $loan_url = isset($data[0]['second'])?$data[0]['second']:'';//借款协议
        $consulting_url = isset($data[1]['second'])?$data[1]['second']:'';//借款咨询协议
        $entrustment_url = isset($data[2]['second'])?$data[2]['second']:'';//委托协议
        $res=array(
            'loan_url'=>$loan_url,
            'consulting_url'=>$consulting_url,
            'entrustment_url'=>$entrustment_url
        );
        return $res;
    }
}