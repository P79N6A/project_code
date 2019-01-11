<?php
namespace app\common;
 
use Yii;

Class PLogger {
    
    private static $log_data=[];
    private static $instance=null;

    /**
     * 自动加载
     */
    private function __construct($source,$type = '') {
        static::$log_data = $this->logstart($source);
        register_shutdown_function([$this, 'logend'],$type);
    }

    /**
     * 类外调用并禁止类外多次实例
     */
    public static function getInstance($source,$type = ''){
        if(static::$instance){
            return static::$instance;
        }
        return static::$instance = new self($source,$type);
    }

    /**
     * 渲染数组
     */
    public static function getJson(){
        $json_data = [];
        $data = static::$log_data;
        if($data){
            $json_data['source']        = $data['source'];
            $json_data['request_time']  = $data['request_time'];
            // $json_data['start_time']    = $data['start_time'];
            $json_data['sessionId']     = $data['sessionId'];
            $json_data['ip']            = $data['ip'];
            $json_data['g_uid']         = $data['g_uid'];
            $json_data['is_login']      = $data['is_login'];
            $json_data['channelid']     = $data['channelid'];
            $json_data['activity']      = $data['activity'];
            $json_data['user_agent']    = $data['user_agent'];
            $json_data['from_url']      = $data['from_url'];
            $json_data['url']           = $data['url'];
            $json_data['cookieId']      = $data['cookieId'];
            $json_data['logId']         = $data['logId'];
            $json_data['_aid']          = $data['_aid'];
            $json_data['sign']          = $data['sign'];
            $json_data['uuid']          = $data['uuid'];
            if($data['source'] == 'weixin'){
                $json_data['nickname']  = $data['nickname'];
                $json_data['sex']       = $data['sex'];
                $json_data['area']      = $data['area'];
                $json_data['openId']    = $data['openId'];
                $json_data['event_name']= $data['event_name'];
            }
        }
//        print_r($json_data);die;
        return json_encode($json_data);
    }

    /**
     * 发送米富日志
     */
    public function logend($type = '') {

        $data = static::$log_data;
        $data['end_time']   =  $this->getMillisecond();
        //启动耗时
        $data['taken_time'] =  bcsub($data['end_time'],$data['start_time']);
        if (!empty($type)) {
            $data['url']          = $type;
        }
        return static::dayLogCsv('files/peanut_fulllog/'.$data['source'],$data);
    }

    /**
     * 获取服务器时间戳（精确到毫秒）
     */
    private function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float) sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * 获取参数
     * @params string   $source     [来源]
     * @return array    $header     [所需参数]
     */
    public function logstart($source){
        //获取用户信息
        // if ($source == 'weixin') {
        //     $user = Yii::$app->user->identity;
        // }else{
        //     $user = Yii::$app->userWeb->identity;
        // }
        
        $user = Yii::$app->user->identity;

        $header = [];
        $header['source']       = $source;
        $header['request_time'] = date('Y-m-d H:i:s');
        $header['start_time']   = $this->getMillisecond();
        $header['sessionId']    = session_id();
        $header['ip']           = $this->getUserHostAddressNoIIS();
        $header['g_uid']        = $user? Crypt3Des::encrypt($user->user_id):'';
        $header['is_login']     = $user?'yes':'no';
        $header['channelid']    = '';
        $header['activity']     = '';
        $header['user_agent']   = isset($_SERVER['HTTP_USER_AGENT'])?$_SERVER['HTTP_USER_AGENT']:'';
        $header['from_url']     = isset($_SERVER["HTTP_REFERER"])?$_SERVER["HTTP_REFERER"]:'';   //上一页url
        $header['url']          = $this->getUrl();         //当前url
        $header['cookieId']     = '';
        $header['logId']        = $source.$this->make_nonceStr().time();
        $header['_aid']         = '99';
        $header['sign']         = base64_encode(Crypt3Des::encrypt(time(),$header['logId']));
        $header['uuid']          = '';
        if($source == 'weixin'){
            $header['nickname']  = '';
            $header['sex']       = '';
            $header['area']      = '';
            //只授权为登录
            $header['event_name']= 'openIdInfo';
            $header['openId']    = Yii::$app->session->get('openid');
            //获取access_token值
            $access_token = Wxapi::getInstance()->getAccessToken();
            if($access_token && !empty($header['openId'])){
                //获取微信用户信息
                $user_info = $this->getUserInfo($access_token,$header['openId']);
                if(!array_key_exists('errcode',$user_info)){
                    if($user_info['sex'] == 1){
                        $sex = '女';
                    }elseif ($user_info['sex'] == 2){
                        $sex = '男';
                    }else{
                        $sex = '未知';
                    }
                    $header['nickname']     = $user_info['nickname'];
                    $header['sex']          = $sex;
                    $header['area']         = $user_info['city'];
                    //授权并登录
                    $header['event_name']   = 'userInfo';
                }
            }
        }

        foreach($header as $k=>$v){
            $header[$k] = str_replace(',','|',$v);
        }
        return $header;
    }

    /**
     * 日志记法
     * 0: file
     * 1... 内容自动以\t分隔, 数组自动var_export($c,true)转换成串
     */
    private static function dayLogCsv(){
        //1 获取第一个参数作为文件名
        $params = func_get_args();
        $filePath = $params[0];
        if( !$filePath ){
            return false;
        }
        unset($params[0]);
        if(empty($params)){
            return false;
        }

        //2 将参数重组
        $ps = [];
        foreach($params[1] as $key => $param){
            if( is_array($param) || is_object($param) ){
                $param = var_export($param, true);
            }
            $ps[] = $param;
        }

        $content = implode(",", $ps);
        static::saveLog($filePath, ','.$content,'csv');
        return true;
    }

    /**
     * 纪录错误日志
     * 按月分组
     */
    private static function saveLog( $categore , $content ,$suffix = 'txt'){
        if(SYSTEM_PROD){
            //生产
            // $filepath = \Yii::$app->basePath. "/../{$categore}/" . date('Ym/d') . '.'.$suffix;
            $filepath = \Yii::$app->basePath. "/log/{$categore}/" . date('Ym/d') . '.'.$suffix;
        }else{
            //测试
            $filepath = \Yii::$app->basePath. "/log/{$categore}/" . date('Ym/d') . '.'.$suffix;
        }
        self::log( $filepath , $content  );
    }

    /**
     * 写入日志
     */
    private static function log( $filepath, $line ){
        self::createdir(dirname($filepath));
        file_put_contents($filepath, $line."\n", FILE_APPEND);
    }

    /**
     * 创建目录
     */
    private static function createdir($dir){
        if(file_exists($dir))return true;
        $dir	= str_replace("\\","/",$dir);
        substr($dir,-1)=="/"?$dir=substr($dir,0,-1):"";
        $dir_arr	= explode("/",$dir);
        $str = '';
        foreach($dir_arr as $k=>$a){
            $str	= $str.$a."/";
            if(!$str)continue;
            if(!file_exists($str))mkdir($str,0775);
        }
        return true;
    }

    // 获取IP
    private function getUserHostAddressNoIIS() {
        switch (true) {
            case isset($_SERVER["HTTP_X_FORWARDED_FOR"]):
              $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
              break;
            case isset($_SERVER["HTTP_CLIENT_IP"]):
              $ip = $_SERVER["HTTP_CLIENT_IP"];
              break;
              default:
            $ip = $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] : '';
        }
        if (strpos($ip, ', ') > 0) {
            $ips = explode(', ', $ip);
            $ip = $ips[0];
        }
        return $ip;
    }

    /**
     * 生成唯一标示
     */
    private function make_nonceStr(){
        $codeSet = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        for ($i = 0; $i<16; $i++) {
            $codes[$i] = $codeSet[mt_rand(0, strlen($codeSet)-1)];
        }
        $nonceStr = implode($codes);
        return $nonceStr;
    }

    
    private function getUrl() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
    }


    /**
     * 获取用户信息（在用户关注公众号以后）
     */
    private function getUserInfo( $access_token, $open_id ){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$access_token.'&openid='.$open_id.'&lang=zh_CN';
        $data = Http::getCurl($url);
        $resultArr = json_decode($data, true);//转为数组
        return $resultArr ;
    }

}

