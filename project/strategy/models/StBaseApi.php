<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;
use app\models\yyy\YiUserCreditList;
use app\models\yyy\UserLoan;


/**
 * 决策DB数据源基类
 */
class StBaseApi 
{
    // 获取用户prome分数
	public function getPromeScore($data)
    {
        $user_id = ArrayHelper::getValue($data,'user_id','');
        $loan_id = ArrayHelper::getValue($data,'loan_id','');
        $aid = ArrayHelper::getValue($data,'aid',1);
        $prome_data = ['PROME_V4_SCORE' => 0,'result_tq' => 0];
        if (empty($user_id) && empty($loan_id)){
            return $prome_data;
        }
        # query yi_user_loan by loan_id for parent_loan_id
        $oUserLoan = new UserLoan();
        $loan_info = $oUserLoan->getLoanOne($loan_id);
        $parent_loan_id = ArrayHelper::getValue($loan_info,'parent_loan_id','');
        if (empty($parent_loan_id)) {
            return $prome_data;
        }
        # query result by loan_id (old loan)
        $old_res_where = ['user_id' => $user_id,'loan_id' => $parent_loan_id,'from' => 6];
        $db = new Result();
        $res = $db->getOne($old_res_where);
        if (empty($res)) {
            #######(new loan)
            # query yi_user_credit_list  by loan_id  for req_id 
            $credit_where = ['loan_id' => $parent_loan_id];
            $oYiUserCreditList = new YiUserCreditList();
            $user_credit = $oYiUserCreditList->getUserCredit($credit_where,'req_id');
            $req_id = ArrayHelper::getValue($user_credit,'req_id','');
            if (empty($req_id)) {
                return $prome_data;
            }
            # query st_request  by req_id  for request_id
            $db = new Request();
            $request_res = $db->getRequestByReqidOne($req_id);
            $request_id = ArrayHelper::getValue($request_res,'request_id','');
            if (empty($request_id)) {
                return $prome_data;
            }
            # query st_result  by request_id 
            $new_res_where = ['request_id' => $request_id,'from' => 68];
            $db = new Result();
            $res = $db->getOne($new_res_where);
        }
        
        $res_info = ArrayHelper::getValue($res,'res_info','');
        if (empty($res_info)) {
        	return $prome_data;
        }
    
        $prome_res = json_decode($res_info,true);
        if (empty($prome_res)){
        	return $prome_data;
        }
        
        $prome_data = [
        	'PROME_V4_SCORE' => (int)ArrayHelper::getValue($prome_res,'PROME_V4_SCORE', 0),
        	'result_tq' => (int)ArrayHelper::getValue($prome_res,'result_tq',0),
        ];
        return $prome_data;
    }

    // 复贷用户获取天启接口权限
    public function getReloanAuth($data) {
        $user_id = ArrayHelper::getValue($data,'user_id','');
        $loan_id = ArrayHelper::getValue($data,'loan_id','');
        $reloan_data = ['result_tq' => 0];
        if (empty($user_id) && empty($loan_id)){
            return $reloan_data;
        }

        $where = ['user_id' => $user_id,'loan_id' => $loan_id,'from' => 4];
        $db = new Result();
        $res = $db->getOne($where);
        if (empty($res)) {
            return $reloan_data;
        }

        $res_info = ArrayHelper::getValue($res,'res_info','');
        if (empty($res_info)) {
            return $reloan_data;
        }
    
        $reloan_res = json_decode($res_info,true);
        if (empty($reloan_res)){
            return $reloan_data;
        }
        
        $reloan_data = [
            'result_tq' => (int)ArrayHelper::getValue($reloan_res,'result_tq',0),
        ];
        return $reloan_data;
    }

    public function getReloanData($data) {
        $loan_id = ArrayHelper::getValue($data,'loan_id','');
        $reloan_data = ['wst_dlq_sts' => ''];
        if (empty($loan_id)){
            return $reloan_data;
        }
        $where = ['loan_id'=>$loan_id];
        $oStloanExtend = new StloanExtend();
        $loan_extend_data = $oStloanExtend->getLoanExtend($where);
        $reloan_data = [
            'wst_dlq_sts' => ArrayHelper::getValue($loan_extend_data, 'wst_dlq_sts', ''),
        ];
        return $reloan_data;
    }
}

