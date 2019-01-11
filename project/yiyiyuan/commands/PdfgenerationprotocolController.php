<?php

/**
 * 自动生成pdf格式协议
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii pfgenerationprotocol > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan_short\yii pdfgenerationprotocol
 *            d:\xampp\php\php.exe D:\www\yiyiyuan_short\yii pdfgenerationprotocol 2 时间（2017-06-07）
 *
 * 生成日志所在目录
 *        计算执行条数：pdfgenerationprotocol
 *        生成借款合同：loan/年/月/日/fund_loan
 *        生成玖富借款合同：jiufu/年/月/日/fund_loan
 *        开放平台：setcontractaccredit
 */

namespace app\commands;

use app\commonapi\Logger;
use app\models\dev\User_loan;
use app\models\news\FundRecord;
use Yii;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

if (!class_exists('TCPDF')) {
    //include '/data/wwwroot/weixin/tcpdf/tcpdf.php';
    include dirname(__DIR__) . '/tcpdf/tcpdf.php';
}

class PdfgenerationprotocolController extends Controller {

    private $begin_time;

    // 命令行入口文件
    /**
     *
     * @param $fund_id 资方:2:玖富，3:连交所'
     * @return bool
     */
    public function actionIndex($fund_id, $runtime = '') {
        $fund_data = array(2, 3);
        if (!in_array($fund_id, $fund_data)) {
            return false;
        }
        if (empty($runtime)) {
            //查询前一天的成功的出款
            $begin_time = date('Y-m-d 00:00:00', strtotime('-2 day'));
            $end_time = date('Y-m-d 23:59:59', strtotime('-2 day'));
        } else {
            //查询前一天的成功的出款
            $begin_time = date('Y-m-d 00:00:00', strtotime($runtime));
            $end_time = date('Y-m-d 23:59:59', strtotime($runtime));
        }
        $this->begin_time = $begin_time;
        $condition = [
            'AND',
            ['fund' => $fund_id],
            ['agreement_status' => 'INIT'], //合同状态:INIT,LOCK,DOING,SUCCESS',
            ['between', 'create_time', $begin_time, $end_time] //创建时间
        ];
        //获取前一天出款的借款
        $total = FundRecord::find()->where($condition)->count();
        //每100条处理一次
        $limit = 100;
        $pages = ceil($total / $limit);

        Logger::dayLog('pdfgenerationprotocol', "共获取" . $total . "条数据", "每次处理" . $limit, "需要要处理" . $pages . "次");

        for ($i = 0; $i < $pages; $i++) {
            $loan_list = FundRecord::find()->where($condition)->offset(0)->limit($limit)->all();
            //如果没有出款，则直接结束
            if (empty($loan_list)) {
                break;
            }
            $loan_ids = ArrayHelper::getColumn($loan_list, 'loan_id');
            FundRecord::updateAll(['agreement_status' => 'LOCK'], ['loan_id' => $loan_ids, 'fund' => $fund_id]);
            foreach ($loan_list as $key => $value) {
                $loan = User_loan::findOne($value->loan_id);
                if ($loan->status == 7) {
                    continue;
                }
                // $this->makeAgreement($loan->loan_id, $loan->create_time);
                $this->makeContract($loan->loan_id, $loan->create_time, $fund_id);
                $value->changeStatus('DOING');
            }
            FundRecord::updateAll(['agreement_status' => 'SUCCESS'], ['agreement_status' => 'DOING', 'fund' => $fund_id]);
        }
    }

    //生成借款合同
    private function makeContract($loan_id, $date, $fund_id) {
        list($year, $month, $day) = $this->formatData($this->begin_time);

//        $url = Yii::$app->params['app_url'] . "/dev/pdf/setcontractaccredit?loan_id=" . $loan_id . "&fund_id=". $fund_id;
        $url = Yii::$app->params['app_url'] . "/dev/pdf/setcontract?loan_id=" . $loan_id;
        $filepath = Yii::$app->basePath . '/log/pdf/loan/fund_loan/' . $year . '/' . $month . '/' . $day;
        Logger::createdir($filepath);
        $contract = 'loan_' . $loan_id;
        $filename = $filepath . '/' . $contract . '.pdf';
        $this->htmltoPdf($url, $filename);
        $now_time = date('Y-m-d H:i:s');
        //修改合同编号和存放路径
        $contract_url = '/log/pdf/loan/' . $year . '/' . $month . '/' . $day . '/' . $contract . '.pdf';
        $sql_xhb = "update yi_user_loan set contract='$contract',contract_url='$contract_url',last_modify_time='$now_time' where loan_id=" . $loan_id;
        Yii::$app->db->createCommand($sql_xhb)->execute();
    }

    /**
     * 生成玖富借款合同
     * @param $loan_id
     * @param $date
     */
    private function makeAgreement($loan_id, $date) {
        list($year, $month, $day) = $this->formatData($this->begin_time);

        $url = Yii::$app->params['app_url'] . "/dev/pdf/agreement?loan_id=" . $loan_id;
        $filepath = Yii::$app->basePath . '/log/pdf/jiufu/fund_loan/' . $year . '/' . $month . '/' . $day;
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
        if (empty($strContent)) {
            return false;
        }
        $pdf->writeHTML($strContent, true, false, true, false, '');
        //输出PDF
        $pdf->Output($filename, 'F');

        return true;
    }

    private function formatData($date) {
        if (!$date) {
            return [ date('Y'), date('m'), date('d')];
        } else {
            return [ date('Y', strtotime($date)), date('m', strtotime($date)), date('d', strtotime($date))];
        }
    }

}
