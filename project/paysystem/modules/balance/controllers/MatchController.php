<?php

namespace app\modules\balance\controllers;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\modules\balance\models\yyy\UserLoan;
use app\modules\balance\common\CRepay;

class MatchController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    /**
     * 还款统计
     * @return string
     */
    public function actionList()
    {
        $pageSize = 30;
        
        //债券类型
        $bondType = $this->getBondtype();

        //筛选条件
        $getData = $this->get();
        $days = ArrayHelper::getValue($getData, 'days', 0);
        $days_no = ArrayHelper::getValue($bondType, $days, 0);
        $filter_where = [
            'days'    => $days_no,
            'start_time'        => ArrayHelper::getValue($getData, 'start_time'),
            'end_time'          => ArrayHelper::getValue($getData, 'end_time'),
        ];

        $oRepay = new UserLoan();
        //统计总数
        $repayTotal = $oRepay->getRepayTotal($filter_where);

        $pages = new Pagination([
            'totalCount' => $oRepay->countRepayData($filter_where),
            'pageSize' => $pageSize,
        ]);
        $getAllData = $oRepay->getAllData($pages, $filter_where);

        return $this->render('list', [
            'bondType' => $bondType,
            'start_time'            => ArrayHelper::getValue($filter_where, 'start_time'),
            'end_time'              => ArrayHelper::getValue($filter_where, 'end_time'),
            'return_data'           => $getAllData,
            'pages'                 => $pages,
            'days'     => ArrayHelper::getValue($getData, 'days', 0),
            'repayTotal' => $repayTotal
        ]);
    }

    


    public function actionRepaydown()
    {
        $getData = $this->get();
        $oRepay = new UserLoan();
        $oDownXls = new CRepay();
        
        $filter_where = [
            'days' => ArrayHelper::getValue($getData, 'days', ''),
            'times' => ArrayHelper::getValue($getData, 'billtime', 0)
        ];
        $res = $oRepay->getDownData($filter_where);
        $oDownXls->downRepayXls($res);
        return json_encode(["msg"=>"success"]);
    }
}
