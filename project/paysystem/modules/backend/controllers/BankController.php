<?php

namespace app\modules\backend\controllers;

use app\models\Channel;
use app\models\ChannelBank;
use app\models\BusinessChan;
use Yii;
use yii\data\Pagination;
class BankController  extends  AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $pay_chan = Yii::$app->request->get('pay_chan');
        $bankname = Yii::$app->request->get('bankname');
        $where = [
            BusinessChan::tableName().'.aid' => $this->aid,
        ];
        if(isset($pay_chan) && $pay_chan!=''){
            $where[ BusinessChan::tableName().'.channel_id'] = $pay_chan;
        }
        if(isset($bankname) && $bankname!=''){
            $where[ ChannelBank::tableName().'.std_bankname'] = $bankname;
        }
        $pages = new Pagination([
            'totalCount' => ChannelBank::find()->leftJoin(Channel::tableName(),ChannelBank::tableName().'.channel_id='.Channel::tableName().'.id')->leftJoin(BusinessChan::tableName(), BusinessChan::tableName().'.channel_id='.Channel::tableName().'.id')->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = ChannelBank::find()
            ->leftJoin(Channel::tableName(),ChannelBank::tableName().'.channel_id='.Channel::tableName().'.id')
            ->leftJoin(BusinessChan::tableName(), BusinessChan::tableName().'.channel_id='.Channel::tableName().'.id')
            ->where($where)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('sort_num asc')
            ->all();
        $where = [
            BusinessChan::tableName().'.aid' => $this->aid,
        ];
        $pay_chanlist   = Channel::find()
            ->leftJoin(BusinessChan::tableName(), BusinessChan::tableName().'.channel_id='.Channel::tableName().'.id')
            ->where($where)
            ->orderBy('id desc')
            ->all();
         //查询银行列表
        $where = [
            BusinessChan::tableName().'.aid' => $this->aid,
        ];
        $banklist = ChannelBank::find()
            ->groupBy(ChannelBank::tableName().'.std_bankname')
            ->leftJoin(BusinessChan::tableName(), BusinessChan::tableName().'.channel_id='.ChannelBank::tableName().'.channel_id')
            ->where($where)
            ->orderBy('id desc')
            ->all();   

        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
                'pay_chanlist'=>$pay_chanlist,
                'pay_chan'=>$pay_chan,
                'banklist'=>$banklist,
                'bankname'=>$bankname
        ]);
    }
    
    public function actionAdd() {
        if ($this->isPost()) {
            $post = $this->post();
            if(empty($post)){
                return $this ->showMessage(10 , '数据错误' );
            }
            $where=[
                'channel_id' => $post['channel_id'] ,
                'bankcode' => $post['bankcode'],
                'card_type' => $post['card_type']
            ];
            $info = (new ChannelBank())-> getBankByConditions($where,TRUE);
            if (!empty($info)) {
                return $this ->showMessage(1 , '该银行配置已存在' );
            }
            $model = new ChannelBank();
            $res   = $model->createData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(2, '数据保存失败' );
            }
        }else{
            $channelinfo = (new Channel())->getChannel();
            return $this->render('add',['channelinfo'=> $channelinfo]);
        }
    }
    
    public function actionUpdate(){ 
        // $where=[
        //     'AND',
        //     ['channel_id' => $post['channel_id']],
        //     ['bankcode' => $post['bankcode']],
        //     ['card_type' => $post['card_type']],
        //     ['!=' ,'id',$post['id']],
        // ];
        // $info = (new ChannelBank())-> getBankByConditions($where);
        if ($this->isPost()) {
            $post = $this->post();
            if(empty($post)){
                return $this ->showMessage(10 , '数据错误' );
            }
            $where=[
                'AND',
                ['channel_id' => $post['channel_id']],
                ['bankcode' => $post['bankcode']],
                ['card_type' => $post['card_type']],
                ['!=' ,'id',$post['id']],
            ];
            $info = (new ChannelBank())-> getBankByConditions($where);
            if (!empty($info)) {
                return $this ->showMessage(1 , '该银行配置已存在' );
            }
            $ipInfo = ChannelBank::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = ChannelBank::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            $channelinfo = (new Channel())->getChannel();
            return $this->render('add' , [
                'post' => $ipInfo,
                'channelinfo' => $channelinfo,
                'doType' => 'update',
            ]);
        }
        
    }
}
