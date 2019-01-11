<?php
/**
 * 获取用户数据及借款信息基类
 */
namespace app\modules\promeapi\common;

use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

use app\models\Request;
use app\models\Loan;
use app\models\Stuser;
use app\models\Result;
use app\models\StloanExtend;
use app\models\yyy\User;
use app\models\yyy\UserLoan;
use app\models\loan\SfUser;
use app\models\loan\SfLoan;

class UserApi 
{
    public function getUserData($data)
    {
        if (!is_array($data) || empty($data)) {
            return [];
        }
        switch ($data['aid']) {
            case 1:
                $user = new User();
                $loan = new UserLoan();
                break;
            case 8:
                $user = new SfUser();
                $loan = new SfLoan();
                break;
            default:
                return [];
                break;
        }
        //获取用户基本信息
        $user_data = $this->getUser($user,$data);
        Logger::dayLog('user_data', '用户数据', $user_data,$data);
        //获取用户借款基本信息
        $user_loan = $this->getLoan($loan,$data);
        $alldata = array_merge($user_data,$user_loan);
        Logger::dayLog('user_loan', '借款数据', $user_loan,$data);
        $alldata = $this->transType($alldata);
        return $alldata;
    }
    /**
     * [getUser 获取用户基本信息]
     * @return [type] [description]
     */
    private function getUser($user,$data)
    {
        $where = ['user_id'=>$data['user_id']];
        $select = 'user_id,realname,come_from,mobile,identity';
        $user_data = $user->getInfo($where,$select);
        if (empty($user_data)) {
            return [];
        }
        return $user_data;
    }

    /**
     * 
     */
    private function getLoan($loan,$data)
    {
        $where = ['and',['user_id'=>$data['user_id']],['loan_id'=>$data['loan_id']]];
        $select = 'loan_id,user_id,create_time,business_type,source';
        $loan_data = $loan->getInfo($where,$select);
        if (empty($loan_data)) {
            return [];
        }
        $loan_data['loan_create_time'] = $loan_data['create_time'];
        unset($loan_data['create_time']);
        return $loan_data;
    }

    /**
     * [getUser 数据类型转换]
     * @return [type] [description]
     */
    public function transType($data)
    {
        foreach ($data as $k => $val) {
            if ($k != 'mobile' && $k != 'reg_time' && $k != 'realname' && $k != 'loan_create_time' && $k != 'identity') {
                $data[$k] = (int)$data[$k];
            }
        }
        return $data;
    }
}