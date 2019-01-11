<?php
/**
 * 逾期已收统计
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 9:53
 */
namespace app\modules\balance\controllers;



use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use app\modules\balance\common\PaymentCommon;
use app\modules\balance\common\SplitCommon;
use app\modules\balance\models\SplitBusiness;
use app\modules\balance\common\COverdue;
use app\models\yyy\YiRenewalPrecord;



class AccountController extends  AdminController
{

   public function init(){
       $this->vvars['menu'] = 'account';
       $this->vvars['nav'] = 'account';
   }

    public function actionIndex()
    {
        return $this->render('index');
    }

    //日统计
    public function actionList()
    {
        $return_channel = $this->returnChannel();
        $get  = $this -> get();
        #$startday=(date("Y-m-d",mktime(0,0,0,date("m")-1,1,date("Y"))));  //上个月月初
        #$endday = (date("Y-m-d",mktime(23,59,59,date("m") ,0,date("Y"))));  //上个月月尾
        $startday = date("Y-m-d",time()-2592000);
        $endday =  date("Y-m-d",time());
        $getdata = [
            'mechart_num'    => trim(ArrayHelper::getValue($get, 'mechart_num')),
            'start_time'      => trim(ArrayHelper::getValue($get, 'repay_time',$startday)),
            'end_time'        => trim(ArrayHelper::getValue($get, 'end_time',$endday)),
        ];
        $old_mechart_num = $getdata['mechart_num'];
        $oPaymentCommon = new PaymentCommon();
        $mechart_num = $oPaymentCommon->handleBE(ArrayHelper::getValue($getdata,'mechart_num'));
        $getdata['mechart_num'] = $mechart_num;

        $oSplitBusiness = new SplitBusiness();

        $total = $oSplitBusiness->getTotal($getdata);

        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => 30,
        ]);

        $resultAllData = $oSplitBusiness->getAllData($getdata,$pages);

        $total_service = $oSplitBusiness->getTotalServiceCharge($getdata);
        $total_money = $oSplitBusiness->getTotalMoney($getdata);
        return $this->render('list',[
            'mechart_num'       =>$old_mechart_num,
            'start_time'            =>ArrayHelper::getValue($getdata, 'start_time'),
            'end_time'           =>ArrayHelper::getValue($getdata, 'end_time'),
            'total_service'      => $total_service,
            'total_money'      => $total_money,
            'resultAllData'      => $resultAllData,
            'total'              =>$total,
            'pages'              => $pages,
            'return_channel'     =>$return_channel
        ]);

        //var_dump($filter_where['mobile']);die;
       // return $this->render('list');
    }

    //日统计明细
    public function actionDetailed()
    {
        $get  = $this -> get();
        $getdata = [
            'mechart_num'    => trim(ArrayHelper::getValue($get, 'mechart_num')),
            'bill_time'      => trim(ArrayHelper::getValue($get, 'bill_time')),
        ];
        if(empty($getdata['mechart_num']) || empty($getdata['bill_time'])){
            $resultAllData = '';
            return $this->render('list',[
                'resultAllData'      => $resultAllData,
            ]);
        }
        $oSplitBusiness = new SplitBusiness();

        $xhh_data = $oSplitBusiness->getXHHData($getdata);  //先花花
        $xxdd_data = $oSplitBusiness->getXXDDData($getdata); //小小黛朵
        $zrys_data = $oSplitBusiness->getZRYSData($getdata); //智融钥匙
        //总金额
        $total_money = 0;
        $total_money = $total_money+$xhh_data['money']+$xxdd_data['money']+$zrys_data['money'];
        //总手续费
        $total_service = 0;
        $total_service = $total_service+$xhh_data['service']+$xxdd_data['service']+$zrys_data['service'];

        return $this->render('detailed',[
            'mechart_num'            =>ArrayHelper::getValue($getdata, 'mechart_num'),
            'bill_time'           =>ArrayHelper::getValue($getdata, 'bill_time'),
            'xxdd_data'      => $xxdd_data,
            'zrys_data'      => $zrys_data,
            'xhh_data'      => $xhh_data,
            'total_money'    =>$total_money,
            'total_service'   => $total_service
        ]);

    }

    /**
     * 账目月统计控制器
     * @return string
     */
    public function actionMouth()
    {
        $return_channel = $this->returnChannel();
        $main_body = $this->main_body();
        $get  = $this -> get();
        #$startday=(date("Y-m-d",mktime(0,0,0,date("m")-1,1,date("Y"))));  //上个月月初
        #$endday = (date("Y-m-d",mktime(23,59,59,date("m") ,0,date("Y"))));  //上个月月尾
        $startday = date("Y-m-d",time()-2592000);
        $endday =  date("Y-m-d",time());
        $getdata = [
            'mechart_num'    => trim(ArrayHelper::getValue($get, 'mechart_num')),
            'fund_party'     =>trim(ArrayHelper::getValue($get, 'main_body','')),
            'start_time'      => trim(ArrayHelper::getValue($get, 'repay_time',$startday)),
            'end_time'        => trim(ArrayHelper::getValue($get, 'end_time',$endday)),
            'return_channel'      => trim(ArrayHelper::getValue($get, 'return_channel')),
        ];
        $old_mechart_num = $getdata['mechart_num'];
        $oPaymentCommon = new PaymentCommon();
        $mechart_num = $oPaymentCommon->handleBE(ArrayHelper::getValue($getdata,'mechart_num'));
        $getdata['mechart_num'] = $mechart_num;
        $oSplitBusiness = new SplitBusiness();
        $total = $oSplitBusiness->getTotalMouth($getdata);
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => 100,
        ]);

        $resultAllData = $oSplitBusiness->mouthAllData($getdata,$pages);

        //获取时间内的 展期数据
        $oYRP = new YiRenewalPrecord();
        $renewalList = $oYRP->getAlldatas($getdata);
        //关联公司主体
        $oSplitCommon = new SplitCommon();
        foreach($renewalList as $k=>$v){
            $renewalList[$k]['party']=$oSplitCommon->realFundParty($v['fund']);
        }
        $total_service = $oSplitBusiness->getTotalServiceCharge($getdata);//手续费总额
        $total_money = $oSplitBusiness->getTotalMoney($getdata);//总金额
        $zrys_service = $oSplitBusiness->getTotalServiceCharge($getdata,3);//智融钥匙手续费总额
        $zrys_money = $oSplitBusiness->getTotalMoney($getdata,3);//智融钥匙总金额
        $split_interest = $oSplitBusiness->split_interest($getdata);//利息总额
        $split_fine = $oSplitBusiness->split_fine($getdata);//罚息总额
        $split_principal = $oSplitBusiness->split_principal($getdata);//本金总金额

        $renewal_money = $oYRP->getTotalMoney($getdata);//展期总金额

        return $this->render('mouth',[
            'return_channel_id'  =>ArrayHelper::getValue($getdata, 'return_channel'),
            'mechart_num'        =>$old_mechart_num,
            'start_time'         =>ArrayHelper::getValue($getdata, 'start_time'),
            'end_time'           =>ArrayHelper::getValue($getdata, 'end_time'),
            'total_service'      => $total_service,
            'total_money'        => $total_money,
            'split_interest'     => $split_interest,
            'split_fine'         => $split_fine,
            'split_principal'    => $split_principal,
            'zrys_service'       => $zrys_service,
            'zrys_money'         => $zrys_money,
            'resultAllData'      => $resultAllData,
            'total'              =>$total,
            'pages'              => $pages,
            'return_channel'     =>$return_channel,
            'main_body'          =>$main_body,
            'renewalList'          =>$renewalList,
            'renewal_money'          =>$renewal_money,
            'main_body_id'       =>((empty(ArrayHelper::getValue($get, 'main_body')) && ArrayHelper::getValue($get, 'main_body')!=0)?'999999':ArrayHelper::getValue($get, 'main_body'))      //因为智融钥匙也是0，加个判断
        ]);

        //var_dump($filter_where['mobile']);die;
        // return $this->render('list');
    }


    public function actionDowndata()
    {
        $getData = $this->get();
        $condition = [
            'fund_party'           => ArrayHelper::getValue($getData, 'main_body'), //公司主体
            'mechart_num'          => ArrayHelper::getValue($getData, 'mechart_num'), //商编
            'start_time'           => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")), //账单日期
            'end_time'             => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d")), //账单日期
        ];
        $old_mechart_num = $condition['mechart_num'];
        $oPaymentCommon = new PaymentCommon();
        $mechart_num = $oPaymentCommon->handleBE(ArrayHelper::getValue($condition,'mechart_num'));
        $condition['mechart_num'] = $mechart_num;
        $oOverdueLoan = new SplitBusiness();
        $data = $oOverdueLoan->accountWhere($condition);
        //获取时间内的 展期数据
        $oYRP = new YiRenewalPrecord();
        $renewalList = $oYRP->getAlldatas($condition);
        //关联公司主体
        $oSplitCommon = new SplitCommon();
        foreach($renewalList as $k=>$v){
            $renewalList[$k]['party']=$oSplitCommon->realFundParty($v['fund']);
        }
        $this->downlist_xls($data,$renewalList);
        return json_encode(['msg'=>json_encode($getData)]);
    }

    /**
     * 下载成功对账成功数据
     * @param $orderData
     * @throws \Exception
     */
    protected function downlist_xls($orderData,$renewalList) {
        $oCOverdue = new COverdue();
        $icount = count($orderData);
        // 创建一个处理对象实例
        $objExcel = new \PHPExcel();
        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        for($a = 0; $a <= 13; $a ++){
            $chr_asc = 65 + $a;
            $objActSheet->getColumnDimension(chr($chr_asc))->setWidth(30);
        }
        $objActSheet->setCellValue('A1', '类型');
        $objActSheet->setCellValue('B1', '主体名称');
        $objActSheet->setCellValue('C1', '通道商编');
        $objActSheet->setCellValue('D1', '通道名称');
        $objActSheet->setCellValue('E1', '本金');
        $objActSheet->setCellValue('F1', '利息');
        $objActSheet->setCellValue('G1', '滞纳金	');
        $objActSheet->setCellValue('H1', '展期服务费');
        $objActSheet->setCellValue('I1', '减免金额');
        $objActSheet->setCellValue('J1', '手续费');
        $objActSheet->setCellValue('K1', '总金额（手续费除外）');

        //展期
        $objActSheet->setCellValue('M1', '展期-类型');
        $objActSheet->setCellValue('N1', '展期-公司主体');
        $objActSheet->setCellValue('O1', '展期-总金额');
//        $num = 0;
        for ($i = 0; $i < $icount; $i++) {
//            $num ++;
            $data_set = $orderData[$i];
            $main_body = $this->main_body();
            $party = ArrayHelper::getValue($main_body, ArrayHelper::getValue($data_set, 'party'),'');//公司主体
            if(empty($party)){
                $party = "智融钥匙";
            }else{
                $party = $main_body[ArrayHelper::getValue($data_set, 'party')];//公司主体
            }
            $return_channel = $this->returnChannel();
            $channel_name = ArrayHelper::getValue($return_channel, ArrayHelper::getValue($data_set, 'return_channel'),'');//通道名称
            if(empty($channel_name)){
                $channel_name = "未知";
            }else{
                $channel_name = $return_channel[ArrayHelper::getValue($data_set, 'return_channel')];//通道名称
            }
            //时间计算
            $objActSheet->setCellValue('A' . ( $i + 2), ArrayHelper::getValue($data_set, 'days',0));//类型
            $objActSheet->setCellValue('B' . ( $i + 2), $party);//公司主体
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($data_set, 'mechart_num')); //通道商编
            $objActSheet->setCellValue('D' . ( $i + 2), $channel_name);//通道名称
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($data_set, 'principal'));//本金
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($data_set, 'interest'));//利息
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($data_set, 'fine'));//滞纳金
            $objActSheet->setCellValue('H' . ( $i + 2), 0);//展期服务费
            $objActSheet->setCellValue('I' . ( $i + 2), 0);//减免金额
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($data_set, 'charge'));//手续费
            $objActSheet->setCellValue('K' . ( $i + 2), ArrayHelper::getValue($data_set, 'money'));//手续费

        }

        $icounts = count($renewalList);
        for ($i = 0; $i < $icounts; $i++) {
//            $num ++;
            $data_set = $renewalList[$i];
            $main_body = $this->main_body();
            $party = ArrayHelper::getValue($main_body, ArrayHelper::getValue($data_set, 'party'),'');//公司主体
            if(empty($party)){
                $party = "智融钥匙";
            }else{
                $party = $main_body[ArrayHelper::getValue($data_set, 'party')];//公司主体
            }
            //时间计算
            $objActSheet->setCellValue('M' . ( $i + 2), ArrayHelper::getValue($data_set, 'days','0'));//类型
            $objActSheet->setCellValue('N' . ( $i + 2), $party); //公司主体
            $objActSheet->setCellValue('O' . ( $i + 2), ArrayHelper::getValue($data_set, 'total_money','0'));//金额
        }
        $outputFileName = date('Y-m-d', time())  . "账目月账单统计" . ".xls";
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



}