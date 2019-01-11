<?php
/**
 * 购卡订单
 * Created by PhpStorm.
 * User: Administrator
 */
namespace app\modules\balance\controllers;
use app\modules\balance\models\yx\OrderPay;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
#use app\modules\balance\controllers\ZrysController;

class PurchaseController extends  ZrysController
{


    /**
     * 差错账管理
     * @return string
     */
    public function actionList()
    {

        //条件
        $getData = $this->get();
        /*
         *  订单号     order_pay_no
         *    商户订单      paybill
         *    姓名        realname
         *    手机号       mobile
         *    商编号       channel_id
         *    付款开始结束时间      repay_start_time   repay_end_time
         *  创建开始结束时间        create_start_time   create_end_time
         * */
        $filter_where = [
            'order_pay_no'            => trim(ArrayHelper::getValue($getData, 'order_pay_no'),' '),
            'paybill'                 => trim(ArrayHelper::getValue($getData, 'paybill'),' '),
            'realname'                      => trim(ArrayHelper::getValue($getData, 'realname'),' '),
            'mobile'               => trim(ArrayHelper::getValue($getData, 'mobile'),' '),
            'channel_id'           => trim(ArrayHelper::getValue($getData, 'channel_id'),' '),
            'repay_start_time'                => trim(ArrayHelper::getValue($getData, 'repay_start_time',date('Y-m-d', strtotime('-7 day'))),' '),
            'repay_end_time'                  => trim(ArrayHelper::getValue($getData, 'repay_end_time',date('Y-m-d')),' '),
            'create_start_time'                  => trim(ArrayHelper::getValue($getData, 'create_start_time'),' '),
            'create_end_time'                => trim(ArrayHelper::getValue($getData, 'create_end_time'),' '),
        ];

        $oOrderPay = new OrderPay();
        //总笔数
        $total = $oOrderPay->getTotal($filter_where);
        //总金额
        $moneySum = $oOrderPay->getSum($filter_where,'money');
        //优惠卷累计
//        $couponSum = $oOrderPay->getSum($filter_where,'val');
        //实收累计
        $actualMoneySum = $oOrderPay->getSum($filter_where,'actual_money');

        $pageSize = ArrayHelper::getValue($getData, 'pageSize',30);
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => $pageSize,
        ]);
        $resultAllData = $oOrderPay->getAllData($filter_where,$pages);

        return $this->render('list', [
            'order_pay_no'     =>ArrayHelper::getValue($getData, 'order_pay_no'),
            'paybill'     =>ArrayHelper::getValue($getData, 'paybill'),
            'realname'     =>ArrayHelper::getValue($getData, 'realname'),
            'mobile'     =>ArrayHelper::getValue($getData, 'mobile'),
            'channel_id'     =>ArrayHelper::getValue($getData, 'channel_id'),
           /* 'repay_start_time'                => ArrayHelper::getValue($getData, 'repay_start_time',date('Y-m-d',time())),
            'repay_end_time'                  => ArrayHelper::getValue($getData, 'repay_end_time',date('Y-m-d',time())),
            'create_start_time'                  => ArrayHelper::getValue($getData, 'create_start_time',date('Y-m-d',time())),
            'create_end_time'                => ArrayHelper::getValue($getData, 'create_end_time',date('Y-m-d',time()))*/
             'repay_start_time'                => ArrayHelper::getValue($filter_where, 'repay_start_time'),
            'repay_end_time'                  => ArrayHelper::getValue($filter_where, 'repay_end_time'),
            'create_start_time'                  => ArrayHelper::getValue($getData, 'create_start_time'),
            'create_end_time'                => ArrayHelper::getValue($getData, 'create_end_time'),

            'resultAllData'   => $resultAllData,
            'total'     =>$total,
            'actualMoneySum'     =>$actualMoneySum,
//            'couponSum'     =>$couponSum,
            'moneySum'     =>$moneySum,
            'pages'                         => $pages,
            'pageSize'                         => $pageSize,
        ]);

    }

}