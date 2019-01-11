<?php
/**
 * 回款对账定时
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/5
 * Time: 17:37
 */
namespace app\commands;

use app\common\Logger;
use app\models\Payorder;
use app\models\open\CjRemit;
use app\models\open\Rbremit;
use app\modules\balance\common\PaymentCommon;
use app\modules\balance\models\PaymentDetails;
use app\modules\balance\models\UpPaymentFile;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\Channel;

//ini_set('error_reporting', E_ALL);//错误级别
set_time_limit(0);
ini_set('memory_limit', '-1');

if (!class_exists('PHPExcel')) {

    include Yii::$app->basePath.'/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/common/phpexcel/Excel5.php';
}

if (!class_exists('Spreadsheet_Excel_Reader')) {
    include Yii::$app->basePath.'/common/phpexcel/reader.php';
}
include Yii::$app->basePath.'/common/phpexcel/PHPExcel/IOFactory.php';

class PaymentController extends BaseController
{
    private $oPaymentCommon;

    public function runPayment()
    {

        //实例化公共类
        $this->oPaymentCommon = new PaymentCommon();

        $limit = 100;
        //实例文件类
        $oUpPaymentFile = new  UpPaymentFile();
        $getAllData = $oUpPaymentFile->getRunData($limit);//文件表 未处理数据状态为0
        if (empty($getAllData)){

            return false;
        }
        foreach($getAllData as $value){

            //锁定
            $lock = $oUpPaymentFile->lockFileStatus($value);//修改状态为1

           if (!$lock){
                return false;
            }
            //数据处理
            $num = $this->fileHandle($value);
            Logger::dayLog("paymentbill/run", '处理条数', $num);
            //成功
            $success = $oUpPaymentFile->successFileStatus($value);//成功修改状态为2
            if (!$success){
                return false;
            }
        }
        echo "success";
    }

    /**
     * 读取文件数据
     * @param $file_data
     * @return bool|int
     */
    private function fileHandle($file_data)
    {
        //1.判断文件是否存在
        $file_name = ArrayHelper::getValue($file_data, 'bill_file');
        if (!is_file($file_name)){
            return false;
        }
        //2.实例化excel类
        $php_ext = explode(".", $file_name);
        if (empty($php_ext[1])){
            Logger::dayLog("paymentbill/run", '文件后缀为空', $file_name);
            return false;
        }
        $objPHPExcel = $this->excelObject($php_ext[1], $file_name);
        if (!$objPHPExcel){
            Logger::dayLog("paymentbill/run", '创建类失败', json_encode([$php_ext[1], $file_name]));
            return false;
        }
        //读取数据
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数

        $num = 0;
        for($j=2;$j<=$highestRow;$j++) {
            $num ++;
            $str = '';
            for ($k = 'A'; $k <= $highestColumn; $k++) {
                $str .= $objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue() . '\\';//读取单元格
            }

            $payment_data = explode("\\", $str);//excel数据
            $state = $this->paymentLogic($file_data, $payment_data);//存储文件表数据  excel数据

            if (!$state){
                Logger::dayLog("paymentbill/run", '处理失败的数据', json_encode($payment_data));
            }
        }

        return $num;
    }

    /**
     * 返回excel类对象
     * @param $ext
     * @param $file_name
     * @return bool|\PHPExcel
     * @throws \PHPExcel_Reader_Exception
     */
    private function excelObject($ext, $file_name)
    {
        $objPHPExcel = false;
        if ($ext == 'xlsx'){
            $objReader= \PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($file_name,'utf-8');
        }
        if ($ext == 'xls'){
            $objReader= \PHPExcel_IOFactory::createReader('Excel5');
            $objPHPExcel = $objReader->load($file_name,'utf-8');
        }
        return $objPHPExcel;
    }

    /**
     * 数据处理
     * @param $file_data 文件表数据
     * @param $other_payment_data excel表数据
     * @return bool
     */
    private function paymentLogic($file_data, $other_payment_data)
    {
        $oPaymentDetails = new PaymentDetails();
        $type = ArrayHelper::getValue($file_data,'type');//1体外放款2体外回款
        if($type==1){
            $format_data = $this->formatData($file_data, $other_payment_data);
        }else{
            $format_data = $this->formatDatas($file_data, $other_payment_data);
        }

        if($format_data==false){
            return false;
        }
        //查找表中是否记录数据  如果记录就不在重复存入
        $get_payment_info = $oPaymentDetails->getOrderId(trim(ArrayHelper::getValue($other_payment_data, 0)));
        if(!empty($get_payment_info)){
            //$save = $oPaymentDetails->updateDatas($format_data);
            return true;
        }
        //保存数据
        $save_status = $oPaymentDetails->saveData($format_data);

        if (!$save_status){
            Logger::dayLog("paymentbill/save", "保存失败记录", json_encode($other_payment_data));
        }
        return true;

    }

    /**
     * 格式数据
     * @param $file_data 文件表数据
     * @param $other_payment_data excel表数据
     * @return array
     */
    private function formatData($file_data, $other_payment_data)
    {
        //商户订单号
        $client_id = trim(ArrayHelper::getValue($other_payment_data, 0));
        //查找订单是否存在
        $oPayorder = new Payorder();
        $order_info = $oPayorder->getOrderId($client_id);//订单表数据
        $busscode = ArrayHelper::getValue($order_info,'business_id',0);//获取订单表business_id
        $aid = ArrayHelper::getValue($order_info,'aid',0);//获取订单表aid
        if($busscode==25){
            $sort = 99;//展期
        }
        if($busscode==5){
            $sort = 98;//逾期
        }
        if($busscode==31){  //优惠券
            $sort = 100;//优惠卷
        }
        if($aid==10){  //智融钥匙103
            $sort = 103;
        }
        if($aid==1 && $busscode==30){ //七天乐展期101
            $sort = 101;
        }
        if($aid==1 && $busscode==26){  //七天乐快捷102
            $sort = 102;
        }
        if(!isset($sort)){
            $sort = 0;
        }
        //差错类型
        $getErrorTypes = $this->getErrorTypes($order_info, $other_payment_data);
        //订单状态
        $passageway_type = $this->getPassagewayType($order_info, $other_payment_data);

        //查询通道商编号
        $channel = new Channel();
        $result = $channel->find()->where(['id'=>ArrayHelper::getValue($order_info, 'channel_id', 0)] )->one();
        $mechart_num = $result['mechart_num'];

        //判断订单是否存在，不存在给通道商编号默认值 0
        $oPayorder = new Payorder();
        $order_info = $oPayorder->getOrderId($client_id);//订单表数据
        if(empty($order_info)){
            //订单不存在
            Logger::dayLog('paymentbill/client_id', '订单号不存在', $client_id);
            $mechart_num=0;
        }
        $source = ArrayHelper::getValue($file_data, 'type', 1);//1:体外放款，2：体外回款
        //$file_data  文件表数据     $other_payment_data Excel数据  $order_info总订单表 pay_payorder

        $save_data = [
            'client_id'             => ArrayHelper::getValue($other_payment_data, 0),//商户订单号
            'channel_id'            => ArrayHelper::getValue($order_info, 'channel_id', 0), //回款通道id
            'series'                => $mechart_num,//通道商编号
            'return_channel'        => ArrayHelper::getValue($file_data, 'channel_id', 0), //回款通道
            'name'                  => ArrayHelper::getValue($other_payment_data, 1, ''), //姓名
            'opening_bank'          => ArrayHelper::getValue($other_payment_data, 2, ''), //开户行
            'guest_account'         => ArrayHelper::getValue($other_payment_data, 3, ''), //银行卡号
            'identityid'            => ArrayHelper::getValue($order_info, 'idcard', ''), //证件号
            'user_mobile'           => ArrayHelper::getValue($order_info, 'phone', ''), //收款人手机号
            'amount'                => ArrayHelper::getValue($other_payment_data, 6, ''), //第三方交易金额
            'payment_amount'        => ArrayHelper::getValue($order_info, 'amount', 0) / 100, //平台金额
            'settle_fee'            => ArrayHelper::getValue($other_payment_data, 7, ''), //手续费
            'payment_date'          => $this->runTime(ArrayHelper::getValue($other_payment_data, 9, '')), //账单日期
            'create_time'           => date("Y-m-d H:i:s", time()), //创建时间
            'file_id'               => ArrayHelper::getValue($file_data, 'id', ''), //文件ID
            'auditing_status'       => 1, //审核状态:1待审核2审核通过3审核失败
            'modify_time'           => date("Y-m-d H:i:s", time()), //更新时间
            'reason'                => '', //原因
            'error_types'           => $getErrorTypes, //差错类型
            'loss'                  => 0, //确认亏损:1是，2否
            'uid'                   => ArrayHelper::getValue($file_data, 'uid', ''), //用户uid
            'source'                => $source,
            'type'                  => ($getErrorTypes == 0) ? 1 : 2,
            'state'                 => ($getErrorTypes == 0) ? 1 : 2,
            'passageway_type'       => $passageway_type,//第三方通道状态和花生米富系统状态
            'aid'                   => (ArrayHelper::getValue($order_info, 'aid', '') ? ArrayHelper::getValue($order_info, 'aid', ''):100), //通道id
            'collection_state'      => 0, //抓取状态 默认，1抓取中，2成功，3重试,99展期
            'collection_reason'     => '', //抓取返回的信息
            'collection_time'       => date("Y-m-d H:i:s", time()) , //更新时间
            'sort'                  => $sort,

        ];
        return $save_data;
    }


    /**
     * 格式数据
     * @param $file_data 文件表数据
     * @param $other_payment_data excel表数据
     * @return array
     */
    private function formatDatas($file_data, $other_payment_data)
    {
        //商户订单号
        $client_id = trim(ArrayHelper::getValue($other_payment_data, 0));
        $channel_name = ArrayHelper::getValue($file_data, 'channel_id', '');
        if($channel_name==1){//融宝
            $oremit = new Rbremit();
        }else{
            $oremit = new Cjremit();
        }
        //查找订单是否存在
        $order_info = $oremit->getOrderId($client_id);//订单表数据
        //$busscode = ArrayHelper::getValue($order_info,'business_id',0);//获取订单表business_id
        $aid = ArrayHelper::getValue($order_info,'aid',0);//获取订单表aid
        $mechart = $this->getMechart();
        $series = ArrayHelper::getValue($order_info,'channel_id',0);
        $mechart_num =  ArrayHelper::getValue($mechart,$series , '');
       /* if($busscode==25){
            $sort = 99;//展期
        }
        if($busscode==5){
            $sort = 98;//逾期
        }
        if($busscode==31){  //优惠券
            $sort = 100;//优惠卷
        }*/
        if($aid==10){  //智融钥匙103
            $sort = 103;
        }
        /*if($aid==1 && $busscode==30){ //七天乐展期101
            $sort = 101;
        }
        if($aid==1 && $busscode==26){  //七天乐快捷102
            $sort = 102;
        }*/
        if(!isset($sort)){
            $sort = 0;
        }
        //差错类型
        $getErrorTypes = $this->getErrorType($channel_name,$order_info, $other_payment_data);
        //订单状态
        $passageway_type = $this->getPassagewayTypes($channel_name,$order_info, $other_payment_data);

        //查询通道商编号
        /*$channel = new Channel();
        $result = $channel->find()->where(['id'=>ArrayHelper::getValue($order_info, 'channel_id', 0)] )->one();
        $mechart_num = $result['mechart_num'];*/

        //判断订单是否存在，不存在给通道商编号默认值 0

        $order_info = $oremit->getOrderId($client_id);//订单表数据
        if(empty($order_info)){
            //订单不存在
            Logger::dayLog('paymentbill/client_id', '订单号不存在', $client_id);
            $mechart_num=0;
        }
        $source = ArrayHelper::getValue($file_data, 'type', 1);//1:体外放款，2：体外回款
        //$file_data  文件表数据     $other_payment_data Excel数据  $order_info总订单表 pay_payorder

        $save_data = [
            'client_id'             => ArrayHelper::getValue($other_payment_data, 0),//商户订单号
            'channel_id'            => ArrayHelper::getValue($order_info, 'channel_id', 0), //回款通道id
            'series'                => $mechart_num,//通道商编号
            'return_channel'        => ArrayHelper::getValue($file_data, 'channel_id', 0), //回款通道
            'name'                  => ArrayHelper::getValue($other_payment_data, 1, ''), //姓名
            'opening_bank'          => ArrayHelper::getValue($other_payment_data, 2, ''), //开户行
            'guest_account'         => ArrayHelper::getValue($other_payment_data, 3, ''), //银行卡号
            'identityid'            => ArrayHelper::getValue($order_info, 'identityid', ''), //证件号
            'user_mobile'           => ArrayHelper::getValue($order_info, 'user_mobile', ''), //收款人手机号
            'amount'                => ArrayHelper::getValue($other_payment_data, 6, ''), //第三方交易金额
            'payment_amount'        => ArrayHelper::getValue($order_info, 'settle_amount', 0) / 100, //平台金额
            'settle_fee'            => ArrayHelper::getValue($other_payment_data, 7, ''), //手续费
            'payment_date'          => $this->runTime(ArrayHelper::getValue($other_payment_data, 9, '')), //账单日期
            'create_time'           => ArrayHelper::getValue($order_info, 'create_time', 0) / 100, //平台金额
            'file_id'               => ArrayHelper::getValue($file_data, 'id', ''), //文件ID
            'auditing_status'       => 1, //审核状态:1待审核2审核通过3审核失败
            'modify_time'           => ArrayHelper::getValue($order_info, 'modify_time', 0) / 100, //平台金额
            'reason'                => '', //原因
            'error_types'           => $getErrorTypes, //差错类型
            'loss'                  => 0, //确认亏损:1是，2否
            'uid'                   => ArrayHelper::getValue($file_data, 'uid', ''), //用户uid
            'source'                => $source,
            'type'                  => ($getErrorTypes == 0) ? 1 : 2,
            'state'                 => ($getErrorTypes == 0) ? 1 : 2,
            'passageway_type'       => $passageway_type,//第三方通道状态和花生米富系统状态
            'aid'                   => (ArrayHelper::getValue($order_info, 'aid', '') ? ArrayHelper::getValue($order_info, 'aid', ''):100), //通道id
            'collection_state'      => 0, //抓取状态 默认，1抓取中，2成功，3重试,99展期
            'collection_reason'     => '', //抓取返回的信息
            'collection_time'       => date("Y-m-d H:i:s", time()) , //更新时间
            'sort'                  => $sort,

        ];
        return $save_data;
    }


    /**
 *读取excel日期转换
 *
 **/

    public function runTime($time){

        //$d = ArrayHelper::getValue($other_payment_data, 9, '');
        $result = intval(($time-25569)*3600*24);//excel的日期是从1900-01-01开始  PHP是从1970-01-01开始两者相差25569天
        $order_time= gmdate('Y-m-d', $result);

        return $order_time;
    }

    /**
     * 差错类型
     * @param $pay_data /订单表数据
     * @param $data_set excel 数据
     * @return int
     */
    private function getErrorTypes($pay_data, $data_set)
    {
        //'1' => '通道单边账',
        if (empty($data_set)){
            return 1;
        }
        //'2' => '系统单边账',
        //商户订单号
        $client_id = trim(ArrayHelper::getValue($data_set, 0));
        //查找订单是否存在
        $oPayorder = new Payorder();
        $order_info = $oPayorder->getOrderId($client_id);//订单表数据
        if(empty($order_info)){
            return 2;
        }
        //'3' => '金额有误',
        $money = (float)ArrayHelper::getValue($data_set, 6); //第三方金额
        $payment_money = (float)ArrayHelper::getValue($pay_data, 'amount',0) / 100; //还款金额
        if (bccomp($money, $payment_money,2) != 0){
            return 3;
        }
        //'4' => '状态有误',
        $other_status = ArrayHelper::getValue($data_set, 8);
        preg_match('/成功/', $other_status, $status);
        if (empty($status)){
            return 4;
        }
        if (ArrayHelper::getValue($pay_data, 'status') != 2){
            return 4;
        }
        //'5' => '关闭订单',
        preg_match('/关闭/', $other_status, $status);
        if (!empty($status)){
            return 5;
        }
       return 0;
    }

    /**
     * 差错类型
     * @param $pay_data /订单表数据
     * @param $data_set excel 数据
     * @return int
     */
    private function getErrorType($channel_name,$pay_data, $data_set)
    {


        //'1' => '通道单边账',
        if (empty($data_set)){
            return 1;
        }
        //'2' => '系统单边账',
        //商户订单号
        if($channel_name==1){//融宝
            $oremit = new Rbremit();
        }else{
            $oremit = new Cjremit();
        }
        $client_id = trim(ArrayHelper::getValue($data_set, 0));
        //查找订单是否存在
        $order_info = $oremit->getOrderId($client_id);//订单表数据
        if(empty($order_info)){
            return 2;
        }
        //'3' => '金额有误',
        $money = (float)ArrayHelper::getValue($data_set, 6); //第三方金额
        $payment_amount = (float)ArrayHelper::getValue($pay_data, 'settle_amount',0); //还款金额
        if (bccomp($money, $payment_amount,2) != 0){
            return 3;
        }
        //'4' => '状态有误',
        $other_status = ArrayHelper::getValue($data_set, 8);
        preg_match('/成功/', $other_status, $status);
        if (empty($status)){
            return 4;
        }
        if (ArrayHelper::getValue($pay_data, 'remit_status') != 6){
            return 4;
        }
        //'5' => '关闭订单',
        preg_match('/关闭/', $other_status, $status);
        if (!empty($status)){
            return 5;
        }
        return 0;
    }

    /**
     * 订单状态
     * @param $pay_data  //订单表数据
     * @param $data_set  excel 表数据
     * @return int
     */
    private function getPassagewayType($pay_data, $data_set)
    {
        $status = 0;
        //第三方订单
        $other_status = ArrayHelper::getValue($data_set, 8);
        preg_match('/成功/', $other_status, $status);
        if (!empty($other_status)){
            $status = 1;  //第三方订单成功
        }

        //商户订单号
        $client_id = ArrayHelper::getValue($data_set, 0);

        //查找订单是否存在
        $oPayorder = new Payorder();
        $order_info = $oPayorder->getOrderId($client_id);//订单表数据
        if(empty($order_info)){
            //订单不存在
            $status = 2;//平台还款成功
        }

        //平台还款订单
        $pay_status = ArrayHelper::getValue($pay_data, 'status', 0);
        if ($pay_status == 2){
            $status = 2;//平台还款成功
        }

        if (!empty($other_status) && ($pay_status == 2)){
            $status = 3;//双方成功
        }
        return $status;
    }

    /**
     * 订单状态
     * @param $pay_data  //订单表数据
     * @param $data_set  excel 表数据
     * @return int
     */
    private function getPassagewayTypes($channel_name,$pay_data, $data_set)
    {
        $status = 0;
        //第三方订单
        $other_status = ArrayHelper::getValue($data_set, 8);
        preg_match('/成功/', $other_status, $status);
        if (!empty($other_status)){
            $status = 1;  //第三方订单成功
        }

        //商户订单号
        $client_id = ArrayHelper::getValue($data_set, 0);
        if($channel_name==1){//融宝
            $oremit = new Rbremit();
        }else{
            $oremit = new Cjremit();
        }
        //查找订单是否存在
        $order_info = $oremit->getOrderId($client_id);//订单表数据
        if(empty($order_info)){
            //订单不存在
            $status = 2;//平台还款成功
        }
        //平台出款订单
        $pay_status = ArrayHelper::getValue($pay_data, 'status', 0);
        if ($pay_status == 6){
            $status = 2;//平台出款成功
        }

        if (!empty($other_status) && ($pay_status == 6)){
            $status = 3;//双方成功
        }
        return $status;
    }
    public function getMechart(){

        return [
            152 => '100000001301635',
            168 => '100000001303510',
            174 => '200001820008',
            176 => '100000001303951',
            183 => '200002160180',
        ];

   }

}