<?php
namespace app\commands;

use yii\console\Controller;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');
abstract class BaseController extends Controller
{
    /**
     * 转发到子类
     */
    public function actionIndex()
    {
        //1 参数验证
        $args = func_get_args();
        $method = $args[0];
        if (empty($method)) {
            echo 'error:empty method!';
            exit;
        }
        unset($args[0]);

        //2 调用方法
        if (!is_array($args)) {
            $args = [];
        }
        return call_user_func_array([$this, $method], $args);
    }
    /**
     * 日志记法
     * 0: file
     * 1... 内容自动以\t分隔, 数组自动var_export($c,true)转换成串
     */
    protected function dayLog()
    {
        call_user_func_array(['\app\commonapi\Logger', 'dayLog'], func_get_args());
        return true;
    }
    /**
     * 根据sql获取全部数据
     */
    public function getAllBySql($sql)
    {
        $connection = Yii::$app->db;
        $command = $connection->createCommand($sql);
        return $command->queryAll();
    }
}
