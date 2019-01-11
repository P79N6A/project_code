<?php
/**
 * 导出excel工具类
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/27
 * Time: 9:59
 */
namespace app\commonapi;
use PHPExcel;
use PHPExcel_Writer_Excel5;
use Yii;
use app\models\dev\User_extend;
use app\models\dev\Areas;
if (!class_exists('PHPExcel')) {
    include '../phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include '../phpexcel/Excel5.php';
}
class ExportExcelTool
{
    /**
     * 用途：担保借款财务管理->出款中，
     * @param $orderData
     */
    public function downlist_xls($orderData) {
        $icount = count($orderData);
        $remit_type = array(
            '1' => '后台出款',
            '2' => '担保卡退款',
            '3' => '担保卡退款',
            '4' => '收益提现',
            '5' => '网盟提现',
            '6' => '红包提现',
        );
        $status = Keywords::getLoanStatus();
        $business_type = array(
            '1' => '好友',
            '2' => '担保卡',
            '3' => '担保人',
        );
        // 创建一个处理对象实例
        $objExcel = new PHPExcel();

        // 创建文件格式写入对象实例, uncomment
        $objWriter = new PHPExcel_Writer_Excel5($objExcel);

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
        $objActSheet->setCellValue('A1', '订单单号');
        $objActSheet->setCellValue('B1', '账单单号');
        $objActSheet->setCellValue('C1', '提现时间');
        $objActSheet->setCellValue('D1', '用户姓名');
        $objActSheet->setCellValue('E1', '银行卡号');
        $objActSheet->setCellValue('F1', '开户行');
        $objActSheet->setCellValue('G1', '出款金额');
        $objActSheet->setCellValue('H1', '业务类型');
        $objActSheet->setCellValue('I1', '状态');
        for ($i = 0; $i < $icount; $i++) {
            if ($orderData[$i]->type == 1) {
                $loanextend = $orderData[$i]->loanextend;
                if (!empty($loanextend)) {
                    $loanextend = $orderData[$i]->loanextend;
                    if (!empty($loanextend)) {
                        if ($loanextend->payment_channel == 1) {
                            $payment_channel = '新浪出款';
                        } else if ($loanextend->payment_channel == 2) {
                            $payment_channel = '中信出款';
                        } else if ($loanextend->fund == 2) {
                            $payment_channel = '玖富出款';
                        } else if ($loanextend->payment_channel == 4) {
                            $payment_channel = '恒丰出款';
                        } else if ($loanextend->payment_channel == 5) {
                            $payment_channel = '广发出款';
                        } else if ($loanextend->payment_channel == 110) {
                            $payment_channel = '融宝一亿元';
                        } else if ($loanextend->payment_channel == 112) {
                            $payment_channel = '融宝米富';
                        }else if($loanextend->payment_channel == 113){
                            $payment_channel = '宝付（米富）';
                        } else if ($loanextend->payment_channel == 114) {
                            $payment_channel = '宝付（一亿元）';
                        } else if ($loanextend->payment_channel == 117) {
                            $payment_channel = '畅捷出款';
                        }else if($loanextend->payment_channel == 131){
                            $payment_channel = '畅捷快捷';
                        } else {
                            $payment_channel = '未知';
                        }
                    } else {
                        $payment_channel = '中信出款';
                    }
                } else {
                    $payment_channel = '中信出款';
                }
            } else {
                $payment_channel = $remit_type[$orderData[$i]->type];
            }
            $objActSheet->setCellValue('A' . ( $i + 2),  !empty($orderData[$i]->order_id) ? '+'.$orderData[$i]->order_id : '');
            $objActSheet->setCellValue('B' . ( $i + 2),  !empty($orderData[$i]->settle_request_id) ?  '+'.$orderData[$i]->settle_request_id : '');
            $objActSheet->setCellValue('C' . ( $i + 2), !empty($orderData[$i]->create_time) ? $orderData[$i]->create_time : '');
            $objActSheet->setCellValue('D' . ( $i + 2), !empty($orderData[$i]->user) ? $orderData[$i]->user->realname : '');
            $objActSheet->setCellValue('E' . ( $i + 2), !empty($orderData[$i]->bank) ? '+' . $orderData[$i]->bank->card : '');
            $objActSheet->setCellValue('F' . ( $i + 2), !empty($orderData[$i]->bank) ? $orderData[$i]->bank->bank_name : '');
            $objActSheet->setCellValue('G' . ( $i + 2), !empty($orderData[$i]->settle_amount) ? $orderData[$i]->settle_amount : '');
            $objActSheet->setCellValue('H' . ( $i + 2), !empty($payment_channel) ? $payment_channel : '');
            $objActSheet->setCellValue('I' . ( $i + 2), !empty($status[$orderData[$i]->remit_status]) ? $status[$orderData[$i]->remit_status] : '');
        }
        if (!empty($orderData)) {
            $title = $status[$orderData[0]->remit_status];
        } else {
            $title = '';
        }
        $outputFileName = date('Y-m-d', time()) . $title . "账单详情" . ".xls";
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
     * 玖富已出款数据导出
     * @param $orderData
     * @throws \Exception
     */
    public function downlist_jiufu_xls($orderData) {
        $icount = count($orderData);
        // 创建一个处理对象实例
        $objExcel = new PHPExcel();

        // 创建文件格式写入对象实例, uncomment
        $objWriter = new PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('玖富数据导出');
        $objActSheet->getColumnDimension('A')->setWidth(15);
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(15);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->setCellValue('A1', '账单序号');
        $objActSheet->setCellValue('B1', '用户姓名');
        $objActSheet->setCellValue('C1', '银行卡户名');
        $objActSheet->setCellValue('D1', '银行卡开户行');
        $objActSheet->setCellValue('E1', '银行卡号');
        $objActSheet->setCellValue('F1', '身份证号');
        $objActSheet->setCellValue('G1', '联系手机');
        for ($i = 0; $i < $icount; $i++) {
            $objActSheet->setCellValue('A' . ( $i + 2), '+' . $i + 1);
            $objActSheet->setCellValue('B' . ( $i + 2), !empty($orderData[$i]->user) ? $orderData[$i]->user->realname : '');
            $objActSheet->setCellValue('C' . ( $i + 2), !empty($orderData[$i]->bank->realname) ? $orderData[$i]->user->realname : '');
            $objActSheet->setCellValue('D' . ( $i + 2), !empty($orderData[$i]->bank->bank_name) ? $orderData[$i]->bank->bank_name : '');
            $objActSheet->setCellValue('E' . ( $i + 2), !empty($orderData[$i]->bank->card) ? $orderData[$i]->bank->card . ' ' : '');
            $objActSheet->setCellValue('F' . ( $i + 2), !empty($orderData[$i]->user->identity) ? $orderData[$i]->user->identity . ' ' : '');
            $objActSheet->setCellValue('G' . ( $i + 2), !empty($orderData[$i]->user->mobile) ? $orderData[$i]->user->mobile : '');
        }

        $outputFileName = date('Y-m-d', time()) . "玖富出款账单详情" . ".xls";
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
     * 玖富已出款数据导出
     * @param $orderData
     * @throws \Exception
     */
    public function downlist_jiufu_two_xls($orderData) {
        $icount = count($orderData);
        // 创建一个处理对象实例
        $objExcel = new PHPExcel();

        // 创建文件格式写入对象实例, uncomment
        $objWriter = new PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('玖富数据导出');
        $objActSheet->getColumnDimension('A')->setWidth(15);
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(15);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->setCellValue('A1', '账单序号');
        $objActSheet->setCellValue('B1', '用户姓名');
        $objActSheet->setCellValue('C1', '身份证号');
        $objActSheet->setCellValue('D1', '联系地址');
        $objActSheet->setCellValue('E1', '联系电话');
        $objActSheet->setCellValue('F1', '工作单位');
        $objActSheet->setCellValue('G1', '工作地址');
        $objActSheet->setCellValue('H1', '统计日期');
        for ($i = 0; $i < $icount; $i++) {
            $objActSheet->setCellValue('A' . ( $i + 2), '+' . $i + 1);
            $objActSheet->setCellValue('B' . ( $i + 2), !empty($orderData[$i]->user) ? $orderData[$i]->user->realname : '');
            $objActSheet->setCellValue('C' . ( $i + 2), !empty($orderData[$i]->user->identity) ? $orderData[$i]->user->identity . ' ' : '');
            $objActSheet->setCellValue('D' . ( $i + 2), !empty($orderData[$i]->user->user_id) ? $this->getAddress($orderData[$i]->user->user_id) : '');
            $objActSheet->setCellValue('E' . ( $i + 2), !empty($orderData[$i]->user->mobile) ? $orderData[$i]->user->mobile : '');
            $objActSheet->setCellValue('F' . ( $i + 2), !empty($orderData[$i]->user->extend->company) ? $orderData[$i]->user->extend->company : '');
            $objActSheet->setCellValue('G' . ( $i + 2), !empty($orderData[$i]->user) ? $this->getAddress($orderData[$i]->user->user_id) : 'company_area');
            $objActSheet->setCellValue('H' . ( $i + 2), date('Y-m-d H:i:s', time()));
        }

        $outputFileName = date('Y-m-d', time()) . "玖富出款账单详情" . ".xls";
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

    /*
     * 通过用户code获取省市区详细地址
     */

    private function getAddress($userId, $areaField = 'home_area') {
        $address = $add = '';
        $userExtendInfo = User_extend::find()->where("user_id = $userId")->one();
        if (empty($userExtendInfo)) {
            return '';
        } else {
            $areaCode = $userExtendInfo->$areaField;
            if ($areaField == 'home_area') {
                $add = $userExtendInfo->home_address;
            } else if ($areaField == 'company_area') {
                $add = $userExtendInfo->company_address;
            }
        }

        $areaInfo = Areas::find()->where("code = $areaCode")->one();
        if (!empty($areaInfo)) {
            $address = $areaInfo->name;
            $provinceInfo = Areas::findOne($areaInfo->pID);
            if (!empty($provinceInfo)) {
                $address = $provinceInfo->name . $address;
                $regionInfo = Areas::findOne($provinceInfo->pID);
                $address = !empty($regionInfo) ? $regionInfo->name . $address : $address;
            }
        }
        return $address . $add;
    }

    /**
     * 借款数据导出
     * @param $orderData
     * @throws \Exception
     */
    public function downrepay_xls($orderData) {
        $icount = count($orderData);
        $business_type = array(
            '1' => '好友',
            '2' => '担保卡',
            '3' => '担保人',
        );
// 创建一个处理对象实例
        $objExcel = new PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

//设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(10);
        $objActSheet->getColumnDimension('B')->setWidth(15);
        $objActSheet->getColumnDimension('C')->setWidth(15);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->setCellValue('A1', '账单单号');
        $objActSheet->setCellValue('B1', '用户姓名');
        $objActSheet->setCellValue('C1', '用户手机号');
        $objActSheet->setCellValue('D1', '提现金额');
        $objActSheet->setCellValue('E1', '业务类型');
        $objActSheet->setCellValue('F1', '还款时间');
        for ($i = 0; $i < $icount; $i++) {
            $objActSheet->setCellValue('A' . ( $i + 2), '+' . $orderData[$i]->loan_id);
            $objActSheet->setCellValue('B' . ( $i + 2), !empty($orderData[$i]->user) ? $orderData[$i]->user->realname : '');
            $objActSheet->setCellValue('C' . ( $i + 2), !empty($orderData[$i]->user) ? $orderData[$i]->user->mobile : '');
            $objActSheet->setCellValue('D' . ( $i + 2), $orderData[$i]->amount);
            $objActSheet->setCellValue('E' . ( $i + 2), isset($business_type[$orderData[$i]->business_type]) ? $business_type[$orderData[$i]->business_type] : '其他');
            $objActSheet->setCellValue('F' . ( $i + 2), $orderData[$i]->repay_time);
        }
        $outputFileName = date('Y-m-d', time()) . "已还款" . ".xls";
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
     * 借款数据导出
     * @param $orderData
     * @throws \Exception\
     */
    public function down_xls_huankuan($orderData) {
        $icount = count($orderData);

// 创建一个处理对象实例
        $objExcel = new PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

//设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(10);
        $objActSheet->getColumnDimension('B')->setWidth(25);
        $objActSheet->getColumnDimension('C')->setWidth(20);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->setCellValue('A1', '账单号');
        $objActSheet->setCellValue('B1', '身份证号码');
        $objActSheet->setCellValue('C1', '真实姓名');
        $objActSheet->setCellValue('D1', '还款时间');
        $objActSheet->setCellValue('E1', '应还款金额');
        $objActSheet->setCellValue('F1', '还款备注');

        for ($i = 0; $i < $icount; $i++) {
            $objActSheet->setCellValue('A' . ( $i + 2), "+" .$orderData[$i]->loan_id);
            $objActSheet->setCellValue('B' . ( $i + 2),  "+" .$orderData[$i]->user->identity);
            $objActSheet->setCellValue('C' . ( $i + 2), $orderData[$i]->user->realname);
            $objActSheet->setCellValue('D' . ( $i + 2), $orderData[$i]->createtime);
            $objActSheet->setCellValueExplicit('E' . ( $i + 2), $orderData[$i]->huankuan_amount);
            $objActSheet->setCellValueExplicit('F' . ( $i + 2), $orderData[$i]->repay_mark);
        }
        $outputFileName = date('Y-m-d', time()) . "待还款确认" . ".xls";
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
     * 出款失败驳回记录数据导出
     * @param $orderData
     * @throws \Exception
     */
    public function downloglist_xls($orderData) {
        $icount = count($orderData);
        $remit_type = array(
            '1' => '后台出款',
            '2' => '担保卡退款',
            '3' => '担保卡退款',
            '4' => '收益提现',
            '5' => '网盟提现',
            '6' => '红包提现',
        );
        $status = array(
            'FAIL' => '出款失败',
            'INIT' => '出款中',
            'PROCEING' => '出款中',
            'REJECT' => '驳回',
            'SUCCESS' => '出款成功',
        );
        $status1 = array(
            '1' => '借记卡',
            '2' => '信用卡',
            '3' => '替他',
            '0' => '借记卡',
        );
        $business_type = array(
            '1' => '好友',
            '2' => '担保卡',
            '3' => '担保人',
        );
// 创建一个处理对象实例
        $objExcel = new PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

//设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(15);
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(15);
        $objActSheet->getColumnDimension('D')->setWidth(15);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('I')->setWidth(15);
        $objActSheet->getColumnDimension('J')->setWidth(15);
        $objActSheet->getColumnDimension('K')->setWidth(15);
        $objActSheet->getColumnDimension('L')->setWidth(15);
        $objActSheet->getColumnDimension('M')->setWidth(30);
        $objActSheet->setCellValue('A1', '账单单号');
        $objActSheet->setCellValue('B1', '提现时间');
        $objActSheet->setCellValue('C1', '用户姓名');
        $objActSheet->setCellValue('D1', '用户电话');
        $objActSheet->setCellValue('E1', '银行卡号');
        $objActSheet->setCellValue('F1', '开户行');
        $objActSheet->setCellValue('G1', '卡类型');
        $objActSheet->setCellValue('H1', '出款金额');
        $objActSheet->setCellValue('I1', '业务类型');
        $objActSheet->setCellValue('J1', '状态');
        $objActSheet->setCellValue('K1', '操作人');
        $objActSheet->setCellValue('L1', '驳回时间');
        $objActSheet->setCellValue('M1', '驳回理由');
        for ($i = 0; $i < $icount; $i++) {
            $objActSheet->setCellValue('A' . ( $i + 2), "+" .$orderData[$i]->settle_request_id);
            $objActSheet->setCellValue('B' . ( $i + 2), $orderData[$i]->create_time);
            $objActSheet->setCellValue('C' . ( $i + 2), !empty($orderData[$i]->user) ? $orderData[$i]->user->realname : '');
            $objActSheet->setCellValue('D' . ( $i + 2), !empty($orderData[$i]->user) ? $orderData[$i]->user->mobile : '');
            $objActSheet->setCellValue('E' . ( $i + 2), !empty($orderData[$i]->bank) ? '+' . $orderData[$i]->bank->card : '');
            $objActSheet->setCellValue('F' . ( $i + 2), !empty($orderData[$i]->bank) ? $orderData[$i]->bank->bank_name : '');
            $objActSheet->setCellValue('G' . ( $i + 2), !empty($orderData[$i]->bank) ? $status1[$orderData[$i]->bank->type] : '');
            $objActSheet->setCellValue('H' . ( $i + 2), $orderData[$i]->real_amount);
            $objActSheet->setCellValue('I' . ( $i + 2), $remit_type[$orderData[$i]->type]);
            $objActSheet->setCellValue('J' . ( $i + 2), $status[$orderData[$i]->remit_status]);
            $objActSheet->setCellValue('K' . ( $i + 2), !empty($orderData[$i]->manager) ? $orderData[$i]->manager['admin_name'] : '');
            $objActSheet->setCellValue('L' . ( $i + 2), !empty($orderData[$i]->manager) ? $orderData[$i]->manager['create_time'] : '');
            $objActSheet->setCellValue('M' . ( $i + 2), !empty($orderData[$i]->manager) ? $orderData[$i]->manager['reason'] : '');
        }
        if (!empty($orderData)) {
            $title = $status[$orderData[0]->remit_status];
        } else {
            $title = '';
        }
        $outputFileName = date('Y-m-d', time()) . $title . "账单详情" . ".xls";
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