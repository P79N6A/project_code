<?php

namespace app\modules\balance\controllers;

use app\common\Common;
use app\common\Func;
use app\models\Whitelist;
use app\models\yyy\YiPlan;
use app\modules\balance\common\PaymentCommon;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;

if (!class_exists('PHPExcel')) {

    include Yii::$app->basePath.'/common/phpexcel/PHPExcel.php';
}
if (!class_exists('PHPExcel_Writer_Excel5')) {
    include Yii::$app->basePath.'/common/phpexcel/Excel5.php';
}

abstract class AdminController extends \app\common\BaseController
{
    public $layout = 'main';
    public $vvars; // 模板变量
    public $aid = 1;//项目id
    private $allowIP = ['127.0.0.1', '121.69.71.58', '124.193.149.180', '124.200.104.130','121.69.104.10'];

    public $redis;
    const PAGE_SIZE = 30; //每页是糯米
    /**
     * 初始化操作
     */
    public function init()
    {
        //限制访问ip
        $ipAllow = $this->chkIp();
        if (!$ipAllow) {
            echo '访问IP受限，请联系管理员！';
            die;
        }
        $this->vvars['nav'] = 'pay';
        // $this->aid = $aid;
        
    }

    public function getNowAid()
    {
        $filepath = Yii::$app->basePath . '/log/aid.txt';
        if (!file_exists($filepath)) {
            touch($filepath, 0775);

        }
        $aid = file_get_contents($filepath);
        return $aid;
    }

    public function setNowAid($aid)
    {
        $filepath = Yii::$app->basePath . '/log/aid.txt';
        file_put_contents($filepath, $aid);
    }

    /**
     * 返回session信息
     */
    public function getUser()
    {
        return Yii::$app->admin->identity;
    }

    /**
     * 只有登陆帐号才可以访问
     * 子类直接继承
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'user' => 'balance_admin',
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
    public function setVal($key, $val)
    {
        Yii::$app->session->set($key, $val);
    }

    //获取session
    public function getVal($key)
    {
        return Yii::$app->session->get($key);
    }

    //删除session
    public function delVal($key)
    {
        return Yii::$app->session->remove($key);
    }

    /**
     * 验证访问IP
     * @return bool
     */
    private function chkIp()
    {
        $ip = Func::get_client_ip();
        if (empty($ip)) {
            return false;
        }
        return in_array($ip, $this->allowIP);
    }

    // end

    /**
     * 显示结果信息
     * @param  int $res_code 错误码0 正确  | >0错误
     * @param  str $res_data结果 | 错误原因
     * @param  str $type json | redict
     * @param  str $redirect [description]
     * @return json | html
     */
    protected function showMessage($res_code, $res_data, $type = null, $redirect = null, $timeout = 3)
    {
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

    protected function get($name = null, $defaultValue = null)
    {
        return Yii::$app->request->get($name, $defaultValue);
    }

    protected function post($name = null, $defaultValue = null)
    {
        return Yii::$app->request->post($name, $defaultValue);
    }

    protected function returnFileJson($error_msg)
    {
        return json_encode(['msg' => $error_msg]);
    }
    protected function returnJson($code,$error_msg)
    {
        return json_encode(['code'=>$code,'msg' => $error_msg]);
    }

    /**
     * 下载模板
     * @throws \PHPExcel_Exception
     */
    protected function downlist_model_xls()
    {
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
        $objActSheet->getColumnDimension('D')->setWidth(30);
        $objActSheet->getColumnDimension('E')->setWidth(15);
        $objActSheet->getColumnDimension('F')->setWidth(15);
        $objActSheet->getColumnDimension('G')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(15);
        $objActSheet->getColumnDimension('H')->setWidth(30);
        $objActSheet->setCellValue('A1', '商户订单号');
        $objActSheet->setCellValue('B1', '姓名');
        $objActSheet->setCellValue('C1', '开户行');
        $objActSheet->setCellValue('D1', '银行卡号');
        $objActSheet->setCellValue('E1', '证件号 ');
        $objActSheet->setCellValue('F1', '手机号');
        $objActSheet->setCellValue('G1', '金额');
        $objActSheet->setCellValue('H1', '手续费');
        $objActSheet->setCellValue('I1', '订单状态');
        $objActSheet->setCellValue('J1', '账单日期');


        $outputFileName = "账单模板.xls";
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
    protected function downlist_model_xlss()
    {
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
        $objActSheet->setCellValue('A1', '订单号');
        $objActSheet->setCellValue('B1', '金额');
        $objActSheet->setCellValue('C1', '手续费');
        $objActSheet->setCellValue('D1', '持卡人姓名');
        $objActSheet->setCellValue('E1', '银行卡卡号 ');
        $objActSheet->setCellValue('F1', '开户行');
        $objActSheet->setCellValue('G1', '状态');
        $objActSheet->setCellValue('H1', '交易时间');


        $outputFileName = "账单模板.xls";
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
    public function errorStatus()
    {
        $oPaymentCommon = new PaymentCommon();
        return $oPaymentCommon->errorStatus();
    }

    /**
     * 业务类型
     *
     */

    public function getAid()
    {
        return [
            '1' => '一亿元',
            '2' => 'java项目',
            '3' => '商户贷',
            '4' => '花生米富',
            '8' => '豆荚贷',
            '9' => '米花花',
            '10' => '有信令',
            '12' => '有卡有钱',
            '14' => '一亿元新商场',
            '15' => '七天乐项目',
            '16' => '新借款系统',
            '17' => '先花一个亿'

        ];
    }

    /**
     * 债券类型
     *
     */

    public function getBondtype()
    {
        return [
            '1' => '7天',
            '2' => '14天',
            '3' => '21天',
            '4' => '28天',
            '5' => '42天',
            '6' => '56天',
            '7' => '63天',
            '8' => '84天',
            '9' => '112天',
            '10' => '140天',
            '11' => '168天',
            '12' => '336天'
        ];
    }

    /**
     * 回款通道
     * @return array
     */
    public function returnChannel()
    {
        return [

            '1' => '融宝',
            '2' => '宝付',
            '3' => '上海汇潮',
            '4' => '益马通付',
            '5' => '畅捷',
            '6' => '京东',
            '7' => '智融钥匙',
            '8' => '易宝',
            '9' => '连连',
        ];
    }
    /**
     * 公司主体
     * @return array
     */
    public function main_body(){

        return [
            '0' => '智融钥匙',
            '1' => '小小黛朵',
            '2' => '先花花',
            '3' => '智融钥匙',
            '4' => '萍乡海桐',
        ];

    }

    /**
     * 审核状态
     * @return array
     */
    public function auditingStatus()
    {
        return [
            '1' => '待审核',
            '2' => '审核通过',
            '3' => '审核失败',
        ];
    }

    public function getBedtype()
    {
        return [
            '1' => '红包领取',
            '2' => '好友分润',
            // '3' => '加息券',
        ];
    }

    /**
     * 下载逾期回款通道统计
     * @param $orderData
     * @throws \Exception
     */
    public function downlist_xls_channel($orderData)
    {
        $icount = count($orderData);

// 创建一个处理对象实例
        $objExcel = new \PHPExcel();

// 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);
        $channel_name = $this->returnChannel();
        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

//设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(30);
        $objActSheet->getColumnDimension('D')->setWidth(30);
        $objActSheet->getColumnDimension('E')->setWidth(30);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(30);
        $objActSheet->getColumnDimension('H')->setWidth(30);
        $objActSheet->getColumnDimension('I')->setWidth(30);
        $objActSheet->getColumnDimension('J')->setWidth(30);
        $objActSheet->getColumnDimension('K')->setWidth(30);
        $objActSheet->getColumnDimension('L')->setWidth(30);
        $objActSheet->getColumnDimension('M')->setWidth(30);
        $objActSheet->getColumnDimension('N')->setWidth(30);
        $objActSheet->getColumnDimension('O')->setWidth(30);
        $objActSheet->getColumnDimension('P')->setWidth(30);
        $objActSheet->setCellValue('A1', '序号');
        $objActSheet->setCellValue('B1', '商户订单号');
        $objActSheet->setCellValue('C1', '回款通道id');
        $objActSheet->setCellValue('D1', '通道商编号');
        $objActSheet->setCellValue('E1', '通道');
        $objActSheet->setCellValue('F1', '姓名');
        $objActSheet->setCellValue('G1', '开户行');
        $objActSheet->setCellValue('H1', '银行卡号');
        $objActSheet->setCellValue('I1', '证件号');
        $objActSheet->setCellValue('J1', '收款人手机号');
        $objActSheet->setCellValue('K1', '第三方交易金额');
        $objActSheet->setCellValue('L1', '平台金额');
        $objActSheet->setCellValue('M1', '手续费');
        $objActSheet->setCellValue('N1', '账单日期');
        $objActSheet->setCellValue('O1', '状态');
        $objActSheet->setCellValue('P1', '业务名称');
        $number = 0;
        for ($i = 0; $i < $icount; $i++) {
            $passageway_type = ArrayHelper::getValue($orderData[$i], 'passageway_type');
            $sort = ArrayHelper::getValue($orderData[$i], 'sort');
            if ($passageway_type == 1) {
                $passageway_status = '第三方成功';
            } elseif ($passageway_type == 2) {
                $passageway_status = '平台成功';
            } elseif ($passageway_type & 3) {
                $passageway_status = "双方成功";
            }
            $sorts = '正常回款';
            if($sort == 0){
                $sorts = '正常回款';
            }
            if($sort == 98){
                $sorts = '逾期';
            }
            if($sort == 99){
                $sorts = '展期';
            }
            if($sort == 101){
                $sorts = '展期';
            }
            if($sort == 102){
                $sorts = '正常回款';
            }
            $orderid = ArrayHelper::getValue($orderData[$i], 'client_id');
            $aid = ArrayHelper::getValue($orderData[$i], 'aid');
            $aids = $this->getAid($aid);
            $aidName = !empty($aids[$aid])?$aids[$aid]:'未知';
            $number++;
            $objActSheet->setCellValue('A' . ($i + 2), $number);
            $objActSheet->setCellValue('B' . ($i + 2),substr_replace($orderid," ",7,0 ));//防止数字科学计数法
            $objActSheet->setCellValue('C' . ($i + 2),$aidName);
            $objActSheet->setCellValue('D' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'series'));
            $objActSheet->setCellValue('E' . ($i + 2), ArrayHelper::getValue($channel_name, ArrayHelper::getValue($orderData[$i], 'return_channel')));
            $objActSheet->setCellValue('F' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'name'));
            $objActSheet->setCellValue('G' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'opening_bank'));
            $objActSheet->setCellValue('H' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'guest_account'));
            $objActSheet->setCellValue('I' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'identityid'));
            $objActSheet->setCellValue('J' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'user_mobile'));
            $objActSheet->setCellValue('K' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'amount'));
            $objActSheet->setCellValue('L' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'payment_amount'));
            $objActSheet->setCellValue('M' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'settle_fee'));
            $objActSheet->setCellValue('N' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'payment_date'));
            $objActSheet->setCellValue('O' . ($i + 2), $passageway_status);
            $objActSheet->setCellValue('P' . ($i + 2), $sorts);

        }
        $outputFileName = date('Y-m-d', time()) . "账单详情" . ".xls";
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
     * 业务名称
     * @return array
     */
    public function businessName()
    {
        return [
            '1'         => '一亿元',
            '9'         => '米花花',
        ];
    }

    /**
     * 债权类型
     * @return array
     */
    public function debtType()
    {
        return [
            '7'            => '7',
            '14'            => '14',
            '28'            => '28',
            '56'            => '56',
            '84'            => '84'
        ];
    }

    /**
     * 贴息类型
     * @return array
     */
    public function discountType()
    {
        return [
            '1'         => '理财红包',
            '2'         => '现金红包',
            '3'         => '加息券',
            '4'         => '好友分润',
            '5'         => '还款贴息',
        ];
    }

    /**
     * 类型
     * @return array
     */
    public function mfType()
    {
        return [
            '1'         => '充值',
            '2'         => '提现'
        ];
    }

    /**
     * 逾期类型
     * @return array
     */
    public function overdueType()
    {
        return [
            '1'         => 'M1',
            '2'         => 'M2',
            '3'         => 'M3',
            '4'         => '坏账',
        ];
    }

    /**
     * 分其类型
     */
    public function typesOfStages()
    {
        return [
            '1'     => '单期',
            '2'     => '分期',
        ];
    }

    /**
     * 资金方
     */
    public function capitalSide()
    {
        $capital_side = YiPlan::capitalSide();
        $data_set = [];
        if ($capital_side){
            foreach($capital_side as $value){
                $k = ArrayHelper::getValue($value, 'fund', 0);
                $data_set[$k] = ArrayHelper::getValue($value, "name");
            }
        }
        return $data_set;
    }

    /**
     * 出款方式
     */
    public function wayOfPayment()
    {
        return [
            '1'     => '体外',
            '10'    => '体内',
        ];
    }

    public function serverDown($orderData)
    {
        $icount = count($orderData);

        // 创建一个处理对象实例
        $objExcel = new \PHPExcel();

        // 创建文件格式写入对象实例, uncomment
        $objWriter = new \PHPExcel_Writer_Excel5($objExcel);

        $objExcel->setActiveSheetIndex(0);
        $objActSheet = $objExcel->getActiveSheet();

        //设置当前活动sheet的名称
        $objActSheet->setTitle('当前sheetname');
        $objActSheet->getColumnDimension('A')->setWidth(30);
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(30);

        $objActSheet->setCellValue('A1', '账单日期');
        $objActSheet->setCellValue('B1', '订单号');
        $objActSheet->setCellValue('C1', '金额');

        for ($i = 0; $i < $icount; $i++) {
            $objActSheet->setCellValue('A' . ($i + 2), date("Y-m-d", strtotime(ArrayHelper::getValue($orderData[$i], 'create_time'))));
            $objActSheet->setCellValue('B' . ($i + 2), ArrayHelper::getValue($orderData[$i], 'order_id'));
            $objActSheet->setCellValue('C' . ($i + 2), number_format(ArrayHelper::getValue($orderData[$i], 'settle_amount'), 2));

        }
        $outputFileName = date('Y-m-d', time()) . "手续费前置手续费户账单" . ".xls";
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
