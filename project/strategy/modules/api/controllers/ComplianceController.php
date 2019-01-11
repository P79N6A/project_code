<?php
/**
 * 合规请求数据接口
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/7
 * Time: 14:11
 */
namespace app\modules\api\controllers;
use app\modules\api\common\Compliance;
use Yii;

class ComplianceController extends ApiController
{
    private $_compliance;
    public function init()
    {
        $this->_compliance = new Compliance();
    }

    /**
     * 入口
     */
    public function actionIndex()
    {
        $datas = $this->post();
        $this->_compliance->logicalProcessing($datas);
    }
}