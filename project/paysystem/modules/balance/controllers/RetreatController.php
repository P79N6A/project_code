<?php
/**
 * 退卡订单
 * Created by PhpStorm.
 * User: Administrator
 */
namespace app\modules\balance\controllers;
use app\modules\balance\models\yx\OrderFail;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class RetreatController extends  ZrysController
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
         * 退卡订单     id
         *  订单号     order_pay_no
         *    商户订单      paybill
         *    姓名        realname
         *    手机号       mobile
         *    商编号       platform
         *      退卡方式    mode
         *      退卡状态    status
         *    退卡开始结束时间      start_time   end_time
         * */
        $filter_where = [
            'id'            => trim(ArrayHelper::getValue($getData, 'id'),' '),
            'order_pay_no'      => trim(ArrayHelper::getValue($getData, 'order_pay_no'),' '),
            'paybill'        => trim(ArrayHelper::getValue($getData, 'paybill'),' '),
            'realname'        => trim(ArrayHelper::getValue($getData, 'realname'),' '),
            'mobile'               => trim(ArrayHelper::getValue($getData, 'mobile'),' '),
            'channel_id'           => trim(ArrayHelper::getValue($getData, 'channel_id'),' '),
            #'mode'           => trim(ArrayHelper::getValue($getData, 'mode'),''),
            'status'           => trim(ArrayHelper::getValue($getData, 'status'),' '),
            'start_time'     => trim(ArrayHelper::getValue($getData, 'start_time'),' '),
            'end_time'         => trim(ArrayHelper::getValue($getData, 'end_time'),' '),
        ];

        $oOrderFail = new OrderFail();
        //总笔数
        $total = $oOrderFail->getTotal($filter_where);
        //总金额
        $moneySum = $oOrderFail->getSum($filter_where,'money');
        //实收累计
        $actualMoneySum = $oOrderFail->getSum($filter_where,'actual_money');

        $pageSize = ArrayHelper::getValue($getData, 'pageSize',30);
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => $pageSize,
        ]);
        $resultAllData = $oOrderFail->getAllData($filter_where,$pages);

        return $this->render('list', [
            'id'                => ArrayHelper::getValue($getData, 'id'),
            'order_pay_no'      => ArrayHelper::getValue($getData, 'order_pay_no'),
            'paybill'           => ArrayHelper::getValue($getData, 'paybill'),
            'realname'          => ArrayHelper::getValue($getData, 'realname'),
            'mobile'            => ArrayHelper::getValue($getData, 'mobile'),
            'channel_id'          => ArrayHelper::getValue($getData, 'channel_id'),
            'mode'              => ArrayHelper::getValue($getData, 'mode'),
            'status'            => ArrayHelper::getValue($getData, 'status'),
            'start_time'        => ArrayHelper::getValue($getData, 'start_time'),
            'end_time'          => ArrayHelper::getValue($getData, 'end_time'),

            'resultAllData'     => $resultAllData,
            'total'             =>$total,
            'actualMoneySum'    =>$actualMoneySum,
            'moneySum'          =>$moneySum,
            'pages'             => $pages,
            'pageSize'          => $pageSize,
        ]);

    }

}