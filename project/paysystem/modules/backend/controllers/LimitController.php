<?php
/**
 * 后台系统—畅捷通知模块
 */
namespace app\modules\backend\controllers;

use app\models\open\BfClientNotify;
use app\models\open\RtSetting;
use Yii;
use yii\data\Pagination;
use app\models\Payorder;
class LimitController  extends  AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $aid = $this->get('aid'); //aid
        $where = [];
        if(!empty($aid)){
            $where['aid']=$aid;
        }
        $pages = new Pagination([
            'totalCount' => RtSetting::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = RtSetting::find()
            ->where($where)
            ->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy('id desc')
            ->all();
        return $this->render('index', [
                'res'   => $res,
                'pages' => $pages,
                'aid' => $aid
        ]);
    }

    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $ipInfo = RtSetting::findOne($post['id']);
            $res   = $ipInfo->saveNotifyStatus($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');

            $ipInfo = RtSetting::findOne($id);
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
