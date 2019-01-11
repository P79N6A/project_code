<?php

namespace app\modules\settlement\controllers;

use app\common\Common;
use app\common\Func;
use app\models\Whitelist;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

if (!class_exists('PHPExcel')) {

    include Yii::$app->basePath.'/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/common/phpexcel/Excel5.php';
}

abstract class AdminController extends \app\common\BaseController {
    public $layout = 'main';
    public $vvars; // 模板变量
    public $aid = 1;//项目id
    private $allowIP = ['127.0.0.1', '121.69.71.58', '124.193.149.180', '124.200.104.130'];
    /**
	 * 初始化操作
	 */
	public function init(){
	    //限制访问ip
        $ipAllow = $this->chkIp();
        if( !$ipAllow ){
            echo '访问IP受限，请联系管理员！';die;
        }
		$aid = $this->getNowAid();
        //$aid = empty($aid)?1:$aid;
        if(isset($aid) && $aid==4){
            $this->vvars['nav'] = 'pay4';
        }else if($aid==8){
            $this->vvars['nav'] = 'pay8';
        }else if($aid==0){
            $this->vvars['nav'] = 'pay0';
        }
        $this->aid = $aid;
	}
    public function getNowAid(){
         $filepath = Yii::$app->basePath.'/log/aid.txt';
         if(!file_exists($filepath)){
             touch($filepath,0775);

         }
         $aid =   file_get_contents($filepath);
         return $aid;
    }
    public function setNowAid($aid){
        $filepath = Yii::$app->basePath.'/log/aid.txt';
        file_put_contents($filepath,$aid);
    }
    /**
     * 返回session信息
     */
    public function getUser() {
        return Yii::$app->admin->identity;
    }
    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user'  => 'set_admin',
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => [],
                        'roles' => ['@'], //@代表授权用户
                    ],
                ],
            ],
        ];
    }

    // start adminsession表session操作
    //设置session
    public function setVal($key, $val) {
        Yii::$app->session->set($key, $val);
    }
    //获取session
    public function getVal($key) {
        return Yii::$app->session->get($key);
    }
    //删除session
    public function delVal($key) {
        return Yii::$app->session->remove($key);
    }

    /**
     * 验证访问IP
     * @return bool
     */
    private function chkIp(){
        $ip = Func::get_client_ip();
        if(empty($ip)){
            return false;
        }
        return in_array($ip,$this->allowIP);
    }

    // end

    /**
     * 显示结果信息
     * @param  int $res_code  错误码0 正确  | >0错误
     * @param  str $res_data结果   | 错误原因
     * @param  str $type   json | redict
     * @param  str $redirect [description]
     * @return json | html
     */
    protected function showMessage($res_code, $res_data, $type = null, $redirect = null, $timeout = 3) {
        // 自动判断返回类型
        if (empty($type)) {
            $type = Yii::$app->request->getIsAjax() ? 'json' : 'html';
        }
        $type = strtoupper($type);
        // 返回结果: 统一json格式或消息提示代码
        switch ($type) {
        case 'JSON':
            return json_encode([
                'res_code' => $res_code,
                'res_data' => $res_data,
            ]);
            break;
        case 'HTML':
        default:
            $redirect = is_null($redirect) ? Yii::$app->request->getReferrer() : $redirect;
            $this->vvars['menu'] = '';
            return $this->render('/showmessage', [
                'res_code' => $res_code,
                'res_data' => $res_data,
                'redirect' => $redirect,
                'timeout' => $timeout,
            ]);
            break;
        }
    }

    protected function channelData()
    {
        //1:融宝 2:宝付;3:畅捷',
        return [
            '1' => '融宝',
            '2' => '宝付',
            '3' => '畅捷',
        ];
    }

    protected function get($name = null, $defaultValue = null) {
        return Yii::$app->request->get($name, $defaultValue);
    }

    protected function post($name = null, $defaultValue = null) {
        return Yii::$app->request->post($name, $defaultValue);
    }

    protected function returnFileJson($error_msg)
    {
        return json_encode(['msg'=>$error_msg]);
    }


    /**
     * 下载成功对账成功数据
     * @param $orderData
     * @throws \Exception
     */
    protected function downlist_xls($orderData) {
        $icount = count($orderData);
        $channel_data = $this->channelData(); //出款通道名
        $error_status = [1=>'已处理', '2' => '未处理'];
        $type = [1=>'正常', '2' => '差错'];

// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

//设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(15);
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->setCellValue('A1', '序号');
        $objActSheet->setCellValue('B1', '出款通道名称');
        $objActSheet->setCellValue('C1', '商户订单号');
        $objActSheet->setCellValue('D1', '收款人姓名');
        $objActSheet->setCellValue('E1', '上游交易金额/订单金额/元 ');
        $objActSheet->setCellValue('F1', '手续费/元');
        $objActSheet->setCellValue('G1', '差错类型');
        $objActSheet->setCellValue('H1', '创建时间');
        for ($i = 0; $i < $icount; $i++) {
            $channel_name = empty($channel_data[$orderData[$i]->channel_id])?"":$channel_data[$orderData[$i]->channel_id];
            $error_name = empty($type[$orderData[$i]->type])?"":$type[$orderData[$i]->type];
            $objActSheet->setCellValue('A' . ( $i + 2), $orderData[$i]->id);
            $objActSheet->setCellValue('B' . ( $i + 2),  $channel_name);
            $objActSheet->setCellValue('C' . ( $i + 2), $orderData[$i]->client_id);
            $objActSheet->setCellValue('D' . ( $i + 2), $orderData[$i]->guest_account_name);
            $objActSheet->setCellValue('E' . ( $i + 2), $orderData[$i]->settle_amount.'/'.$orderData[$i]->amount);
            $objActSheet->setCellValue('F' . ( $i + 2), $orderData[$i]->settle_fee);
            $objActSheet->setCellValue('G' . ( $i + 2), $error_name);
            $objActSheet->setCellValue('H' . ( $i + 2), $orderData[$i]->create_time);
        }
        $outputFileName = date('Y-m-d', time())  . "账单详情" . ".xls";
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
     * 下载差错账单数据
     * @param $orderData
     * @throws \Exception
     */
    public function downlist_xls_fail($orderData) {
        $icount = count($orderData);
        $channel_data = $this->channelData(); //出款通道名
        $error_status = [1=>'已处理', '2' => '未处理'];
        $type = [1=>'正常', '2' => '差错'];

// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

//设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(15);
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->setCellValue('A1', '序号');
        $objActSheet->setCellValue('B1', '出款通道名称');
        $objActSheet->setCellValue('C1', '商户订单号');
        $objActSheet->setCellValue('D1', '收款人姓名');
        $objActSheet->setCellValue('E1', '收款人银行账号');
        $objActSheet->setCellValue('F1', '订单金额/元');
        $objActSheet->setCellValue('G1', '手续费/元');
        $objActSheet->setCellValue('H1', '账单日期');
        $objActSheet->setCellValue('I1', '创建时间');
        $objActSheet->setCellValue('J1', '对账结果');
        for ($i = 0; $i < $icount; $i++) {
            $channel_name = empty($channel_data[$orderData[$i]->channel_id])?"":$channel_data[$orderData[$i]->channel_id];
            $error_name = empty($error_status[$orderData[$i]->error_status])?"":$error_status[$orderData[$i]->error_status];
            $objActSheet->setCellValue('A' . ( $i + 2), $orderData[$i]->id);
            $objActSheet->setCellValue('B' . ( $i + 2),  $channel_name);
            $objActSheet->setCellValue('C' . ( $i + 2), $orderData[$i]->client_id);
            $objActSheet->setCellValue('D' . ( $i + 2), $orderData[$i]->guest_account_name);
            $objActSheet->setCellValue('E' . ( $i + 2), $orderData[$i]->guest_account);
            $objActSheet->setCellValue('F' . ( $i + 2), $orderData[$i]->settle_amount);
            $objActSheet->setCellValue('G' . ( $i + 2), $orderData[$i]->settle_fee);
            $objActSheet->setCellValue('H' . ( $i + 2), $orderData[$i]->bill_number);
            $objActSheet->setCellValue('I' . ( $i + 2), $orderData[$i]->create_time);
            $objActSheet->setCellValue('J' . ( $i + 2), $error_name);
        }
        $outputFileName = date('Y-m-d', time())  . "账单详情" . ".xls";
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
     * 下载模板
     * @throws \PHPExcel_Exception
     */
    protected function downlist_model_xls() {
        // 创建一个处理对象实例
        $objExcel = new \PHPExcel();

        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(15);
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->getColumnDimension('J')->setWidth(15);
        $objActSheet->setCellValue('A1', '商户订单号');
        $objActSheet->setCellValue('B1', '收款人姓名');
        $objActSheet->setCellValue('C1', '收款人开户行');
        $objActSheet->setCellValue('D1', '收款人银行卡号');
        $objActSheet->setCellValue('E1', '收款人证件号 ');
        $objActSheet->setCellValue('F1', '收款人手机号');
        $objActSheet->setCellValue('G1', '金额');
        $objActSheet->setCellValue('H1', '手续费');
        $objActSheet->setCellValue('I1', '付款状态');
        $objActSheet->setCellValue('J1', '账单日期');


        $outputFileName =  "账单模板.xls";
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
     * 出款通道名称
     * @return array
     */
    public function passageOfMoney()
    {
        return [
            '1' => '融宝',
            '2' => '宝付',
            '3' => '畅捷',
            '4' => '玖富',
            '5' => '微神马',
            '6' => '新浪',
            '7' => '小诺理财',
        ];
    }

    /**
     * 差错类型
     * @return array
     */
    public function errorTypes()
    {
        return [
            '1' => '通道单边账',
            '2' => '支付系统单边账',
            '3' => '支付系统金额有误',
            '4' => '支付系统状态有误',
            '5' => '支付对业务单边账',
            '6' => '业务系统单边账',
            '7' => '业务系统金额有误',
            '8' => '业务系统状态有误',
            '9' => '关闭订单',
        ];
    }

    /**
     * 下载差错账单数据
     * @param $orderData
     * @throws \Exception
     */
    public function downlist_xls_uppper($orderData) {
        $icount = count($orderData);

// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $channel_name = $this->passageOfMoney();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

//设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(15);
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->getColumnDimension('J')->setWidth(15);
        $objActSheet->setCellValue('A1', '序号');
        $objActSheet->setCellValue('B1', '出款通道名称');
        $objActSheet->setCellValue('C1', '商户订单号');
        $objActSheet->setCellValue('D1', '通道商编号');
        $objActSheet->setCellValue('E1', '收款人姓名');
        $objActSheet->setCellValue('F1', '收款人银行账号');
        $objActSheet->setCellValue('G1', '订单金额/元');
        $objActSheet->setCellValue('H1', '手续费/元');
        $objActSheet->setCellValue('I1', '账单日期');
        $objActSheet->setCellValue('J1', '创建时间');
        $number = 0;
        for ($i = 0; $i < $icount; $i++) {
            $number++;
            $objActSheet->setCellValue('A' . ( $i + 2), $number);
            $objActSheet->setCellValue('B' . ( $i + 2), ArrayHelper::getValue($channel_name, $orderData[$i]->channel_id, ''));
            $objActSheet->setCellValue('C' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'client_id', ''));
            $objActSheet->setCellValue('D' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'client_number', ''));
            $objActSheet->setCellValue('E' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'guest_account_name', ''));
            $objActSheet->setCellValue('F' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'guest_account', ''));
            $objActSheet->setCellValue('G' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'settle_amount', ''));
            $objActSheet->setCellValue('H' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'settle_fee', ''));
            $objActSheet->setCellValue('I' . ( $i + 2), date("Y-m-d", strtotime(ArrayHelper::getValue($orderData[$i], 'bill_number', ''))));
            $objActSheet->setCellValue('J' . ( $i + 2), ArrayHelper::getValue($orderData[$i], 'create_time', ''));
        }
        $outputFileName = date('Y-m-d', time())  . "账单详情" . ".xls";
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
