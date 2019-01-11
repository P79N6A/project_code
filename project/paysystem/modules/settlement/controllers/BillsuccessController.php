<?php

namespace app\modules\settlement\controllers;

use app\models\App;
use app\models\bill\BillDetails;
use app\models\Manager;
use Yii;
use yii\data\Pagination;
class BillsuccessController  extends  AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $pay_chan = Yii::$app->request->get('pay_chan');
        $get_data = $this->get();

        $bill_details_object = new BillDetails();
        $pages = new Pagination([
            'totalCount' => $bill_details_object->successBillTotal(),
            'pageSize'   => '20'
        ]);
        $res   = $bill_details_object->successBillData($pages);
        if (!empty($get_data['exportbutton']) && $get_data['exportbutton']=='down_excel'){
            $all_bill_data = $bill_details_object->getSuccessBillData();
            $this->downlist_xls($all_bill_data);
        }
        return $this->render('index', [
            'res'   => $res,
            'pages' => $pages,
            'channel_name_data' => $this->channelData(),
        ]);
    }

    public function actionDetails() {
        $get_data = $this->get();
        if (empty($get_data['id'])){
            $this->redirect("index");
        }
        $bill_success_object = new BillDetails();
        $channel_data = $this->channelData();
        $res = $bill_success_object->getSuccessDetailsData($get_data['id']);
        if (empty($res)){
            $this->redirect("index");
        }
        //操作人信息
        $omanager = new Manager();
        $manager_info = $omanager->getUserNameById($res->uid);
        return $this->render('details',[
            'result' =>$res,
            'channel_data' => $channel_data,
            'manager_info' => $manager_info,
        ]);
    }

    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            if(empty($post)){
                return $this ->showMessage(10 , '数据错误' );
            }
            $appInfo = App::findOne($post['id']);
            $res   = $appInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $appInfo = App::findOne($id);
            if (empty($appInfo)) {
                return $this->redirect('index');
            }
            return $this->render('add' , [
                'post' => $appInfo,
                'doType' => 'update',
            ]);
        }

    }
}