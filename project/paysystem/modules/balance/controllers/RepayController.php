<?php

namespace app\modules\balance\controllers;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\modules\balance\models\yyy\UserLoan;
use app\modules\balance\common\CRepay;

class RepayController  extends  AdminController {
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

        //分期类型
        $types_of_stages = $this->typesOfStages();

        //资金方
        $capital_side = $this->capitalSide();

        //还款方式
        $wayOfPayment = $this->wayOfPayment();

        //筛选条件
        $getData = $this->get();
        $days = ArrayHelper::getValue($getData, 'days', 0);
        $days_no = ArrayHelper::getValue($bondType, $days, 0);
        $repay_type = ArrayHelper::getValue($getData, 'repay_type');
        $filter_where = [
            'types_of_stages'   => ArrayHelper::getValue($getData, 'types_of_stages'), //分期类型
            'fund'              => ArrayHelper::getValue($getData, 'capital_side'), //资金方
            'days'              => $days_no,
            'wayOfPayment'      => $repay_type, //还款方式
            'start_time'        => ArrayHelper::getValue($getData, 'start_time',date("Y-m-d")),
            'end_time'          => ArrayHelper::getValue($getData, 'end_time',date("Y-m-d")),
        ];
        //var_dump($filter_where);die;
        $conditionTime = 31*24*60*60;
        $strStart = strtotime($filter_where['start_time']);
        $strEnd = strtotime($filter_where['end_time']);
        if(($strEnd-$strStart)>$conditionTime){
            $strStart = $strEnd-$conditionTime;
        }
        $filter_where['start_time'] = date('Y-m-d',$strStart);
        $oRepay = new UserLoan();
        //统计总数
        //$repayTotal = $oRepay->getRepayTotal($filter_where);
        $repayTotal = $oRepay->getAllSum($filter_where);
        //var_dump($repayTotal);die;
        $totalCount = $oRepay->getAllTotal($filter_where);
        $totalCount = ArrayHelper::getValue(ArrayHelper::getValue($totalCount, 0), "count");
        $pages = new Pagination([
            //'totalCount' => $oRepay->countRepayData($filter_where),
            'totalCount'    => $totalCount,
            'pageSize'      => $pageSize,
        ]);
        $getAllData = $oRepay->getAllDatas($pages, $filter_where);
       // var_dump($getAllData);die;
        return $this->render('list', [
            'bondType' => $bondType,
            'start_time'            => ArrayHelper::getValue($filter_where, 'start_time'),
            'end_time'              => ArrayHelper::getValue($filter_where, 'end_time'),
            'return_data'           => $getAllData,
            'pages'                 => $pages,
            'days'                  => ArrayHelper::getValue($getData, 'days', 0),
            'repayTotal'            => ArrayHelper::getValue($repayTotal, 0),
            'types_of_stages'       => $types_of_stages,
            'capital_side'          => $capital_side,
            'condition'             => $filter_where,
            'wayOfPayment'          => $wayOfPayment,
            'repay_type'            => $repay_type,
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
