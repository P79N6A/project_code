<?php
/**
 * 数据魔盒H5 对接接口公共类
 */
namespace app\modules\api\common\sjmh;

use app\common\Logger;
use yii\helpers\ArrayHelper;
use Yii;

class SjmhDockingApi
{

    //数据源的三种类型
    const SOURCE_CHSI = 1; // 学信
    const SOURCE_SHE_BAO = 2; // 社保
    const SOURCE_GJJ = 3; // 公积金
    const SOURCE_JD = 4; // 京东
    const SOURCE_TB = 6; // 淘宝
    const SOURCE_WY = 7; // 网银

    private $sourceType =[
        'SOURCE_CHSI' => self::SOURCE_CHSI,
        'SOURCE_SHE_BAO' => self::SOURCE_SHE_BAO,
        'SOURCE_GJJ' => self::SOURCE_GJJ,
        'SOURCE_JD' => self::SOURCE_JD,
        'SOURCE_TB' => self::SOURCE_TB,
        'SOURCE_WY' => self::SOURCE_WY,
    ];

    /**
     * 获取配置文件
     * @param $cfg
     * @return mixed
     * @throws \Exception
     */
    public function getConfig()
    {
        $is_prod = SYSTEM_PROD ? true : false;
        $cfg = $is_prod ? "prod" : 'dev';
        $configPath = __DIR__ . DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."{$cfg}.php";
        if (!file_exists($configPath)) {
            throw new \Exception($configPath . "配置文件不存在", 100);
        }
        $config = include $configPath;
        return $config;
    }



    //获取请求类型
    public function getTypeUrl($data){
        $config = $this->getConfig();
        $box_toke=ArrayHelper::getValue($config,'box_token');
        $type = strtoupper($data['source']);

        if(empty($data['user_id']) || empty($data['source'])){
            return false;
        }
        $url = 'error';
        switch($type){
            case  static::SOURCE_SHE_BAO : $url=ArrayHelper::getValue($config,'she_bao_url').'box_token='.$box_toke;break;
            case static::SOURCE_GJJ : $url=ArrayHelper::getValue($config,'gjj_url').'box_token='.$box_toke;break;
            case static::SOURCE_CHSI : $url=ArrayHelper::getValue($config,'chsi_url').'box_token='.$box_toke;break;
            case static::SOURCE_JD : $url=ArrayHelper::getValue($config,'jd_url').'box_token='.$box_toke;break;
            case static::SOURCE_TB : $url=ArrayHelper::getValue($config,'tb_url').'box_token='.$box_toke;break;
            case static::SOURCE_WY : $url=ArrayHelper::getValue($config,'wy_url').'box_token='.$box_toke;break;
            default :
                Logger::dayLog("sjmh/sjmhDockingApi",'getTypeUrl', "类型不存在");
        }
        $cb_url = ArrayHelper::getValue($data,'cb_url');
        unset($data['cb_url']);
        if($url != 'error'){
            $url = $url.'&cb='.urlencode($this->combinationUrl($cb_url,$data));
            return $url;
        }else{
            return false;
        }
    }


    //重组URl地址
    public function combinationUrl($url,$data){
        if(empty($url)){
            logger::dayLog('sjmh/sjmhDockingApi','combinationUrl','URL地址不能为空'.$url);
            return false;
        }
        if(!is_array($data)){
            logger::dayLog('sjmh/sjmhDockingApi','combinationUrl','数据必须为数组',$data);
            return false;
        }
        $link = strpos($url, "?") === false ? '?' : '';
        $url = $url.$link.http_build_query($data);
        return $url;
    }

    //根据source获取查询的url地址
    function getQueryUrl($source){
        $config = $this->getConfig();
        if(in_array($source,$this->sourceType)){
            $url = ArrayHelper::getValue($config,'query_url');
        }else{
            logger::dayLog('sjmh/collection','getQueryUrl','类型不存在');
            return false;
        }
        return   $url;
    }

    //判断source是否正确
    public function is_source($source){
        if(!in_array($source,$this->sourceType)){
            Logger::dayLog('sjmh/sjmhDocking','Save/error','source类型不存在：',$source);
            return true;
        }
    }


    /**
     * 获取字符串形式状态
     * @param  string $status_str
     * @return int | []
     */
    public function gStatus($status_str=null){
        if($status_str){
            return $this->sourceType[$status_str];
        }else{
            return $this->sourceType;
        }
    }



}