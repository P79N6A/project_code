<?php
/***
 * 展期统计
 */
namespace app\modules\balance\controllers;

use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\modules\balance\models\yyy\UserLoan;
use app\modules\balance\models\yyy\RenewalPaymentRecord;
use app\modules\balance\models\yyy\LoanRepay;
use app\modules\balance\models\yyy\Renew_amount;
use app\modules\balance\common\CRepay;

class RenewalController  extends  AdminController {
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
            'days'              => $days_no,
            'loan_id'           => ArrayHelper::getValue($getData, 'loan_id'),
            'order_id'          => ArrayHelper::getValue($getData, 'order_id'),
            'start_time'        => ArrayHelper::getValue($getData, 'start_time'),
            'end_time'          => ArrayHelper::getValue($getData, 'end_time'),
        ];

        $oRenewal = new RenewalPaymentRecord();
        $totleCount = $oRenewal->countRenewal($filter_where);
        $pages = new Pagination([
            'totalCount' => $totleCount,
            'pageSize' => $pageSize,
        ]);
        $getAllData = $oRenewal->getAllData($pages, $filter_where);
        $page = new Pagination([
            'totalCount' => $totleCount,
            'pageSize' => $totleCount,
        ]);
        $getAllDatas = $oRenewal->getAllDatas($page,$filter_where);
        //已收本金累计
        $amount = number_format(ArrayHelper::getValue($getAllDatas, 'amount'),2);//本金累计

        //已收利息累计
        $interest_fee = number_format(ArrayHelper::getValue($getAllDatas, 'all_interest_fee'),2);
       //展期 服务费
        $actual_money = number_format(ArrayHelper::getValue($getAllDatas, 'actual_money'),2);

        return $this->render('list', [
            'bondType' => $bondType,
            'start_time'            => ArrayHelper::getValue($filter_where, 'start_time'),
            'end_time'              => ArrayHelper::getValue($filter_where, 'end_time'),
            'return_data'           => $getAllData,
            'pages'                 => $pages,
            'days'     => ArrayHelper::getValue($getData, 'days', 0),
            'totleCount' => $totleCount,
            'order_id' => ArrayHelper::getValue($getData, 'order_id', ''),
            'loan_id' => ArrayHelper::getValue($getData, 'loan_id', ''),
            'amount'  => $amount,
            'interest_fee'  => $interest_fee,
            'actual_money'  => $actual_money,

        ]);
    }

    
    public function actionDetails()
    {
        $getData = $this->get();
        if (empty($getData)){
            return false;
        }
        $loanId = ArrayHelper::getValue($getData, 'loan_id', 0);
        $orderId = ArrayHelper::getValue($getData, 'order_id', '');
        $filter_where = [
            'loan_id' => $loanId,
            'order_id' => $orderId
        ];
        $oRenewal = new RenewalPaymentRecord();
        $oRepay = new LoanRepay();
        $oRenewalNum= new Renew_amount();
        //借款信息
        $loanData = $oRenewal->getLoanData($filter_where);
        //还款信息
        $repayData = $oRepay->getDataByLoanid($loanId);
        //展期表信息
        $renewInfo = $oRenewalNum->getDataByLoanid($loanId);

        return $this->render('details', [
            'loanData' => $loanData,
            'repayData' => $repayData,
            'renewInfo' => $renewInfo
        ]);
    }

    public function actionRenewaldown()//导出
    {
        $getData = $this->get();
        $oRepay = new RenewalPaymentRecord();
        $oDownXls = new CRepay();
        
        $filter_where = [
            'days' => ArrayHelper::getValue($getData, 'days', ''),
            'start_time' => ArrayHelper::getValue($getData, 'start_time'),
            'end_time' => ArrayHelper::getValue($getData, 'end_time'),
        ];
        $res = $oRepay->getDownData($filter_where);
        $oDownXls->downRenewalXls($res);
        return json_encode(["msg"=>"success"]);
    }
}
