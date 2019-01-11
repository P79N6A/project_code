<?php

namespace app\modules\settlement\controllers;

use app\models\App;
use app\models\bill\BillDetails;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class BillerrorController  extends  AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $pay_chan = Yii::$app->request->get('pay_chan');
        $get_data = $this->get();
        $bill_details_object = new BillDetails();
        $pages = new Pagination([
            'totalCount' => $bill_details_object->failBillTotal(),
            'pageSize'   => '20'
        ]);
        $res   = $bill_details_object->failBillData($pages);
        if (!empty($get_data['exportbutton']) && $get_data['exportbutton']=='down_excel'){
            $all_bill_data = $bill_details_object->getFailBillData();
            $this->downlist_xls_fail($all_bill_data);
        }
        return $this->render('index', [
            'res'   => $res,
            'pages' => $pages,
            'channel_data' => $this->channelData(),
        ]);
    }

    public function actionDetails() {
        $get_data = $this->get();
        if (empty($get_data['id'])){
            $this->redirect("index");
        }
        $bill_success_object = new BillDetails();
        $channel_data = $this->channelData();
        $res = $bill_success_object->getFailDetailsData($get_data['id']);
        if (empty($res)){
            $this->redirect("index");
        }
        return $this->render('details',[
            'result' =>$res,
            'channel_data' => $channel_data,
        ]);
    }

    public function actionUpdatebill(){

        if ($this->isPost()) {
            $post_data = $this->post();
            //查找错误账单
            $bill_details_object = new BillDetails();
            $fail_bill_data = $bill_details_object->getFailDetailsData(ArrayHelper::getValue($post_data, 'id', 0));
            if (empty($fail_bill_data)){
                return $this->returnFileJson("订单不存在");
            }
            //修改账单
            $update_bill_data = [
                //'error_types' => "差错已处理", //差错类型',
                'error_status' => 1, //差错状态',
                'type' => (int)BillDetails::SUCCESS_TYPE, //账单类型：1正常，2差错',
                'reason' => ArrayHelper::getValue($post_data, 'reason', ''), //原因',
                'uid' => Yii::$app->admin->id,
            ];
            $state = $fail_bill_data->updateBillData($update_bill_data);
            if ($state){
                return $this->returnFileJson("对账成功");
            }else{
                return $this->returnFileJson("对账失败");
            }

        }else{
            $this->redirect('index');
        }

    }
}