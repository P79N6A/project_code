<?php

namespace app\modules\balance\controllers;
use app\modules\balance\models\PaymentDetails;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class PaidcountController  extends  AdminController {
   /* public $vvars = [
        'menu' => 'channelcount',
        'nav' =>'channelcount',
    ];*/
    public function init(){
        $this->vvars['menu'] = 'paid';
        $this->vvars['nav'] = 'paid';
    }

    public function actionIndex()
    {
        return $this->render('index');
    }


    /**
     * 回款统计
     * @return string
     */
    public function actionList()
    {
        //回款通道
        $return_channel = $this->returnChannel();
        $aid = $this->getAid();
        //筛选条件
        $getData = $this->get();
        $state   = $this->state();
        $filter_where = [
            'return_channel'    => ArrayHelper::getValue($getData, 'return_channel', ''),//回款通道
            'aid'               => ArrayHelper::getValue($getData, 'aid', ''),//产品名称
            'series'            => ArrayHelper::getValue($getData, 'series', ''),//通道商编号
            'order_id'          => ArrayHelper::getValue($getData, 'order_id', ''),//商户订单号
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d", time())),
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d", time())),
            'create_time'        => ArrayHelper::getValue($getData, 'create_time'),
            'create_times'       => ArrayHelper::getValue($getData, 'create_times'),
            'source'            => 2,//1:体外放款，2：体外回款
            'state_id'          => ArrayHelper::getValue($getData, 'state_id', ''),
        ];
        $oPaymentDetails = new PaymentDetails();
        $pages = new Pagination([
            'totalCount' => $oPaymentDetails->countData($filter_where),
            'pageSize' => self::PAGE_SIZE,
        ]);

        $getAllData = $oPaymentDetails->getData($pages, $filter_where);
        $return_data = [];
        if (!empty($getAllData)){
            foreach($getAllData as $value){
                $return_data[] = $this->formatAllDatas($value);
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
            'aid'                   =>  $aid,
            'state'                 => $state,
            'state_id'              => ArrayHelper::getValue($filter_where, 'state_id'),
            'name_aid'              => ArrayHelper::getValue($filter_where, 'aid'),
            'start_time'            => ArrayHelper::getValue($filter_where, 'start_time'),
            'order_id'              => ArrayHelper::getValue($filter_where, 'order_id'),
            'end_time'              => ArrayHelper::getValue($filter_where, 'end_time'),
            'create_time'            => ArrayHelper::getValue($filter_where, 'create_time'),
            'create_times'           => ArrayHelper::getValue($filter_where, 'create_times'),
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

    /**
    * 回款统计
    *
    */
    private function formatAllDatas($data_set){

        if (empty($data_set)){
            return false;
        }
        //回款通道
        $return_channel = $this->returnChannel();
        $getAid = $this->getAid();
        $type = ArrayHelper::getValue($data_set, 'type');
        if($type==1){
            $erroyType = '对账成功';
        }else{
            $erroyType = '对账失败';
        }
        $oPaymentDetails = new PaymentDetails();
        $return_data = [];
        $return_data['client_id'] = ArrayHelper::getValue($data_set, 'client_id');
        $return_data['return_channel'] =  ArrayHelper::getValue($return_channel, ArrayHelper::getValue($data_set, 'return_channel'));
        $return_data['channel_id'] = ArrayHelper::getValue($data_set, 'channel_id').'-'. ArrayHelper::getValue($data_set, 'series');
        $return_data['aid'] =  ArrayHelper::getValue($getAid, ArrayHelper::getValue($data_set, 'aid'));
        $return_data['amount'] = ArrayHelper::getValue($data_set, 'amount');
        $return_data['settle_fee'] = ArrayHelper::getValue($data_set, 'settle_fee');
        $return_data['create_time'] = ArrayHelper::getValue($data_set, 'create_time', '');
        $return_data['modify_time'] = ArrayHelper::getValue($data_set, 'modify_time', '');
        $return_data['collection_time'] = ArrayHelper::getValue($data_set, 'collection_time', '');
        $return_data['payment_date'] = ArrayHelper::getValue($data_set, 'payment_date', '');
        $return_data['type'] =$erroyType;
        return $return_data;
    }

    /**
     * 放款统计
     *
     */
    private function formatAllData($data_set)
    {
        if (empty($data_set)){
            return false;
        }

        //回款通道
        $return_channel = $this->returnChannel();
        $channel_id = ArrayHelper::getValue($data_set, 'channel_id', 0);
        $payment_date = ArrayHelper::getValue($data_set, 'payment_date', 0);
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
        $res = $oPaymentDetails->downStatisticsDatas($channel_id, $payment_date);
        $this->downlist_xls_channel($res);
        return json_encode(["msg"=>"success"]);
    }

    public function actionDowndata(){

        $getData = $this->get();
        $filter_where = [
            'return_channel'    => ArrayHelper::getValue($getData, 'return_channel', ''),//回款通道
            'aid'               => ArrayHelper::getValue($getData, 'aid', ''),//产品名称
            'series'            => ArrayHelper::getValue($getData, 'series', ''),//通道商编号
            'order_id'          => ArrayHelper::getValue($getData, 'order_id', ''),//商户订单号
            'start_time'        => ArrayHelper::getValue($getData, 'start_time'),
            'end_time'          => ArrayHelper::getValue($getData, 'end_time'),
            'create_time'        => ArrayHelper::getValue($getData, 'create_time'),
            'create_times'       => ArrayHelper::getValue($getData, 'create_times'),
            'source'            => 2,//1:体外放款，2：体外回款
        ];
        $oPaymentDetails = new PaymentDetails();
        $getAllData = $oPaymentDetails->getDatas($filter_where);
        $this->downlist_xls($getAllData);
        return json_encode(['msg'=>json_encode($getData)]);
    }

    protected function downlist_xls($orderData) {

        $icount = count($orderData);
        // 创建一个处理对象实例
        $objExcel = new \PHPExcel();
        $return_channel = $this->returnChannel();
        $aid = $this->getAid();
        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        for($a = 0; $a <= 15; $a ++){
            $chr_asc = 65 + $a;
            $objActSheet->getColumnDimension(chr($chr_asc))->setWidth(30);
        }

        $objActSheet->setCellValue('A1', '商户订单号');
        $objActSheet->setCellValue('B1', '通道名称');
        $objActSheet->setCellValue('C1', '通道ID');
        $objActSheet->setCellValue('D1', '产品名称');
        $objActSheet->setCellValue('E1', '金额');
        $objActSheet->setCellValue('F1', '手续费');
        $objActSheet->setCellValue('G1', '请求时间');
        $objActSheet->setCellValue('H1', '完成时间');
        $objActSheet->setCellValue('I1', '对账创建时间');
        $objActSheet->setCellValue('J1', '账单日期');
        $objActSheet->setCellValue('K1', '状态');
        $num = 0;

        //出款
       // $oUserLoan = new UserLoan();
        for ($i = 0; $i < $icount; $i++) {
            $num ++;
            $data_set = $orderData[$i];
            $channel = ArrayHelper::getValue($data_set, 'return_channel');
            $channel_id = ArrayHelper::getValue($data_set, 'channel_id').'-'.ArrayHelper::getValue($data_set, 'series');
            $aid_name = ArrayHelper::getValue($data_set, 'aid');
            $error_type = ArrayHelper::getValue($data_set, 'type');
            if($error_type==1){
                $type = '对账成功';
            }else{
                $type = '对账失败';
            }
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($data_set, 'client_id'));//商户订单号
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($return_channel, $channel)); //通道名称
            $objActSheet->setCellValue('C' . ( $i + 2),$channel_id);//通道ID
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($aid, $aid_name));//产品名称
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($data_set, 'amount'));//金额
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($data_set, 'settle_fee'));//手续费
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($data_set,'create_time'));//请求时间
            $objActSheet->setCellValue('H' . ( $i + 2), ArrayHelper::getValue($data_set,'modify_time'));//完成时间
            $objActSheet->setCellValue('I' . ( $i + 2), ArrayHelper::getValue($data_set, 'create_time'));//对账创建时间
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'payment_date'));//账单日期
            $objActSheet->setCellValue('K' . ( $i + 2),$type);//对账状态

        }
        $outputFileName = date('Y-m-d', time())  . "对账统计" . ".xls";
        //到文件
        //$objWriter->save($outputFileName);
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="' . $outputFileName . '"');
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    /**
     * 差错帐处理
     * @
     **/

    public function actionDetails(){

        $getData = $this->get();
        $client_id =ArrayHelper::getValue($getData, 'client_id'); //差错帐订单
        $oPaymentDetails = new PaymentDetails();
        $order = $oPaymentDetails->getOrderId($client_id);
        //回款通道
        $return_channel = $this->returnChannel();
        //差错类型
        $errorStatus = $this->errorStatus();
        //审核状态
        $auditingStatus = $this->auditingStatus();

        return $this->render('dateils', [
            'result'            => $order,
            'return_channel'    => $return_channel,
            'errorStatus'       => $errorStatus,
            'auditingStatus'    => $auditingStatus,
        ]);

    }

    public function actionUpdatebill()
    {

        if ($this->isPost()) {
            $post_data = $this->post();
            //查找错误账单
            $oPaymentDetails = new PaymentDetails();
            $fail_bill_data = $oPaymentDetails->getDetails(ArrayHelper::getValue($post_data, 'id', 0));
            if (empty($fail_bill_data)){
                return $this->returnFileJson("订单不存在");
            }

            //修改账单
            $update_bill_data = [
                //'error_types' => "差错已处理", //差错类型',
                'loss'          => (int)ArrayHelper::getValue($post_data, 'loss', 2),
                'state'         => (int)ArrayHelper::getValue($post_data, 'state', 2),
                //'error_types'   => 1, //差错状态',
                'type'          => (int)PaymentDetails::TYPE_SUCCESS, //账单类型：1正常，2差错',
                'reason'        => (string)ArrayHelper::getValue($post_data, 'reason', ''), //原因',
                'uid'           => Yii::$app->admin->id,
            ];
            $state = $fail_bill_data->updateData($update_bill_data);

            if ($state){
                return $this->returnFileJson("对账成功");
            }else{
                return $this->returnFileJson("对账失败");
            }

        }else{
            $this->redirect('index');
        }

    }


    public function state(){

        return[
            '1'=> '对账成功',
            '2'=> '对账失败',
        ];
    }



}
