<?php
/**
 * 提交到微神马或是查询订单
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/16
 * Time: 16:52
 * D:\phpStudy\php\php-5.6.27-nts\php.exe d:\www\paysystem\yii weishenma runremits
 * D:\phpStudy\php\php-5.6.27-nts\php.exe d:\www\paysystem\yii weishenma runquerys
 */
namespace app\commands;
use app\modules\api\common\wsm\CWSMRemit;
use Yii;
use app\common\Logger;
class WsmremitController extends BaseController
{
    private $env;
    public function init(){
        $this->env = SYSTEM_PROD ? 'prod' : 'dev';
    }
    /**
     * 微神马--推送
     * 每五分钟执行一次
     */
    public function runRemits()
    {
        $oM = new CWSMRemit();
        $data = $oM->runRemits();
        Logger::dayLog('wsm/wsm_sends_data', 'content',$data);
        return json_encode($data);
    }

    /**
     * 微神马--补单
     * 每五分钟执行一次
     */
    public function runQuerys()
    {
        $oM = new CWSMRemit();
        $data = $oM->runQuerys();
        Logger::dayLog('wsm/wsm_querys_data', 'content',$data);
        return json_encode($data);
    }

    /**
     * 微神马--拉取订单
     * 每五分钟执行一次
     */
    public function runBill()
    {
        $oM = new CWSMRemit();
        $data = $oM->runBill();
        Logger::dayLog('wsm/wsm_querys_data', 'content',$data);
        return json_encode($data);
    }


}