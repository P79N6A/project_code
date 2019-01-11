<?php

namespace app\modules\settlement\controllers;

use app\common\Common;
use app\models\App;
use app\models\bill\BillDetails;
use app\models\bill\ChannelBills;
use app\models\bill\ComparativeBill;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ChannelcountController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    /**
     * 出款统计
     * @return string
     */
    public function actionList()
    {
        $getData = $this->get();
        $start_time = date("Y-m-01", time());
        $end_time = date("Y-m-d", time());
        if (!empty($getData['start_time'])){
            $start_time = $getData['start_time'];
        }
        if (!empty($getData['end_time'])){
            $end_time = $getData['end_time'];
        }
        $oComparativeBill = new ComparativeBill();
        //分页调用
        $pages = new Pagination([
            'totalCount' => $oComparativeBill->getDateGroupCount($start_time, $end_time),
            'pageSize'   => '20'
        ]);
        //分组数据
        $res = $oComparativeBill->getDateGroupDatas($pages, $start_time, $end_time);
        $result = [];
        if (!empty($res)){
            foreach($res as $value){
                $result[] = $this->getFormatData($value);
            }
        }
        //总笔数
        $total = $oComparativeBill->getSectionTotal($start_time, $end_time);
        //总金额
        $total_money = $oComparativeBill->getSectionMoney($start_time, $end_time);
        //总手续费
        $total_fee = $oComparativeBill->getSectionFee($start_time, $end_time);
        //差错账总笔数
        $total_bill_error = $oComparativeBill->getSectionBillError($start_time, $end_time);

        return $this->render('list', [
            'result'            => $result,
            'total'             => $total,
            'total_money'       => $total_money,
            'total_fee'         => $total_fee,
            'total_bill_error'  => $total_bill_error,
            'start_time'        => $start_time,
            'end_time'          => $end_time,
            'pages'             => $pages,
        ]);
    }

    private function getFormatData($data_set)
    {
        $oComparativeBill = new ComparativeBill();
        //总笔数
        $total = $oComparativeBill -> getBillNumberCount($data_set->bill_number);
        //总金额
        $total_money = $oComparativeBill ->getBillNumberMoney($data_set->bill_number);
        //总手续费
        $total_fee = $oComparativeBill->getBillNumberFee($data_set->bill_number);
        //差错账总笔数
        $total_error = $oComparativeBill->getBillNumberError($data_set->bill_number);

        $format_data = [
                'bill_number'       => $data_set->bill_number, // 账单日期
                'total_num'         => $total, //总笔数
                'total_money'       => $total_money, //总金额/元
                'total_fee'         => $total_fee, //总手续费/元
                'total_error_bill'  => $total_error, //差错账笔数
                'create_time'       => $data_set->create_time,
        ];
        return $format_data;
    }

    /**
     * 按账单日志查看通道数据
     * @return string
     */
    public function actionDatelist()
    {
        $getData = $this->get();
        if (empty($getData['bill_number'])){
            $this->redirect("/settlement/channelcount/list");
        }
        $channel_id = ArrayHelper::getValue($getData, 'channel_name', '');
        $client_number = ArrayHelper::getValue($getData, 'client_number', '');
        //获取数据
        $oComparativeBill = new ComparativeBill();
        //分页调用
        $pages = new Pagination([
            'totalCount' => $oComparativeBill->getBillNumberTotal($getData['bill_number'], $channel_id, $client_number),
            'pageSize'   => '20'
        ]);
        $res = $oComparativeBill->getBillNumberData($pages, $getData['bill_number'], $channel_id, $client_number);
        $result = [];
        if (!empty($res)){
            foreach($res as $value){
                $result[] = $this->formatDateList($value);
            }
        }
        //总笔数
        $total = $oComparativeBill -> getChannelSearchCount($getData['bill_number'], $channel_id, $client_number);
        //总金额
        $total_money = $oComparativeBill->getChannelSearchMoney($getData['bill_number'], $channel_id, $client_number);
        //总手续费
        $total_fee = $oComparativeBill->getChannelSearchFee($getData['bill_number'], $channel_id, $client_number);
        //差错账总笔数
        $total_bill_error = $oComparativeBill->getChannelSearchBillError($getData['bill_number'], $channel_id, $client_number);
        return $this->render('datelist',
            [
                'result'            => $result,
                'passageOfMoney'    => $this->passageOfMoney(),
                'total'             => $total,
                'total_money'       => $total_money,
                'total_fee'         => $total_fee,
                'total_bill_error'  => $total_bill_error,
                'bill_number'       => $getData['bill_number'],
                'channel_name'      => $channel_id,
                'client_number'     => $client_number,
                'pages'             => $pages,
            ]);
    }

    private function formatDateList($data_set)
    {
        $oComparativeBill = new ComparativeBill();
        //出款通道名称
        $channel_name = ArrayHelper::getValue($this->passageOfMoney(), $data_set->channel_id, '');
        //总笔数
        $total = $oComparativeBill->getChannelBillCount($data_set->bill_number, $data_set->channel_id);
        //总金额/元
        $total_money = $oComparativeBill->getChannelBillMoney($data_set->bill_number, $data_set->channel_id);
        //总手续费/元
        $total_fee = $oComparativeBill->getChannelBillFee($data_set->bill_number, $data_set->channel_id);
        //差错账笔数
        $total_bill_error = $oComparativeBill->getChannelBillError($data_set->bill_number, $data_set->channel_id);
        //账单日期
        $bill_number = $data_set->bill_number;
        //创建时间
        $create_time = $data_set->create_time;
        //通道分类
        $channel_num = $oComparativeBill->getChannelName($data_set->bill_number, $data_set->channel_id);
        $format_data = [
            'channel_id'        => $data_set->channel_id,
            'channel_name'      => $channel_name,
            'total'             => $total,
            'total_money'       => $total_money,
            'total_fee'         => $total_fee,
            'total_bill_error'  => $total_bill_error,
            'bill_number'       => date("Y-m-d", strtotime($bill_number)),
            'create_time'       => $create_time,
            'channel_num'       => $channel_num,
        ];
        return $format_data;
    }

    public function actionChannellist()
    {
        $postData = $this->post();
        if (empty($postData)){
            return json_encode(['data'=>'暂时数据']);
        }

        $oComparativeBill = new ComparativeBill();
        $bill_number = ArrayHelper::getValue($postData, 'bill_number', '');
        //$bill_number = "2017-11-13 00:00:00";
        $channel_id = ArrayHelper::getValue($postData, 'channel_id', '');
        //$channel_id = 2;
        $res = $oComparativeBill->getChannelChildData($bill_number, $channel_id);

        $result = [];
        if (!empty($res)){
            foreach($res as $value){
                $result[] = $this->childFormatData($value);
            }
        }
        return json_encode(['data'=>$result]);
    }
    private function childFormatData($data_set)
    {
        $oComparativeBill = new ComparativeBill();
        $bill_number = ArrayHelper::getValue($data_set, 'bill_number', '');
        $channel_id = ArrayHelper::getValue($data_set, 'channel_id', '');
        $child_channel_id = ArrayHelper::getValue($data_set, 'child_channel_id', '');
        //出款通道名称
        $channel_name = ArrayHelper::getValue($this->passageOfMoney(), $data_set->channel_id, '');
        //总笔数
        $total = $oComparativeBill->getChannelChildTotal($bill_number, $channel_id, $child_channel_id);
        //总金额/元
        $total_money = $oComparativeBill->getChannelChildMoney($bill_number, $channel_id, $child_channel_id);
        //总手续费/元
        $total_fee = $oComparativeBill->getChannelChildFee($bill_number, $channel_id, $child_channel_id);
        //差错账笔数
        $total_bill_error = $oComparativeBill->getChannelChildError($bill_number, $channel_id, $child_channel_id);
        
        return [
            'channel_name'      => $channel_name,
            'child_channel_id'  => $child_channel_id,
            'total'             => $total,
            'total_money'       => $total_money,
            'total_fee'         => $total_fee,
            'total_bill_error'  => $total_bill_error,
            'bill_number'       => date("Y-m-d", strtotime(ArrayHelper::getValue($data_set, 'bill_number', ''))),
            'create_time'       => ArrayHelper::getValue($data_set, 'create_time', ''),
        ];
    }
}
