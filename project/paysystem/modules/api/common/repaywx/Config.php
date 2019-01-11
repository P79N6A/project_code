<?php
/**
 * 读取还款配置文件
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/16
 * Time: 17:51
 */
namespace app\modules\api\common\repaywx;

use app\common\Logger;
use yii\base\Exception;

class Config
{
    /**
     * 读取配置项
     * @param $cfg
     * @return mixed
     * @throws \Exception
     */
    public function getConfig($cfg=149) {
        //$env = SYSTEM_PROD ? 'prod' : 'dev';
        $env = "prod";
        $configPath = __DIR__ .DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."config.{$env}{$cfg}.php";
        if (!file_exists($configPath)) {
            Logger::dayLog("paywx/error", "file:",$configPath);
            throw new \Exception($configPath . "配置文件不存在", 98);
        }
        $config = include $configPath;
        return $config;
    }

    /**
     * 读取返回信息
     * @return mixed
     */
    public function returnInfo() {
        $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR .'repayment'.DIRECTORY_SEPARATOR."config".DIRECTORY_SEPARATOR."Returninfo.php";
        $config = include $configPath;
        return $config;
    }
}