<?php

namespace app\modules\settlement\controllers;

use app\common\Common;
use app\models\App;
use app\models\bill\BillDetails;
use app\models\bill\ChannelBills;
use Yii;
use yii\data\Pagination;
class BillController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $pay_chan = Yii::$app->request->get('pay_chan');
        $search_data = $this->get();
        if (empty($search_data['search_date'])) {
            $bill_number = date("Ymd", strtotime("-1 day"));
        }else{
            $bill_number = date("Ymd", strtotime($search_data['search_date']));
        }
        $search_date = date("Y-m-d", strtotime($bill_number));
        //通道
        $channel_data = $this->channelData();

        //获取数据
        $channel_bills_object = new ChannelBills();
        $channel_bills_data = $channel_bills_object->getSectionData($bill_number);
        if (!empty($channel_bills_data)){
            foreach($channel_bills_data as $value){
                if (!empty($channel_data[$value->channel_id])){
                    $channel_data[$value->channel_id] = ['channel_name'=>$channel_data[$value->channel_id], 'data'=>$value];
                }
            }
        }
        return $this->render('index', [
            'channel_data' => $channel_data,
            'search_date' => $search_date,
        ]);
    }

    public function actionAdd() {
        $channel_type = $this->get();
        $channel_data = $this->channelData();
        if ($this->isPost()){
            $post_data = $this->post();
            $file_data = $_FILES;
            if (empty($file_data)){
                return $this->returnFileJson("上传文件不能为空！");
            }
            $to_path = Yii::$app->basePath.'/web/upload/bill/'; //上传文件的目标路径
            //文件上传
            $file_info = Common::Uploadfun($file_data['file_name'], $to_path, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']); //调用单文件上传函数

            if (strpos($file_info, 'xls') > 0){
                //记录数据表
                $channel_bill_object = new ChannelBills();
                $channel_time_data = $channel_bill_object->getChannelTimeData($post_data['channel_date'], $post_data['channel_type']);
                if (empty($channel_time_data)){
                    $channel_bill_data = [
                        'channel_id' =>$post_data['channel_type'],
                        'channel_file' =>$to_path.$file_info,
                        'bill_number'=> date("Ymd", strtotime($post_data['channel_date'])),
                    ];
                    $bill_state = $channel_bill_object->saveChannelData($channel_bill_data);
                    if (!$bill_state){
                        return $this->returnFileJson("添加记录失败");
                    }

                    return $this->returnFileJson("上传成功");
                }
                return $this->returnFileJson("重复记录不更新");
            }
            return $this->returnFileJson($file_info);
        }
        if (empty($channel_type['channel_type']) || empty($channel_data[$channel_type['channel_type']]) || empty($channel_type['channel_date'])){
            $this->redirect('index');
        }

        return $this->render('add', [
                'channel_name' => $channel_data[$channel_type['channel_type']],
                'channel_type' => $channel_type['channel_type'],
                'channel_date' => date("Ymd", strtotime($channel_type['channel_date'])),
            ]);

    }

    public function actionChannellist(){
        $get_data = $this->get();
        if (empty($get_data['channel_date']) || empty($get_data['channel_type'])){
            $this->redirect('index');
        }
        $channel_date = date('Ymd', strtotime($get_data['channel_date']));

        $bill_details_object = new BillDetails();
        //交易总笔数
        $total_count = $bill_details_object->totalPenCount($get_data['channel_type'],$channel_date);
        // 交易总金额
        $total_money = $bill_details_object->totalMoney($get_data['channel_type'],$channel_date);
        //结算手续费
        $total_settle_fee = $bill_details_object->totalSettleFee($get_data['channel_type'],$channel_date);
        $pages = new Pagination([
            'totalCount' => $total_count,
            'pageSize'   => '30'
        ]);
        $res   = $bill_details_object->billListData($get_data['channel_type'],$channel_date, $pages);

        return $this->render('channellist', [
            'res'   => $res,
            'pages' => $pages,
            'channel_name_data' => $this->channelData(),
            'channel_type' => $get_data['channel_type'],
            'total_count' => $total_count,
            'channel_date' => $get_data['channel_date'],
            'total_money' => $total_money,
            'total_settle_fee' => $total_settle_fee,
        ]);

    }

    public function actionDownmodule()
    {
        $this->downlist_model_xls();
    }
}
