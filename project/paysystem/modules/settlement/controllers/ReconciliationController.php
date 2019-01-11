<?php

namespace app\modules\settlement\controllers;

use app\common\Common;
use app\models\App;
use app\models\bill\BillDetails;
use app\models\bill\ChannelBills;
use app\models\bill\ComparativeBill;
use app\models\Manager;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ReconciliationController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    /**
     * 对账成功列表
     * @return string
     */
    public function actionList()
    {
        $getData = $this->get();
        $oComparativeBill = new ComparativeBill();
        //总笔数
        $total = $oComparativeBill->getReconciliationCount($getData);
        //分页调用
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize'   => '20'
        ]);
        $res = $oComparativeBill->getReconciliationData($pages, $getData);
        //总金额
        $total_money = $oComparativeBill->getReconciliationMoney($getData);
        //总手续费
        $total_fee = $oComparativeBill->getReconciliationFee($getData);
        //差错账总笔数
        $total_bill_error = $oComparativeBill->getReconciliationError($getData);
        return $this->render('list', [
            'passageOfMoney'    =>$this->passageOfMoney(),
            'result'            => $res,
            'getData'           => $getData,
            'url_params'        => http_build_query($getData),
            'pages'             => $pages,
            'total'             => $total,
            'total_money'       => $total_money,
            'total_fee'         => $total_fee,
            'total_bill_error'  => $total_bill_error,
        ]);
    }

    public function actionDetails()
    {
        $getData = $this->get();
        if (empty($getData['id'])){
            $this->redirect('/settlement/reconciliation/list');
        }

        $oComparativeBill = new ComparativeBill();
        $result = $oComparativeBill->getBillData($getData['id']);

        //查找用户名
        $oManager = new Manager();
        $opt_user_info = $oManager->findIdentity(ArrayHelper::getValue($result, 'uid', 0));

        return $this->render('details', [
            'errorTypes'        => $this->errorTypes(),
            'passageOfMoney'    =>$this->passageOfMoney(),
            'url_params'        => http_build_query($getData),
            'result'            => $result,
            'getData'           => http_build_query($getData),
            'opt_name'          => ArrayHelper::getValue($opt_user_info, 'realname', ''),
        ]);

    }

    public function actionDown()
    {
        
        $postData = $this->get();
        $oComparativeBill = new ComparativeBill();
        $res = $oComparativeBill->getReconciliationDown($postData);
        $this->downlist_xls_uppper($res);
        return json_encode(["msg"=>"success"]);
    }
}
