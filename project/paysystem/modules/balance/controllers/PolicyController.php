<?php
/**
 * 保险统计
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 9:59
 */
namespace app\modules\balance\controllers;
use app\modules\balance\models\ZhanPolicy;
use app\modules\balance\common\CPolicy;
use Yii;
use \yii\helpers\ArrayHelper;
use yii\data\Pagination;

class PolicyController extends  AdminController
{
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];
    /**
     * 保险统计
     * @return string
     */
    public function actionList()
    {
        $pageSize = 30;
        $bondType = ZhanPolicy::getBondtype();
        $get  = $this -> get();
        $filter_where = [
            'policyDate'    => empty($bondType[ArrayHelper::getValue($get, 'type', 0)])?0:$bondType[ArrayHelper::getValue($get, 'type', 0)],
            'start_time'    => ArrayHelper::getValue($get, 'start_time'),
            'end_time'      => ArrayHelper::getValue($get, 'end_time'),
        ];
        $model = new ZhanPolicy();
        $pages = new Pagination([
            'totalCount' => $model->countPolicyData($filter_where),
            'pageSize' => $pageSize,
        ]);
        $res = $model->getPolicyData($pages, $filter_where);
        return $this->render('list', [
            'get'        => $get,
            'res'        => $res,
            'pages'      => $pages,
            'bondType'   => $bondType
        ]);
    }
    /**
     * Undocumented function
     * 导出明细Excel
     * @return void
     */
    public function actionExport()
    {
        $getData = $this->get();
        $bill_date = ArrayHelper::getValue($getData, 'bill_date');
        $days = ArrayHelper::getValue($getData, 'days');
        if(empty($bill_date)) return false;
        if(empty($days)) return false;
        $filter_where = [
            'days'          => $days,
            'start_time'    => $bill_date,
            'end_time'      => $bill_date.' 23:59:59',
        ];
        $res = (new ZhanPolicy)->getExportData($filter_where);
        (new  CPolicy)->exportExcel($res);
        return json_encode(["msg"=>"success"]);
    }
}