<?php
/**
 * 异常处理类
 *
 * @author lubaba <luchao@xianhuahua.com>
 */
namespace app\modules\bankauth\common;

class ExceptionHandler extends \Exception
{
    /**
    * 抛出异常
    * @param    int $code    异常码
    * @param    string $msg  异常描述信息
    * @return   void
    */
    public static function make_throw($code, $msg='')
    {
        if (empty($msg)) {
            $configPath = __DIR__ . "/../config/errorCode.php";
            if (!file_exists($configPath)) {
                throw new \Exception($configPath . "配置文件不存在");
            }
            $config = include $configPath;
            $msg = !empty($config[$code]) ? $config[$code] : '';
        }
        throw new \Exception($msg, $code);
    }
}
