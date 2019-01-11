<?php

namespace app\modules\backend\controllers;

use app\models\Channel;
use app\models\BusinessChan;
use app\models\ChannelBank;
use Yii;
use yii\data\Pagination;

class ChannelController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $get  = $this -> get();
        $where = [
            BusinessChan::tableName().'.aid' => $this->aid,
        ];
        $inputArray = ['company_name','status'];  //允许搜索的值
        if(!empty($get)){          
            foreach($get as $k => $v){
                if($v!='' && in_array($k, $inputArray)){
                    $where[Channel::tableName().'.'.$k] =  $v;
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => Channel::find()->leftJoin(BusinessChan::tableName(), BusinessChan::tableName().'.channel_id='.Channel::tableName().'.id')->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = Channel::find()
            ->leftJoin(BusinessChan::tableName(), BusinessChan::tableName().'.channel_id='.Channel::tableName().'.id')
            ->where($where)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();
        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
                'get'   => $get
        ]);
    }

    public function actionAdd() {
        if ($this->isPost()) {
            $post = $this->post();
            $model = new Channel();
            $res   = $model->createData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(2, '数据保存失败' );
            }
        }else{
            return $this->render('add');
        }
    }
    
    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $ipInfo = Channel::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = Channel::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            return $this->render('add' , [
                'post' => $ipInfo,
                'doType' => 'update',
            ]);
        }
        
    }
    
    public function actionBank(){
        $channel_id = $this ->get('id');
        $bankInfo = (new ChannelBank()) ->getBankBychannelId($channel_id);
        return $this->render('bank' , [
            'res' => $bankInfo,
        ]);
    }
}
