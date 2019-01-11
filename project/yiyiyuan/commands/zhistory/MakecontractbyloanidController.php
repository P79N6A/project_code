<?php

/**
 * 自动生成借款合同
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands;

use app\commonapi\Logger;
use app\models\dev\User_loan;
use app\models\news\Fund_tmp;
use Exception;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

if (!class_exists('TCPDF')) {
    include '/data/wwwroot/weixin/tcpdf/tcpdf.php';
    //include 'd:\phpStudy\WWW\appyiyiyuan\tcpdf\tcpdf.php';
    //    include 'd:\yiyiyuan\yyy_usereg\tcpdf\tcpdf.php';
}

class MakecontractbyloanidController extends Controller {

    public function actionMakeall() {
        $count = Fund_tmp::find()->where(['status' => [0]])->count();
        $limit = 500;
        $page = ceil($count / $limit);
        for ($i = 0; $i < $page; $i++) {
            $data = Fund_tmp::find()->where(['status' => [0]])->offset($i * $limit)->limit($limit)->all();
            if (empty($data)) {
                break;
            }
            $ids = ArrayHelper::getColumn($data, 'id');
            $nums = Fund_tmp::updateAll(['status' => 1], ['id' => $ids]);
            foreach ($data as $key => $val) {
                $result = $this->makeone($val->loan_id);
                if ($result) {
                    $val->status = 2;
                    $val->save();
                } else {
                    Logger::dayLog('handloanpdf', $val->loan_id);
                }
            }
        }
        //Fund_tmp::updateAll(['status' => 2], ['status' => -2]);
    }

    // 生成单条合同
    public function makeone($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            $this->log("没有可生成记录！");
            return FALSE;
        }
        $loan = User_loan::findOne($loan_id);
        if ($loan && in_array($loan->business_type, array(1, 4, 5, 6))) {
            $this->makeContract($loan_id);
            return TRUE;
        } else {
            $this->log("没有可生成记录！");
            return FALSE;
        }
    }

    //生成借款合同
    private function makeContract($loan_id, $date = 0) {
        $year = date('Y');
        $month = date('m');
        $day = date('d');
        $url = Yii::$app->params['app_url'] . "/dev/pdf/setloancontract?loan_id=" . $loan_id;
        $filepath = Yii::$app->basePath . '/log/pdf/handloan';
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

        try {
            $strContent = file_get_contents($url);
            if (empty($strContent)) {
                return false;
            }
            $pdf->writeHTML($strContent, true, false, true, false, '');
            //输出PDF
            $pdf->Output($filename, 'F');
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
        return true;
    }

    // 保存日志
    private function log($message) {
        echo $message . "\n";
    }

}
