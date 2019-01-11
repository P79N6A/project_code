<?php

namespace app\modules\backend\controllers;

use app\models\App;
use app\models\Business;
use app\models\BusinessChan;
use app\models\Channel;
use Yii;
use yii\data\Pagination;
use app\models\Payorder;
use app\common\Logger;
class OrderController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $get  = $this -> get();
        //var_dump($get);
        $inputArray = ['other_orderid','orderid','phone','channel_id','business_id','status'];  //允许搜索的值
        $where = [];
        if(!empty($get)){
            foreach($get as $k => $v){
                if($v!=''&& in_array($k, $inputArray)){
                    $where[$k] =  $v;
                }
            }
        }

        //以多条支付通道为准
        if (!empty($get['channel_ids'])){
            unset($where['channel_id']);
        }

        $result_sql = Payorder::find()->where($where);
        if (!empty($get['channel_ids'])){
            $result_sql->andWhere(['in', 'channel_id', explode(',', $get['channel_ids'])]);
        }
        //时间区间
        if (!empty($get['start_date'])){
            $result_sql->andWhere(['>=', 'create_time', $get['start_date'].' 00:00:00']);
        }
        if (!empty($get['end_date'])){
            $result_sql->andWhere(['<=', 'create_time', $get['end_date'].' 23:59:59']);
        }

        $totalAll = $result_sql->count();
        $pages = new Pagination([
            'totalCount' => $totalAll,
            'pageSize'   => '20'
        ]);
        $res   = $result_sql->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();
        // echo Payorder::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')
        //                     ->createCommand()
        //                     ->getRawSql();

        $channel    = (new Channel)->getChannel();
        //var_dump($channel);die;
        $business = (new Business)->getBusiness();
        return $this->render('index', [
                'channel'       => $channel,
                'business'      => $business,
                'get'           => $get,
                'res'           => $res,
                'pages'         => $pages,
                'totalAll'      => $totalAll,
        ]);
    }

    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $ipInfo = Payorder::findOne($post['id']);
            $oldStatus = $ipInfo->status;
            $res = $ipInfo->editStatusByPerson($post['status']);
            if ($res) {
                Logger::dayLog('backend/order','changeOrderStatus成功,orderId:'.$ipInfo->id.',oldStatus:'.$oldStatus.',newStatus:'.$post['status']);
                return $this ->showMessage(0 , '操作成功' );
            } else {
                Logger::dayLog('backend/order','changeOrderStatus失败,orderId:'.$ipInfo->id.',oldStatus:'.$oldStatus.',newStatus:'.$post['status']);
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = Payorder::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            $app = App::find()->all();
            $channel = Channel::find()->all();
            $business = Business::find()->all();
            return $this->render('add',[
                'post' => $ipInfo,
                'app' => $app,
                'channel' => $channel,
                'business' => $business,
                'doType' => 'update',
            ]);
        }

    }

    public function actionSort($business_id) {
        $res   = BusinessChan::find()
            ->where("business_id = $business_id")
            ->orderBy('aid desc ,sort_num asc')
            ->all();
        return $this->render('sort', [
                'res'   => $res,
                'pages' => $pages,
        ]);
    }

    public function actionDosort() {
        $data = $this -> post('data');
        if(empty($data)){
            return $this ->showMessage(1 , '数据错误' );
        }
        $transaction = Yii::$app->db->beginTransaction();
        foreach($data as $key => $val){
            $info = BusinessChan::findOne($val);
            $info -> sort_num = $key + 1;
            $res = $info -> save();
            if (!$res) {
                $transaction->rollBack();
                return $this ->showMessage(2 , '数据保存出错' );
            }
        }
        $transaction->commit();
        return $this ->showMessage(0 , '操作成功' );
    }

}
