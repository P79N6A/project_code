<?php

namespace app\modules\policyment\controllers;

use app\common\Common;
use app\models\App;
use app\models\policy\PolicyCheckbill;
use app\models\policy\PolicyCheckbilldetail;
use Yii;
use yii\data\Pagination;
class BillController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];
    /**
     * Undocumented function
     * 对账列表
     * @return void
     */
    public function actionIndex() {
        
        $get  = $this -> get();
        $where = [];
        $inputArray = ['billDate','user_mobile','fund','billStatus'];  //允许搜索的值
        if(!empty($get)){          
            foreach($get as $k => $v){
                if($v!=''&& in_array($k, $inputArray)){
                    $where[$k] =  $v;
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => PolicyCheckbilldetail::find()->where($where)->count(),
            'pageSize'   => '20'
        ]);
        $res   = PolicyCheckbilldetail::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('id desc')->all();
        return $this->render('index', [
            'res'   => $res,
            'pages' => $pages,
            'get'   => $get
        ]);
    }
    /**
     * Undocumented function
     * 差异列表
     * @return void
     */
    public function actionDiffindex() {
        
        $get  = $this -> get();
        $where = ['billStatus'=>PolicyCheckbill::STATUS_FAILURE];
        $inputArray = ['billDate'];  //允许搜索的值
        if(!empty($get)){          
            foreach($get as $k => $v){
                if($v!=''&& in_array($k, $inputArray)){
                    $where[$k] =  $v;
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => PolicyCheckbill::find()->where($where)->count(),
            'pageSize'   => '2'
        ]);
        $res   = PolicyCheckbill::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('billDate desc')->all();
        $result = [];
        if(!empty($res)){
            foreach($res as $k=>$v){
                $billDate = $v['billDate'];
                $res_data = (new PolicyCheckbilldetail)->getDiffBill($billDate);
                array_push($result,$res_data);
            }
        }
        return $this->render('diffindex', [
            'res'   => $result,
            'pages' => $pages,
            'get'   => $get
        ]);
    }
     /**
     * Undocumented function
     * 完成保单列表
     * @return void
     */
    public function actionCompindex() {
        
        $get  = $this -> get();
        $where = ['billStatus'=>PolicyCheckbill::STATUS_SUCCESS];
        $inputArray = ['billDate'];  //允许搜索的值
        if(!empty($get)){          
            foreach($get as $k => $v){
                if($v!=''&& in_array($k, $inputArray)){
                    $where[$k] =  $v;
                }
            }
        }
        $pages = new Pagination([
            'totalCount' => PolicyCheckbill::find()->where($where)->count(),
            'pageSize'   => '2'
        ]);
        $res   = PolicyCheckbill::find()->where($where)->offset($pages->offset)->limit($pages->limit)->orderBy('billDate desc')->all();
        $result = [];
        if(!empty($res)){
            foreach($res as $k=>$v){
                $billDate = $v['billDate'];
                $res_data = (new PolicyCheckbilldetail)->getCompBill($billDate);
                array_push($result,$res_data);
            }
        }
        return $this->render('compindex', [
            'res'   => $result,
            'pages' => $pages,
            'get'   => $get
        ]);
    }
    /**
     * Undocumented function
     * 更新对账备注信息
     * @return void
     */
    public function actionSaveremark(){
        $id = $this->post('id');
        $remark = $this->post('remark');
        if(empty($id)||empty($remark)){
            echo json_encode(['res_code'=>'-1','res_msg'=>'参数缺失']);die;
        }
        $where = ['id'=>$id];
        $data = ['remark'=>$remark];
        $res = (new PolicyCheckbilldetail)->updateData($where,$data);
        if($res){
            echo json_encode(['res_code'=>0,'res_msg'=>'保存成功']);
        }else{
            echo json_encode(['res_code'=>-1,'res_msg'=>'保存失败']);
        }
    }
    /**
     * Undocumented function
     * 完成对账 更新状态
     * @return void
     */
    public function actionCompletebill(){
        $id = $this->post('id');
        if(empty($id)){
            echo json_encode(['res_code'=>'-1','res_msg'=>'参数缺失']);die;
        }
        $where = ['id'=>$id];
        $oDetail = (new PolicyCheckbilldetail)->getData($where);
        if(!$oDetail){
            echo json_encode(['res_code'=>-1,'res_msg'=>'保存失败，查询不到数据']);die;
        }
        $data = ['billStatus'=>PolicyCheckbilldetail::STATUS_SUCCESS];
        $res = (new PolicyCheckbilldetail)->updateData($where,$data);
        if($res){
            //查询是否还存在未处理差异账单
            $_where = [
                'billDate'=>$oDetail->billDate,
                'billStatus'=>PolicyCheckbilldetail::STATUS_FAILURE,
            ];
            $_oDetail = (new PolicyCheckbilldetail)->getData($_where);
            if(empty($_oDetail)){
                $res = (new PolicyCheckbill)->updateData(['billDate'=>$oDetail->billDate],['billStatus'=>PolicyCheckbill::STATUS_SUCCESS]);
            }
            echo json_encode(['res_code'=>0,'res_msg'=>'保存成功']);
        }else{
            echo json_encode(['res_code'=>-1,'res_msg'=>'保存失败']);
        }
    }
}
