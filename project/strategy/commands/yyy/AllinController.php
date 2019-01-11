<?php
/**
 * 一亿元 评测决策
 *
 * 定时拉取st_strategy_request到st_request表中，同时调用java决策并存储决策结果
 * D:\phpstudy\php55\php.exe  D:\phpstudy\WWW\strategy_new\yii yyy/allin runAll
 */
namespace app\commands\yyy;

use Yii;
use app\commands\BaseController;
use app\commands\yyy\logic\AllInLogic;
use app\commands\yyy\logic\YyycreditLogic;

class AllinController extends BaseController
{
    protected $come_from;
    protected $aid;

    public function init()
    {
        $this->come_from = Yii::$app->params['from']['STRATEGY_ALLIN'];
        $this->aid = Yii::$app->params['aid']['SOURCE_YYY'];
    }

    public function runAll($from = null, $aid = null)
    {
        if (!$from) {
            $from = $this->come_from;
        }

        if (!$aid) {
            $aid = [$this->aid,14];
        }
        $oAllInLogic = new AllInLogic();
        $res = $oAllInLogic->runAllin($from, $aid);
        return true;
    }
}