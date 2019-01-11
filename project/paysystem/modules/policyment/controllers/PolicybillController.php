<?php

namespace app\modules\policyment\controllers;

use app\common\Common;
use app\models\App;
use app\models\policy\ZhanPolicy;
use app\models\policy\PolicyBill;
use yii\helpers\ArrayHelper;
use Yii;
use yii\data\Pagination;
use app\modules\policyment\common\CPolicyBill;
class PolicybillController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];
    /**
     * Undocumented function
     * 对账列表
     * @return void
     */
    public function actionIndex() {
        $pageSize = 30;
        $policyStatus = PolicyBill::getPolicyStatus();
        $get  = $this -> get();
        $filter_where = [
            'dataType'      => ArrayHelper::getValue($get, 'status'),
            'start_time'    => ArrayHelper::getValue($get, 'start_time'),
            'end_time'      => ArrayHelper::getValue($get, 'end_time'),
            'channelOrderNo'=> ArrayHelper::getValue($get, 'client_id'),
            'policyNo'      => ArrayHelper::getValue($get, 'policyNo'),
        ];
        $model = new PolicyBill();
        $pages = new Pagination([
            'totalCount' => $model->countBillDetailData($filter_where),
            'pageSize' => $pageSize,
        ]);
        $res = $model->getBillDetailData($pages, $filter_where);
        return $this->render('index', [
            'get'        => $get,
            'res'        => $res,
            'pages'      => $pages,
            'policyStatus'   => $policyStatus
        ]);
    }
    /**
     * Undocumented function
     * 对账列表
     * @return void
     */
    public function actionList() {
        $pageSize = 30;
        $get  = $this -> get();
        $filter_where = [
            'start_time'    => ArrayHelper::getValue($get, 'start_time'),
            'end_time'      => ArrayHelper::getValue($get, 'end_time')
        ];
        $model = new PolicyBill();
        $pages = new Pagination([
            'totalCount' => $model->countBillData($filter_where),
            'pageSize' => $pageSize,
        ]);
        $res = $model->getBillData($pages, $filter_where);
        return $this->render('list', [
            'get'        => $get,
            'res'        => $res,
            'pages'      => $pages,
        ]);
    }
    /**
     * Undocumented function
     * 导出
     * @return void
     */
    public function actionExportbill(){
        $get  = $this -> get();
        $filter_where = [
            'start_time'    => ArrayHelper::getValue($get, 'start_time'),
            'end_time'      => ArrayHelper::getValue($get, 'end_time')
        ];
        $model = new PolicyBill();
        $data = $model->getExportBill($filter_where);
        (new CPolicyBill)->exportExcel($data);
    }
    /**
     * Undocumented function
     * 导出
     * @return void
     */
    public function actionExportdetail(){
        $get  = $this -> get();
        $filter_where = [
            'start_time'    => ArrayHelper::getValue($get, 'start_time'),
            'end_time'      => ArrayHelper::getValue($get, 'end_time')
        ];
        $model = new PolicyBill();
        $data = $model->getExportDetail($filter_where);
        (new CPolicyBill)->exportExcelDetail($data);
    }
}
