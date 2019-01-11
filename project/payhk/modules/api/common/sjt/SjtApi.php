<?php
namespace app\modules\api\common\sjt;
use yii\helpers\ArrayHelper;
use app\modules\api\common\sjt\Util;
class SjtApi{
    private $config;
    private $oUtil;
    private $channel_code = '100000';//渠道编码
    private $channel_type = 'YYS';//渠道类型
    private $task_stage_init = 'INIT';//初始阶段
    private $request_type_submit = 'submit';//请求类型
    private $create_url = "https://api.shujumohe.com/octopus/task.unify.create/v3?";//创建任务url
    private $login_url = "https://api.shujumohe.com/octopus/yys.unify.acquire/v3?";//登录验证url
    private $query_url = "https://api.shujumohe.com/octopus/task.unify.query/v4?";//任务查询url
    private $report_url = "https://api.shujumohe.com/octopus/report.task.query/v2?";//报告查询url
    private $retry_url = "https://api.shujumohe.com/octopus/task.unify.retry/v3?";//重试url
    /**
     * 初始化接口
     */
    public function __construct(){
		$configPath = __DIR__ . "/config.php";
		if( !file_exists($configPath) ){
			throw new \Exception($configPath."配置文件不存在",6000);
		}
        $this->config = include( $configPath );
        $this->oUtil = new Util;
    }
    /**
     * Undocumented function
     * 创建任务
     * @return void
     */
    public function createTask($postdata){
        $params = [
            'channel_code'  =>$this->channel_code,
            'channel_type'  =>$this->channel_type,
            'real_name'     =>ArrayHelper::getValue($postdata,'name',''),
            'identity_code' =>ArrayHelper::getValue($postdata,'idcard',''),
            'user_mobile'   =>ArrayHelper::getValue($postdata,'phone',''),
        ];
        $url = $this->getUrl($this->create_url);
        $result = $this->oUtil->sendPost($url,$params);
        return $result;
    }
    /**
     * Undocumented function
     * 登录验证
     * @param [type] $postdata
     * @return void
     */
    public function loginAuth($postdata,$request_type=true){
        $params = [
            'task_id'       => ArrayHelper::getValue($postdata,'task_id'),
            'user_name'     => ArrayHelper::getValue($postdata,'phone'),
            'user_pass'     => ArrayHelper::getValue($postdata,'password'),
            'task_stage'    => 'INIT',//初次请求
            'request_type'  => $this->getRequestType($request_type),
        ];
        $url = $this->getUrl($this->login_url);
        $result = $this->oUtil->sendPost($url,$params);
        return $result;
    }
    /**
     * Undocumented function
     * 验证码 输入验证
     * @param [type] $postdata
     * @param boolean $request_type
     * @return void
     */
    public function loginCodeAuth($postdata,$request_type=true){
        $postdata['request_type'] = $this->getRequestType($request_type);
        $url = $this->getUrl($this->login_url);
        $result = $this->oUtil->sendPost($url,$postdata);
        return $result;
    }
    /**
     * Undocumented function
     * 任务查询接口
     * @param [type] $task_id
     * @return void
     */
    public function taskQuery($task_id){
        $postdata = [
            'task_id'=>$task_id
        ];
        $url = $this->getUrl($this->query_url);
        $result = $this->oUtil->sendPost($url,$postdata);
        return $result;
    }
    /**
    * Undocumented function
    * 报告查询接口
    * @param [type] $task_id
    * @return void
    */
   public function reportQuery($task_id){
       $postdata = [
           'task_id'=>$task_id
       ];
       $url = $this->getUrl($this->report_url);
       $result = $this->oUtil->sendPost($url,$postdata);
       return $result;
   }
   /**
    * Undocumented function
    * 验证码重试接口
    * @param [type] $task_id
    * @return void
    */
    public function codeRetry($task_id){
        $postdata = [
            'task_id'=>$task_id
        ];
        $url = $this->getUrl($this->retry_url);
        $result = $this->oUtil->sendPost($url,$postdata);
        return $result;
    }
    /**
     * Undocumented function
     * 获得请求阶段 empty INIT 
     * @param [type] $task_stage
     * @return void
     */
    private function getTaskStage($task_stage){
        return empty($task_stage)?'INIT':$task_stage;
    }
    /**
     * Undocumented function
     * 获得请求类型 true submit false query
     * @param [type] $request_type
     * @return void
     */
    private function getRequestType($request_type){
        return empty($request_type)?'query':'submit';
    }
    /**
     * Undocumented function
     * 获得标准url
     * @param [type] $url
     * @return void
     */
    private function getUrl($url){
        $partner_code = $this->config['partner_code'];
        $partner_key = $this->config['partner_key'];
        $_url = $url.'partner_code='.$partner_code.'&partner_key='.$partner_key;
        return $_url;
    }
}
?>