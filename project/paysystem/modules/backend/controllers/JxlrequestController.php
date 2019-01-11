<?php

namespace app\modules\backend\controllers;
use Yii;
use yii\data\Pagination;
use app\models\open\JxlRequestModel;

class JxlrequestController extends AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav'  => 'pay',
    ];

    public function actionIndex() {
        $get  = $this -> get();
        $inputArray = ['name','phone','source','process_code'];  //允许搜索的值
        $where = [];
        if(!empty($get)){          
            foreach($get as $k => $v){
                if($v!=''&& $v!='0' && in_array($k, $inputArray)){
                    $where[$k] =  $v;
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => JxlRequestModel::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = JxlRequestModel::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();
        
        $fromList = JxlRequestModel::fromList();
        return $this->render('index', [
                'fromList' => $fromList,
                'get'   => $get,
                'res'   => $res,
                'pages' => $pages,
        ]);
    }
    
}
