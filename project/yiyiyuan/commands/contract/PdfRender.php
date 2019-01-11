<?php

namespace app\commands\contract;

use app\commonapi\Logger;
use Yii;

if (!class_exists('TCPDF')) {
    include Yii::$app->basePath . '/tcpdf/tcpdf.php';
}

/**
 * pdf 生成、渲染类
 * Class pdfRender
 * @package app\commands\contract
 */
class PdfRender{

    /**
     * 生成、渲染 pdf
     * @param string $template 需要渲染的模版
     * @param string $pdfPath  pdf保存路径
     * @param $data  渲染数据
     * @return bool|void
     */
    public function pdfRender($template, $pdfPath, $data){
        if(!$template || !$pdfPath || empty($data)){
            return false;
        }
        $strContent = $this->myRender($template,$data);
        return $this->htmlToPdf($pdfPath, $strContent);
    }

    /**
     * 渲染模板
     * @param [type] $_file_
     * @param array $_params_
     * @return void
     */
    private function myRender($_file_, $_params_ = []) {
        ob_start();
        ob_implicit_flush(false);
        extract($_params_, EXTR_OVERWRITE);
        require $_file_;

        return ob_get_clean();
    }

    /**
     * 将HTML页面转化为PDF格式的文档
     * @param [type] $filename
     * @param [type] $strContent
     * @return void
     */
    private function htmlToPdf($filename, $strContent) {
        if (empty($strContent) || empty($filename)) {
            return false;
        }
        Logger::createdir(dirname($filename));

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

        $pdf->writeHTML($strContent, true, false, true, false, '');
        //输出PDF
        $pdf->Output($filename, 'F');

        return true;
    }
}
