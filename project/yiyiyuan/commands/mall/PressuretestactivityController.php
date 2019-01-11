<?php
namespace app\commands\mall;
use app\commands\BaseController;
use app\models\news\Attention;
use app\models\news\Coupon_list;
use app\models\news\User;

class PressuretestactivityController extends BaseController
{
    /**
     * @return mixed|void
     * 定时发放优惠券
     */
    public function actionIndex()
    {
        $arr = [];
        $start_date = date('Y-m-d H:i:00');
        $end_date = date('Y-m-d H:i:00', strtotime('-1 hours'));
        $where = [
            'AND',
            ['<=',User::tableName() . '.create_time',$start_date],
            ['>=',User::tableName() . '.create_time',$end_date],
            [User::tableName() . '.come_from'=>5],
        ];
        $be_invited = User::find()->select(['from_code','mobile','user_id','openid'])->where($where)->asArray()->all();
        if($be_invited){
            $model = new Coupon_list();
            foreach ($be_invited as $key=>$val){
                $arr[] = [
                    'title' => '20元借款券',
                    'type' => 1,
                    'val' => 20,
                    'mobile' => $val['mobile'],
                    'start_date' => date('Y-m-d 00:00:00'),
                    'end_date' => date('Y-m-d 00:00:00', strtotime('+1month')),
                    'create_time' => date('Y-m-d H:i:s'),
                    'sn' => date('ymdHis', time()) . '1',
                ];
            }
            $res = 0;
            $countNum = count($arr);
            //如果$arr不为空  则批量添加
            if (!empty($arr)){
                for ($i=0;$i<5;$i++){
                    $res = $model->insertBatch($arr);
                }
            }
            $this->log("\n all:{$countNum},SUCCESS:{$res}\n");
        }
        //新用户关注公众号--发放优惠券
        $this->coupon();
    }

    /**
     * 新用户关注公众号
     * 发放优惠券
     */
    public function coupon()
    {
        $arr = [];
        $start_date = '2018-10-16 00:00:00';;
        $end_date = '2018-11-02 23:59:59';
        $model = new Coupon_list();
        $where = [
            'AND',
            ['>=',User::tableName() . '.create_time',$start_date],
            ['<=',User::tableName() . '.create_time',$end_date],
        ];
        $be_invited = User::find()->select(['from_code','mobile','user_id','openid'])->where($where)->asArray()->all();
        if($be_invited){
            foreach ($be_invited as $k=>$v){
                $attention  = Attention::find()->where(['openid'=>$v['openid']])->asArray()->one();
                if($attention){
                    $isCoupon = Coupon_list::find()->where(['title'=>'5元借款券','mobile'=>$v['mobile']])->one();
                    if(!$isCoupon){
                        $arr[] = [
                            'title' => '5元借款券',
                            'type' => 2,
                            'val' => 5,
                            'mobile' => $v['mobile'],
                            'start_date' => date('Y-m-d 00:00:00'),
                            'end_date' => date('Y-m-d 00:00:00', strtotime('+1month')),
                            'create_time' => date('Y-m-d H:i:s'),
                            'sn' => date('ymdHis', time()) . '1',
                        ];
                    }
                }
            }
            $res = 0;
            $countNum = count($arr);
            if(!empty($arr)){
                $res =  $model->insertBatch($arr);
            }
            $this->log("\n all:{$countNum},SUCCESS:{$res}\n");
        }
    }

    // 纪录日志
    private function log($message)
    {
        echo $message . "\n";
    }
}