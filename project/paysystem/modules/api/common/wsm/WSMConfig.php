<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/16
 * Time: 17:51
 */
namespace app\modules\api\common\wsm;

class WSMConfig
{
    /**
     * 读取配置项
     * @param $env
     * @return mixed
     */
    public function initConfig($env) {
        $env = $env == 'prod' ? 'prod' : 'dev';
        $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR .'wsm'.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."config.{$env}.php";
        $config = include $configPath;
        return $config;
    }
}