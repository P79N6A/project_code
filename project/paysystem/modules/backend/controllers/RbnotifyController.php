<?php
/**
 * 后台系统—融宝通知模块
 */
namespace app\modules\backend\controllers;

use app\models\open\RbClientNotify;
use Yii;
use yii\data\Pagination;
use app\models\Payorder;
class RbnotifyController  extends  AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $remit_id = $this->get('remit_id'); //出款id
        $where = [];
        if(!empty($remit_id)){
            $where['remit_id']=$remit_id;
        }
        $pages = new Pagination([
            'totalCount' => RbClientNotify::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = RbClientNotify::find()
            ->where($where)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();
       
        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
                'remit_id' => $remit_id
        ]);
    }

    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $ipInfo = RbClientNotify::findOne($post['id']);
            $res   = $ipInfo->saveNotifyStatus($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = RbClientNotify::findOne($id);
            if (empty($ipInfo)) {
                return $this->redirect('index');
            }
            return $this->render('add',[
                'post' => $ipInfo,
                'doType' => 'update',
            ]);
        }

    }

}
