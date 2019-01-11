<?php

namespace app\modules\backend\controllers;

use Yii;
use app\models\WhiteIp;
use yii\data\Pagination;
use app\models\App;
use yii\helpers\ArrayHelper;

class WhiteIpController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {

        $get_data = $this->get();
        $aid = ArrayHelper::getValue($get_data, "aid");
        $ip = ArrayHelper::getValue($get_data, "ip");


        $pages = new Pagination([
            'totalCount' => $this->ipCount($aid, $ip),
            'pageSize'   => '20'
        ]);
        $res   = $this->ipResult($aid, $ip)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();
        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
                'aid'   => $aid,
                'ip'    => $ip,
        ]);
    }

    /**
     * 计算白名单条数
     * @param $aid
     * @param $ip
     * @return int
     */
    private function ipCount($aid, $ip)
    {
        $query = WhiteIp::find();
        if ($aid){
            $query->andWhere(['aid' => $aid]);
        }
        if ($ip){
            $query->andWhere(['ip' => $ip]);
        }
        return $query->count();
    }

    /**
     * 获取条数
     * @param $aid
     * @param $ip
     * @return \yii\db\ActiveQuery
     */
    private function ipResult($aid, $ip)
    {
        $res = WhiteIp::find();
        if ($aid){
            $res->andWhere(['aid' => $aid]);
        }
        if ($ip){
            $res->andWhere(['ip' => $ip]);
        }
        return $res;
    }

    public function actionAdd() {
        if ($this->isPost()) {
            $post = $this->post();
            $info = (new WhiteIp())-> find()->where(['ip' => $post['ip'],'aid'=>$post['aid']])->one();
            if (!empty($info)) {
                return $this ->showMessage(1 , '该IP已存在' );
            }
            $model = new WhiteIp();
            $res   = $model->createData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(2, '数据保存失败' );
            }
        }else{
            $appinfo = (new App())->getApp();
            return $this->render('add',['appinfo'=> $appinfo]);
        }
    }
    
    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $countInfo = WhiteIp::find()->where(['ip' => $post['ip'],'aid'=>$post['aid']])->andFilterWhere(['!=' , 'id',$post['id'] ])->count();
            if ($countInfo >= 1) {
                return $this ->showMessage(1, '该IP已存在' );
            }
            $ipInfo = WhiteIp::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = WhiteIp::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            $appinfo = (new App())->getApp();
            return $this->render('add' , [
                'post' => $ipInfo,
                'appinfo' => $appinfo,
                'doType' => 'update',
            ]);
        }
        
    }
    
    /*
     * $data   渲染页面表单数据
     * $page   渲染页面名称
     * $msg    渲染页面错误信息
     * $dpType 当前操作action名称
     */
    private function showErrorMsg($data, $page, $msg , $doType='') {
        return $this->render($page, [
            'msg'    => $msg,
            'post'   => $data,
            'doType' => $doType
        ]);
    }

    /*
     * 禁用和启用
     */

    public function actionStatus() {
        $id = intval(Yii::$app->request->get("id"));
        if ($id <= 0) {
            return false;
        }

        $info = WhiteIp::findOne($id);
        if (empty($info)) {
            return false;
        }

        if ($info->status === 1) {
            $info->status = 0;
        } else if ($info->status === 0) {
            $info->status = 1;
        }
        $res = $info->save();
        $this->redirect("/backend/white-ip");
    }

}
