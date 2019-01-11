<?php

namespace app\modules\backend\controllers;
use Yii;
use yii\data\Pagination;
use app\models\JxlStat;

class JxlstatController extends AdminController {

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
            'totalCount' => JxlStat::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = JxlStat::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();
        
        $fromList = JxlStat::fromList();
        $isValid = JxlStat::isValid();
        return $this->render('index', [
                'fromList' => $fromList,
                'isValid' => $isValid,
                'get'   => $get,
                'res'   => $res,
                'pages' => $pages,
        ]);
    }
    
}
