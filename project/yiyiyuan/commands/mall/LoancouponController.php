<?php
namespace app\commands\mall;
use app\commands\BaseController;
use app\models\news\Coupon_list;
use app\models\news\Loan_repay;
use app\models\news\User;
use app\models\news\User_loan;
use yii\helpers\ArrayHelper;

class LoancouponController extends BaseController
{
    public function actionIndex()
    {
        //当前时间的前五分钟
        $start_date = date('Y-m-d H:i:00', strtotime('-10 minutes'));
        $new_date = date('Y-m-d H:i:00');  //当前时间
        $start_time = '2018-8-10 00:00:00';
        $end_time = '2018-8-25 23:59:59';
        //where 条件
        $where = [
            'AND',
            ['>=', Loan_repay::tableName() . '.last_modify_time', $start_date],
            ['<', Loan_repay::tableName() . '.last_modify_time', $new_date],
            ['>', Loan_repay::tableName() . '.createtime', $start_time],
            ['<', Loan_repay::tableName() . '.createtime', $end_time],
            [Loan_repay::tableName() . '.status' => 1],
            [User_loan::tableName() . '.status' => 8],
            [User_loan::tableName() . '.settle_type' => 1],
            [User_loan::tableName() . '.days' => 7],
        ];
        //两边联查 --符合以上条件的条数
        $sql = Loan_repay::find()->joinWith('userloan', 'TRUE', 'INNER JOIN')->where($where)->asArray()->all();
//        $arrId = array_column($sql, 'user_id');   //取user_id一列
        $arrId  = ArrayHelper::getColumn($sql, 'user_id');
        $userWhere = [
            'AND',
            ['in', User::tableName() . '.user_id', $arrId],
        ];
        $sqlIn = User::find()->joinWith('usercredit', 'TRUE', 'INNER JOIN')->where($userWhere)->asArray()->all();
        $couponMobile = ArrayHelper::getColumn($sqlIn, 'mobile');
        $model = new Coupon_list();
        $arr = [];
        foreach ($couponMobile as $k => $v) {
            $date = Coupon_list::find()->where(['mobile' => $v, 'title' => '7天享乐券'])->asArray()->all();
            //date为空的组成数组
            $num = count($date);
            if ($num<2){
                $arr[] = [
                    'title' => '7天享乐券',
                    'type' => 5,
                    'val' => 20,
                    'mobile' => $v,
                    'start_date' => date('Y-m-d H:i:s'),
                    'end_date' => date('Y-m-d H:i:s', strtotime('+1month')),
                    'create_time' => date('Y-m-d H:i:s'),
                    'sn' => date('ymdHis', time()) . '1',
                ];
            }
        }
        //总条数
        $countNum = count($arr);
        $res = 0;
        //如果$arr不为空  则批量添加
        if (!empty($arr)) {
            $res = $model->insertBatch($arr);
        }
        $this->log("\n all:{$countNum},SUCCESS:{$res}\n");
    }

// 纪录日志
    private function log($message)
    {
        echo $message . "\n";
    }
}
