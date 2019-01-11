<?php

namespace app\modules\policyment\controllers;

use app\common\Common;
use app\models\App;
use app\models\policy\ZhanPolicy;
use Yii;
use yii\data\Pagination;
class PolicyController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];
    /**
     * Undocumented function
     * 待支付列表
     * @return void
     */
    public function actionIndex() {
        $get  = $this -> get();
        $where = [
            'remit_status'=>ZhanPolicy::STATUS_DOING,
            'pay_status'=>ZhanPolicy::PAY_INIT
        ];
        $inputArray = ['req_id','client_id','user_mobile','fund'];  //允许搜索的值
        if(!empty($get)){          
            foreach($get as $k => $v){
                if($v!=''&& in_array($k, $inputArray)){
                    $where[$k] =  $v;
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => ZhanPolicy::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = ZhanPolicy::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();

        //各资金方待支付金额合计
        $fund = ZhanPolicy::getFund();
        $pay_fund = [];
        foreach($fund as $k=>$v){
            $fundcount = ZhanPolicy::getPaySum($k);
            $temp = [
                'fund'=>$k,
                'fund_name'=>$v,
                'fund_count'=>$fundcount
            ];
            array_push($pay_fund,$temp);
        }
        //查询总额
        $all_pay = ZhanPolicy::getPaySum();
        //查询余额
        $balance = ZhanPolicy::getBfBalance();
        return $this->render('index', [
            'res'   => $res,
            'pages' => $pages,
            'get'   => $get,
            'pay_fund'=>$pay_fund,
            'all_pay'=>$all_pay,
            'balance'=>$balance
        ]);
    }
    /**
     * Undocumented function
     * 保单列表
     * @return void
     */
    public function actionIndex1() {
        $get  = $this -> get();
        $where = [];
        $inputArray = ['req_id','client_id','user_mobile','remit_status','pay_status','fund'];  //允许搜索的值
        if(!empty($get)){          
            foreach($get as $k => $v){
                if($v!=''&& in_array($k, $inputArray)){
                    $where[$k] =  $v;
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => ZhanPolicy::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = ZhanPolicy::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();
        return $this->render('index1', [
            'res'   => $res,
            'pages' => $pages,
            'get'   => $get
        ]);
    }
    /**
     * Undocumented function
     * 详情页
     * @return void
     */
    public function actionDetail(){
        $id = $this->get('id');
        $data = (new ZhanPolicy)->getPolicyById($id);
        return $this->render('detail',['data'=>$data]);
    }
}
