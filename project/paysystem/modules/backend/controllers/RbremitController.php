<?php
/**
 * 后台系统—融宝出款模块
 */
namespace app\modules\backend\controllers;

use app\models\App;
use app\models\Business;
use app\models\open\Rbremit;
use app\models\BusinessChan;
use app\models\Channel;
use Yii;
use yii\data\Pagination;

class RbremitController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $get  = $this -> get();
        $inputArray = ['req_id','client_id','user_mobile','remit_status','aid'];  //允许搜索的值
        $where = [];
        if(!empty($get)){
            foreach($get as $k => $v){
                if($v!='' && in_array($k, $inputArray)){
                    $where[$k] =  $v;
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => Rbremit::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = Rbremit::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();
        $channel    = (new Channel)->getChannel();
        $business = (new Business)->getBusiness();

        return $this->render('index', [
                'channel' => $channel,
                'business' => $business,
                'get'   => $get,
                'res'   => $res,
                'pages' => $pages,
        ]);
    }

    public function actionUpdate(){
        if ($this->isPost()) {
            $post = $this->post();
            $ipInfo = Rbremit::findOne($post['id']);
            $res   = $ipInfo->updateData($post);
            if ($res) {
                return $this ->showMessage(0 , '操作成功' );
            } else {
                return $this ->showMessage(1, '数据保存失败' );
            }
        }else{
            $id = Yii::$app->request->get('id');
            $ipInfo = Rbremit::findOne($id);
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
}
