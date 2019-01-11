<?php

namespace app\modules\settlement\controllers;

use app\common\Common;
use app\models\App;
use app\models\bill\BillDetails;
use app\models\bill\ChannelBills;
use app\models\bill\UpBillFile;
use app\models\bill\ComparativeBill;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class UpperlistController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    /**
     * 上游通道出款列表
     * @return string
     */
    public function actionList()
    {
        $getData = $this->get();
        $oComparativeBill = new ComparativeBill();
        //分页调用
        $total = $oComparativeBill->getUperBillCount($getData);
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize'   => '20'
        ]);
        $res = $oComparativeBill->getUperBillData($pages, $getData);
        //总金额
        $total_money = $oComparativeBill->getUperBillMoney($getData);
        //总手续费
        $total_fee = $oComparativeBill->getUperBillFee($getData);
        // 差错账总笔数
        $total_bill_error = $oComparativeBill->getUperBillError($getData);
        return $this->render('list', [
            'pages'             => $pages,
            'passageOfMoney'    => $this->passageOfMoney(),
            'result'            => $res,
            'getData'           => $getData,
            'total'             => $total,
            'total_money'       => $total_money,
            'total_fee'         => $total_fee,
            'total_bill_error'  => $total_bill_error,
            'url_params'        => http_build_query($getData),
        ]);
    }
    public function actionDown()
    {
        $postData = $this->get();
        $oComparativeBill = new ComparativeBill();
        $res = $oComparativeBill->getUperBillDown($postData);
        $this->downlist_xls_uppper($res);
        return json_encode(["msg"=>"success"]);
    }
}
