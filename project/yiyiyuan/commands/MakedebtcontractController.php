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
use app\models\dev\User_loan_flows;
use Yii;
use yii\console\Controller;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

if (!class_exists('TCPDF')) {
    include '/data/wwwroot/weixin/tcpdf/tcpdf.php';
    //include 'd:\phpStudy\WWW\appyiyiyuan\tcpdf\tcpdf.php';
}

class MakedebtcontractController extends Controller {

    // 生成2017年3月13日以后的所有合同
    public function actionMakeall() {
        //查询2017年3月13日以后的成功的出款
        $begin_time = "2017-03-29 14:40:27";
        $now_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 00:00:00', strtotime('+1 days', strtotime($begin_time)));
        $condition = [
            'AND',
            ['loan_status' => 9],
        ];
        do {
            $condition[2] = ['>=', 'create_time', $begin_time];
            $condition[3] = ['<', 'create_time', $end_time];
            //获取前一天出款的借款
            $total = User_loan_flows::find()->where($condition)->count();

            //每100条处理一次
            $limit = 100;
            $pages = ceil($total / $limit);

            Logger::dayLog('makecontract', "\n共获取" . $total . "条数据每次处理" . $limit . "需要要处理" . $pages . "次\n");

            for ($i = 0; $i < $pages; $i++) {
                $loan_list = User_loan_flows::find()->where($condition)->offset($i * $limit)->limit($limit)->all();
                //如果没有出款，则直接结束
                if (empty($loan_list)) {
                    break;
                }

                foreach ($loan_list as $key => $value) {
                    $loan = User_loan::findOne($value->loan_id);
                    if ($loan->status == 7) {
                        continue;
                    }
                    if ($loan && in_array($loan->business_type, array(1, 4, 5, 6))) {//生成借款合同
                        $this->makeContract($loan->loan_id, $loan->create_time);
                        $this->makeJiufuContract($loan->loan_id, $loan->create_time);
                    }
                }
            }
            $begin_time = date('Y-m-d 00:00:00', strtotime('+1 days', strtotime($begin_time)));
            $end_time = date('Y-m-d 00:00:00', strtotime('+1 days', strtotime($end_time)));
        } while ($end_time <= $now_time);
    }

    // 生成单条合同
    public function actionMakeone($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            $this->log("没有可生成记录！");
            exit;
        }
        $loan = User_loan::findOne($loan_id);
        if ($loan && in_array($loan->business_type, array(1,4,5,6))) {
            $this->makeContract($loan_id, $loan->create_time);
            //$this->makeJiufuContract($loan_id, $loan->create_time);
        } else {
            $this->log("没有可生成记录！");
            exit;
        }
    }

    //生成玖富借款合同
    private function makeJiufuContract($loan_id, $date) {
        if (!$date) {
            $year = date('Y');
            $month = date('m');
            $day = date('d');
        } else {
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
            $day = date('d', strtotime($date));
        }
        $url = Yii::$app->params['app_url'] . "/dev/pdf/jiufu?loan_id=" . $loan_id;
        $filepath = Yii::$app->basePath . '/log/pdf/jiufu/' . $year . '/' . $month . '/' . $day;
        Logger::createdir($filepath);
        $contract = 'loan_' . $loan_id;
        $filename = $filepath . '/' . $contract . '.pdf';
        $this->htmltoPdf($url, $filename);
    }

    //生成借款合同
    private function makeContract($loan_id, $date) {
        if (!$date) {
            $year = date('Y');
            $month = date('m');
            $day = date('d');
        } else {
            $year = date('Y', strtotime($date));
            $month = date('m', strtotime($date));
            $day = date('d', strtotime($date));
        }
        $url = Yii::$app->params['app_url'] . "/dev/pdf/setcontract?loan_id=" . $loan_id;
        $filepath = Yii::$app->basePath . '/log/pdf/loan/' . $year . '/' . $month . '/' . $day;
        Logger::createdir($filepath);
        $contract = 'loan_' . $loan_id;
        $filename = $filepath . '/' . $contract . '.pdf';
        $this->htmltoPdf($url, $filename);
        $now_time = date('Y-m-d H:i:s');
        //修改合同编号和存放路径
        $contract_url = '/log/pdf/loan/' . $year . '/' . $month . '/' . $day . '/' . $contract . '.pdf';
        $sql_xhb = "update yi_user_loan set contract='$contract',contract_url='$contract_url',last_modify_time='$now_time' where loan_id=" . $loan_id;
        $ret_invest = Yii::$app->db->createCommand($sql_xhb)->execute();
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
