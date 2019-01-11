<?php

/**
 * 生成整份借款合同
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands;

use app\models\dev\Loan_record;
use app\models\dev\User_loan_flows;
use app\commonapi\Logger;
use Yii;
use yii\console\Controller;

if (!class_exists('TCPDF')) {
    include '/data/wwwroot/weixin/tcpdf/tcpdf.php';
}

class MakeloancontractController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        //查询前一天的成功的出款
        $sql = "select * from yi_collections where id < 11";
        $loan_coupon = Yii::$app->db->createCommand($sql)->queryAll();
        foreach ($loan_coupon as $key => $value) {
            $loan_id = $value['loan_id'];

            $this->makeContract($loan_id);
        }
    }

    //生成借款合同
    private function makeContract($loan_id) {
        $url = Yii::$app->params['app_url'] . "/dev/pdf/setloancontract?loan_id=" . $loan_id;
        $filepath = Yii::$app->basePath . '/log/pdf/contract/' . date('Y') . '/' . date('m') . '/' . date('d');
        Logger::createdir($filepath);
        $contract = 'loan_' . $loan_id;
        $filename = $filepath . '/' . $contract . '.pdf';
        $this->htmltoPdf($url, $filename);
    }

    //将HTML页面转化为PDF格式的文档
    private function htmltoPdf($url = null, $filename = null) {
        //实例化
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // 设置文档信息
        $pdf->SetCreator('Helloweba');
        $pdf->SetAuthor('yueguangguang');
        $pdf->SetTitle('合同实例');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, PHP');

        // 设置页眉和页脚信息

        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // 设置页眉和页脚字体
        $pdf->setHeaderFont(Array('stsongstdlight', '', '10'));
        $pdf->setFooterFont(Array('helvetica', '', '8'));

        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');

        // 设置间距
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // 设置分页
        $pdf->SetAutoPageBreak(TRUE, 25);

        // set image scale factor
        $pdf->setImageScale(1.25);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        //设置字体
        $pdf->SetFont('stsongstdlight', '', 12);

        $pdf->AddPage();

        $strContent = file_get_contents($url);

        $pdf->writeHTML($strContent, true, false, true, false, '');
        //输出PDF
        $pdf->Output($filename, 'F');

        return true;
    }

    // 保存日志
    private function log($message) {
        echo $message . "\n";
    }

}
