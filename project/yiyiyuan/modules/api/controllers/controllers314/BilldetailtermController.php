<?php
namespace app\modules\api\controllers\controllers314;

use app\common\Logger;
use app\models\news\User_loan;
use app\models\news\ApplicationList;
use app\models\news\User;
use app\models\news\GoodsBill;
use app\modules\api\common\ApiController;
use app\commonapi\Apidepository;
use app\commonapi\Apihttp;
use Yii;

class BilldetailtermController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $loan_id = Yii::$app->request->post('loan_id',"");
        $user_id = Yii::$app->request->post('user_id',"");
        if(empty($loan_id) || empty($user_id)){
            exit($this->returnBack('99994'));
        }

        //查询是否有待还账单
        $yetBillList = (New GoodsBill())->getNotYetBillList($loan_id);//待还账单
        $mustBillList = (New GoodsBill())->getNotYetBillList($loan_id,2);//必还账单
        $allBillList = (New GoodsBill())->getNotYetBillList($loan_id,3);//所有账单
        if(!$yetBillList || !$allBillList ||  empty($yetBillList) || empty($allBillList)){
            exit($this->returnBack('10248'));
        }
        $mustIds = array_column($mustBillList,'id');
        //组装账单信息
        $yetBillListNew = $this->getBillListNew($yetBillList,$mustIds);
//        $resArr['allrepay'] = array_sum(array_map(function($val){return $val['actual_amount'];}, $yetBillList));//总待还
        $oUserLoan = User_loan::findOne($loan_id);
        $allRepay = (New User_loan()) ->getRepaymentAmount($oUserLoan);
        $resArr['allrepay'] = empty($allRepay)? 0.00 : sprintf('%.2f', $allRepay);//还款金额（兼容分期）
        $resArr['days'] = isset($yetBillList['0']['days']) ? $yetBillList['0']['days'] : "";//天数
        $resArr['terms'] = isset($yetBillList['0']['number']) ? $yetBillList['0']['number'] : "";//期数
        $resArr['yetbilllist'] = $yetBillListNew;
        exit($this->returnBack('0000', $resArr));
    }

    /*
     * 组装账单必还字段 repay_type 1为必还，2为可还
     */
    public function getBillListNew($yetBillList,$mustIds){
        $phaseArr = [1=>"首期应还金额",2=>"第二期应还金额",3=>"第三期应还金额",4=>"第四期应还金额",5=>"第五期应还金额",6=>"第六期应还金额",7=>"第七期应还金额",8=>"第八期应还金额",9=>"第九期应还金额"];
        if(empty($yetBillList) || !is_array($yetBillList)){
            return false;
        }
        foreach ($yetBillList as &$v){
            if(in_array($v['id'],$mustIds)){
                $v['repay_type'] = 1;
            }else{
                $v['repay_type'] = 2;
            }
            $v['bill_id'] = $v['id'];
            $v['end_time'] = date('Y-m-d',strtotime($v['end_time'])-24*3600);
            if(isset($phaseArr[$v['phase']])){
                $v['phase'] = $phaseArr[$v['phase']];
            }
            $v['actual_amount'] = empty($v['actual_amount'])? 0.00 : sprintf('%.2f', $v['actual_amount']);
        }
        return $yetBillList;
    }

}
