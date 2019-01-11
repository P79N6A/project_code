<?php
/**
 * 数据魔盒H5 对接接口公共类
 */
namespace app\modules\api\common\baiduhc;

use app\common\Logger;
use yii\helpers\ArrayHelper;
use Yii;

class BaiduhcApi
{

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



    //请求百度黑产
    public function getSign($data){
        if(empty($data)){
            return false;
        }
        $config = $this->getConfig();
        $sp_no = ArrayHelper::getValue($config,'sp_no');//商户号
        $key = ArrayHelper::getValue($config,'key');
        $service_id = ArrayHelper::getValue($config,'service_id');
        $query_url = ArrayHelper::getValue($config,'query_url');
        $data['sp_no'] = $sp_no;
        $data['service_id'] = $service_id;
        $singStr='';
        $arrinput = ksort($data);//ASCII排序
        foreach($data as $k=>$v){

            $singStr .= '&'.$k.'='.$v;
        }
        $str = substr($singStr,1);
        $signStr = $str.'&key='.$key;
        $sign = md5($signStr);
        $url = $query_url.$str.'&sign='.$sign;
        $result = file_get_contents($url);
        return $result;

    }

    //数据组合
    /*
    *$parm  $value  用户传输的数据
    *$parm  $postdata  请求接口的数据
    */
    public function sendFormat($value,$postdata){

        $data=[
            'user_id'    => ArrayHelper::getValue($value,'user_id'),
            'aid'        => ArrayHelper::getValue($value,'aid',0),
            //'channel_id' => ArrayHelper::getValue($value,'channel_id',0),
            'name'       =>   ArrayHelper::getValue($value,'name'),
            'identity'   =>   ArrayHelper::getValue($value,'identity'),//身份证
            'mobile'      =>   ArrayHelper::getValue($value,'phone'),
            'create_time'   =>   ArrayHelper::getValue($postdata,'datetime'),
            'reqid'      =>   ArrayHelper::getValue($postdata,'reqid'),

        ];
        return $data;



    }





}