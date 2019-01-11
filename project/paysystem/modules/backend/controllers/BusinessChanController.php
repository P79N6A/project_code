<?php

namespace app\modules\backend\controllers;

use app\models\App;
use app\models\Business;
use app\models\BusinessChan;
use app\models\Channel;
use Yii;
use yii\data\Pagination;

class BusinessChanController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $pay_buss = Yii::$app->request->get('pay_buss');
        $where = [
            BusinessChan::tableName().'.aid' => $this->aid,
            Channel::tableName().'.status' => 1,
        ];
        if(isset($pay_buss) && $pay_buss!=''){
            $where[ BusinessChan::tableName().'.business_id'] = $pay_buss;
        }
        $pages = new Pagination([
            'totalCount' => BusinessChan::find()->leftJoin(Channel::tableName(),BusinessChan::tableName().'.channel_id='.Channel::tableName().'.id')->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = BusinessChan::find()
            ->where($where)
            ->leftJoin(Channel::tableName(),BusinessChan::tableName().'.channel_id='.Channel::tableName().'.id')
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('aid desc ,business_id asc,sort_num asc')
            ->all();
        //查询项目业务列表
        $pay_busslist = Business::find()->where(array('aid'=>$this->aid))->all();
        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
                'pay_busslist'=>$pay_busslist,
                'pay_buss'=>$pay_buss
        ]);
    }

    public function actionAdd() {

        if ($this->isPost()) {
            $post = $this->post();
            if(empty($post)){
                return $this ->showMessage(1 , '数据错误' );
            }
            $where = ['aid' => $post['aid'],'channel_id' => $post['channel_id'], 'business_id' => $post['business_id']];
            $info = (new BusinessChan()) ->getBusinessChanByConditions($where);
            if(!empty($info)){
                return $this ->showMessage(1 , '该配置已存在');
            }
            $model = new BusinessChan();
            $res   = $model->createData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(2, '数据保存失败' );
            }
        }else{
            $post =[
                'status' =>'',
            ];
            $app = App::find()->all();
            $channel = Channel::find()->where("status=1")->all();
            $business = Business::find()->where("status=1")->all();
            return $this->render('add',[
                'app' => $app,
                'channel' => $channel,
                'business' => $business,
                'post'     =>$post,
            ]);
        }
    }
    
    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $ipInfo = BusinessChan::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = BusinessChan::findOne($id);
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
        $where = [
            BusinessChan::tableName().'.business_id' => $business_id,
            Channel::tableName().'.status' => 1,
        ];
        $res   = BusinessChan::find()
            ->where($where)
            ->leftJoin(Channel::tableName(),BusinessChan::tableName().'.channel_id='.Channel::tableName().'.id')
            ->orderBy('aid desc ,sort_num asc')
            ->all();

        return $this->render('sort', [
                'res'   => $res,
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
