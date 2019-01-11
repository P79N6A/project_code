<?php

namespace app\modules\balance\controllers;
use app\modules\balance\models\PaymentDetails;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ChannelcountController  extends  AdminController {
   /* public $vvars = [
        'menu' => 'channelcount',
        'nav' =>'channelcount',
    ];*/
    public function init(){
        $this->vvars['menu'] = 'channelcount';
        $this->vvars['nav'] = 'channelcount';
    }

    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * 出款统计
     * @return string
     */
    public function actionList()
    {
        //回款通道
        $return_channel = $this->returnChannel();

        //筛选条件
        $getData = $this->get();
        $filter_where = [
            'return_channel'    => ArrayHelper::getValue($getData, 'return_channel', ''),//回款通道
            'series'            => ArrayHelper::getValue($getData, 'series', ''),//通道商编号
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d", time())),
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d", time())),
            'source'            => 1,//1:体外放款，2：体外回款
        ];
        $oPaymentDetails = new PaymentDetails();
        $pages = new Pagination([
            'totalCount' => $oPaymentDetails->countPaymentData($filter_where),
            'pageSize' => self::PAGE_SIZE,
        ]);

        $getAllData = $oPaymentDetails->getAllData($pages, $filter_where);

        $return_data = [];
        if (!empty($getAllData)){
            foreach($getAllData as $value){
                $return_data[] = $this->formatAllData($value);
            }
        }

        //总笔数
        $success_total = $oPaymentDetails->getSectionTotal($filter_where);
        //成功总手续费
        $success_fee = $oPaymentDetails->getSectionFee($filter_where);
        //成功金额
        $success_amount = $oPaymentDetails->getSectionAmount($filter_where);
        //差错总笔数
        $fial_total = $oPaymentDetails->getSectionFailTotal($filter_where);
        // 差错总金额
        $fial_amount = $oPaymentDetails->getSectionFailAmount($filter_where);
        // 差错账手续费
        $fial_fee = $oPaymentDetails->getSectionFailFee($filter_where);

        return $this->render('list', [
            'return_channel'        => $return_channel,
            'start_time'            => ArrayHelper::getValue($filter_where, 'start_time'),
            'end_time'              => ArrayHelper::getValue($filter_where, 'end_time'),
            'return_data'           => $return_data,
            'pages'                 => $pages,
            'return_channel_id'     => ArrayHelper::getValue($filter_where, 'return_channel'),
            'series'                => ArrayHelper::getValue($filter_where, 'series', ''),
            'success_total'         => $success_total,
            'success_fee'           => $success_fee,
            'fial_total'            => $fial_total,
            'fial_fee'              => $fial_fee,
            'fial_amount'           => $fial_amount,
            'success_amount'        => $success_amount,
        ]);
    }

    private function formatAllData($data_set)
    {
        if (empty($data_set)){
            return false;
        }

        //回款通道
        $return_channel = $this->returnChannel();
        $channel_id = ArrayHelper::getValue($data_set, 'channel_id', 0);
        $payment_date = ArrayHelper::getValue($data_set, 'payment_date', 0);
        //var_dump($payment_date);die;
        $oPaymentDetails = new PaymentDetails();
        $return_data = [];
        //回款通道
        $return_data['return_channel'] = ArrayHelper::getValue($return_channel, ArrayHelper::getValue($data_set, 'return_channel')).'+'.ArrayHelper::getValue($data_set, 'channel_id');
        //成功总笔数
        $success_total = $oPaymentDetails->getSuccessTotal($channel_id, $payment_date);
        $return_data['success_total'] = $success_total;
        //成功总金额
        $success_money = $oPaymentDetails->getSuccessMoney($channel_id, $payment_date);
        $return_data['success_money'] = $success_money;
        //成功总手续
        $success_fee = $oPaymentDetails->getSuccessFee($channel_id, $payment_date);
        $return_data['success_fee'] = $success_fee;
        //差错账总笔数
        $error_total = $oPaymentDetails->getErrorTotal($channel_id, $payment_date);
        $return_data['error_total'] = $error_total;
        //差错账总金额
        $error_money = $oPaymentDetails->getErrorMoney($channel_id, $payment_date);
        $return_data['error_money'] = $error_money;
        //差错账总手续费
        $error_fee = $oPaymentDetails->getErrorFee($channel_id, $payment_date);
        $return_data['error_fee'] = $error_fee;
        //账单日期
        $return_data['payment_date'] = ArrayHelper::getValue($data_set, 'payment_date', '');
        //创建时间
        $return_data['create_time'] = ArrayHelper::getValue($data_set, 'create_time', '');

        $return_data['channel_id'] = ArrayHelper::getValue($data_set, 'channel_id', '');

        return $return_data;
    }

    public function actionPaymentdown()
    {
        $getData = $this->get();
        $oPaymentDetails = new PaymentDetails();
        $channel_id = ArrayHelper::getValue($getData, 'channel_id', 0);
        $payment_date = ArrayHelper::getValue($getData, 'payment_date', 0);
        $res = $oPaymentDetails->downStatisticsData($channel_id, $payment_date);
        $this->downlist_xls_channel($res);
        return json_encode(["msg"=>"success"]);
    }
}
