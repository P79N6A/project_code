<?php
namespace app\commands\msg;

use app\models\news\Coupon_list;
use app\models\news\User;
use app\models\news\WarnMessageList;
use yii\console\Controller;

class SendcouponmessageController extends Controller {

    /*
     * 发送优惠券消息定时
     * */
    public function actionIndex($time_type=''){
        $limit = 200;
        $nowday=date('Y-m-d H:i:s');
        if($time_type == 1){
            $end_time=date('Y-m-d H:i:s',strtotime('-5 minutes'));
            $start_time=date('Y-m-d H:i:s',strtotime('-10 minutes'));
            $where = [
                "AND",
                ["BETWEEN",Coupon_list::tableName(). ".create_time", $start_time,$end_time],
                ['<',Coupon_list::tableName().'.start_date',$nowday],
                ['>',Coupon_list::tableName().'.end_date',$nowday],
                ['=',Coupon_list::tableName().'.status',1],
            ];
        }else{
            $three_time = date("Y-m-d 00:00:00", strtotime('+72 hours'));
            $where = [
                "AND",
                [Coupon_list::tableName().'.end_date'=>$three_time],
                [Coupon_list::tableName().'.status'=>1],
            ];
        }

        //有效期的优惠券且未使用的
        $Coupon_wsy= Coupon_list::find()->joinWith('user',true,'LEFT JOIN')->where($where);

        $total = $Coupon_wsy->count();
        $pages = ceil($total / $limit);
        $type=7;//优惠券
        for ($i = 0; $i < $pages; $i++) {
            $Coupon_wsy = $Coupon_wsy->offset($i*$limit)->limit($limit)->all();
            if (!empty($Coupon_wsy)) {
                $warnmsg_model = new WarnMessageList();
                $res = $warnmsg_model->todo($Coupon_wsy,$time_type,$type);
                $this->log("\n all:{$limit},SUCCESS:{$res},pages:{$i}\n");
            }
        }

    }
    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}