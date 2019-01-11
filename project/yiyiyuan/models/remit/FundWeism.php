<?php

namespace app\models\remit;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Address;
use app\models\news\Areas;
use app\models\news\CommonNotify;
use app\models\news\Money_limit;
use app\models\news\User_password;
use app\models\news\User_remit_list;
use Yii;
use yii\helpers\ArrayHelper;
use app\commands\contract\MakecontractDo;

if (!class_exists('TCPDF')) {
//    include '/tcpdf/tcpdf.php';
    if (SYSTEM_ENV == 'prod') {
        include '/data/wwwroot/weixin/tcpdf/tcpdf.php';
//        include  Yii::$app->basePath. "\\tcpdf\\tcpdf.php";
    } else {
        include '/data/wwwroot/yiyiyuan_test/tcpdf/tcpdf.php';
        //       include '/Users/lunima/xianhuahua/yiyiyuan/tcpdf/tcpdf.php';
//        include 'd:\yiyiyuan\yyy_online\tcpdf/tcpdf.php';
//        include 'D:\phpStudy\WWW\yyymobile\tcpdf/tcpdf.php';
    }
}

class FundWeism implements CapitalInterface {

    /**
     * 微神马出款接口
     * @param $oRemit
     * @return array
     */
    public function pay($oRemit) {
        $userExtend = $oRemit->userExtend;
        if (empty($userExtend) || empty($userExtend->company_area) || empty($userExtend->company) || empty($userExtend->telephone)) {
            return ['status' => 'FAIL', 'res_code' => 'emptyextend', 'res_msg' => '用户附属信息不全'];
        }
        if (empty($oRemit->password) || empty($oRemit->password->iden_address)) {
            return ['status' => 'FAIL', 'res_code' => 'emptypass', 'res_msg' => '用户password表信息不全'];
        }
        if (empty($oRemit->contacts)) {
            return ['status' => 'FAIL', 'res_code' => 'emptycontacts', 'res_msg' => '联系人表信息为空'];
        }
//        $hitresult = $this->ownRule($oRemit);
//        if ($hitresult) {
//            return ['status' => 'FAIL', 'res_code' => 'hitruleerror', 'res_msg' => '出款总额超限'];
//        }
        $area = Areas::getProCityArea($oRemit->userExtend->company_area);
        $cityArea = Areas::findOne($area['city']);
        $city = $cityArea->name;
        $provinceArea = Areas::findOne($area['province']);
        $province = $provinceArea->name;
        $host = Yii::$app->params['app_url'] . '/pdf/';
        //生成合同
        $uri = $this->makeContract($oRemit);
        if (!$uri) {
            return ['status' => 'FAIL', 'res_code' => 'wsmerr', 'res_msg' => '生成协议失败'];
        }
        $params = [
            'req_id' => $oRemit->order_id, //商户订单号
            'xm' => $oRemit->user->realname, //借款人姓
            'sfzh' => $oRemit->user->identity, //身份证号
            'sjh' => $oRemit->user->mobile, //手机号
            'sflx' => '1', //身份类型
            'dzxx' => $oRemit->password->iden_address, //身份证地址信息
            'hyzk' => $this->getMarrige($oRemit->userExtend->marriage), //婚姻状况
            'jkzk' => '1', //健康状况
            'zgxl' => $this->getEdu($oRemit->userExtend->edu), //最高学历
            'gsmc' => $oRemit->userExtend->company, //公司名称
            'gsdh' => $oRemit->userExtend->telephone, //公司电话
            'lxrxm1' => $oRemit->contacts->relatives_name, //联系人1姓名
            'lxrdh1' => $oRemit->contacts->phone, //联系人1电话
            'lxrgx1' => (string) $oRemit->contacts->relation_family, //联系人1关系
            'dkyt' => 1, //贷款用途
            'yhklx' => $this->bank_type($oRemit->bank->type), //银行卡类
            'kh' => $oRemit->bank->card, //卡号
            'khh' => $oRemit->bank->bank_abbr, //开户行
            'sqje' => $oRemit->real_amount, //申请金额（元）
            'qx' => 1, //期限
            'ts' => $oRemit->loan->days,
            'qyszsheng' => $province, //签约所在
            'qyszshi' => $city, //约所在市
            'fkxx' => $this->getFkxx($oRemit->user), //风控信息
            'on_line' => '1', //线上标识
            'callbackurl' => Yii::$app->params['remit_repay'],
            'jjfwxyckdz' => $host . $uri,
        ];
        $openApi = new Apihttp();
        $res = $openApi->weiShenma($params);

        $res_code = ArrayHelper::getValue($res, 'res_code');
        $res_msg = ArrayHelper::getValue($res, 'res_msg');

        //4. 处理流程
        if ($res_code == '0000') {
            $status = 'DOREMIT';
        } else {
            $fail_codes = $this->getFails();
            if (in_array($res_code, $fail_codes)) {
                // 明确错误时
                $status = 'FAIL';
            } else {
                // 不明确错误挂起
                $status = NULL;
            }
        }
        $array = ['status' => $status, 'res_code' => $res_code, 'res_msg' => $res_msg];
        Logger::dayLog('fundweism', $oRemit->id, $array);
        //5. 加入到通知表中
        $r = (new CommonNotify)->addNotify($oRemit, $status);
        return $array;
    }

    /**
     * 明确错误码
     * @return array
     */
    public function getFails() {
        return [
            '1030001', //传入的值不能为空
            '1030002', //**不能为空
            //'1030003', //**（订单号）不存在
            '1030004', //**（订单号）已提交申请
            '1030005', //节假日不接受推送用户
            '1030007', //已超过日最高限额
            '1030008', //暂不支持该银行卡
        ];
    }

    /**
     * 婚姻映射
     * @param $marriage
     * @return int
     */
    private function getMarrige($marriage) {
        if (in_array($marriage, [1, 2, 3, 4])) {
            return (string) $marriage;
        }
        return '1';
    }

    /**
     * 学历映射
     * @param $edu
     * @return int
     */
    private function getEdu($edu) {
        switch ($edu) {
            case '1':
                $str = '4';
                break;
            case '2':
                $str = '3';
                break;
            case '3':
                $str = '2';
                break;
            default:
                $str = '10';
                break;
        }
        return $str;
    }

    /**
     * 借款理由映射关系
     * @param $desc
     * @return string
     */
    private function desc_common($desc) {
        // 1.消费2.汽车3.医美4.旅游5.教育6. 3C 7.家装 8.租房9.租赁 10. 农业
        if ($desc == '购买原材料') {
            return '1';
        } elseif ($desc == '进货') {
            return '2';
        } elseif ($desc == '购买设备') {
            return '3';
        } elseif ($desc == '资金周转') {
            return '4';
        } elseif ($desc == '个人或家庭消费') {
            return '6';
        } elseif ($desc == '学习') {
            return '5';
        } elseif ($desc == '租房') {
            return '8';
        } elseif ($desc == '物流运输') {
            return '7';
        } elseif ($desc == '资金周转') {
            return '9';
        } else {
            return '10';
        }
    }

    /**
     * 银行卡类型映射
     * @param $type
     * @return string
     */
    private function bank_type($type) {
        if ($type == 0) {
            return '1';
        } elseif ($type == 1) {
            return '2';
        } else {
            return '1';
        }
    }

    private function getFkxx($userInfo) {
        $address = Address::find()->where(['user_id' => $userInfo->user_id])->one();
        $arr = [
            "姓名" => $userInfo->realname,
            "性别" => $sex = substr($userInfo->identity, -2, 1) % 2 ? '女' : '男',
            "地址类别" => '1',
            "设备MAC" => "",
            "坐标经纬度" => !empty($address) ? $address->latitude . ',' . $address->longitude : "",
            "社交账号" => $userInfo->extend->email,
            "社交账号类型" => "3",
            "月化利率" => "1.5%",
            "地址信息是否校验" => "1",
            "身份证多项信息是否验证" => "1",
            "身份证多项信息验证渠道" => "天行数科",
            "工作信息是否验证" => "0",
            "工作信息验证渠道" => "",
            "学历/学籍信息是否验证" => "0",
            "学历/学籍信息验证渠道" => "",
            "联系人信息是否验证" => "1",
            "联系人信息验证渠道" => "系统+人工",
            "通讯录是否读取" => "1",
            "是否获取电信运营商数据" => "0",
            "是否获取电商数据" => "0",
            "是否获取社保数据" => "0",
            "是否获取公积金数据" => "0",
            "身份证号、姓名、银行卡号一致性验证" => "1",
            "身份证号、姓名、银行卡号一致性验证渠道" => "天行数科",
            "网贷黑名单是否命中" => '0',
            "网贷黑名单验证渠道" => '百融/同盾/百度',
            "法院黑名单是否命中" => '0',
            "法院黑名单验证渠道" => '百融/同盾/百度',
            "公安信息黑名单是否命中" => '0',
            "公安信息黑名单验证渠道" => '百融/同盾/百度',
            "第三方支付交易编号" => '',
        ];
        return json_encode($arr, JSON_UNESCAPED_UNICODE);
    }

    //生成合同
    private function makeContract($oRemit) {
        $loan = $oRemit->loan;
        if ($loan->create_time) {
            $year = date('Y', strtotime($loan->create_time));
            $month = date('m', strtotime($loan->create_time));
            $day = date('d', strtotime($loan->create_time));
        } else {
            $year = date('Y');
            $month = date('m');
            $day = date('d');
        }
        $url = Yii::$app->params['app_url'] . "/new/agreeloan/wsm?loan_id=" . $oRemit->loan_id;
        $rootdir = dirname(Yii::$app->basePath);
        $filepath = $rootdir . '/share/pdf/wsm/' . $year . '/' . $month . '/' . $day;
        Logger::createdir($filepath);
        $contract = 'loan_' . $loan->loan_no;
        $filename = $filepath . '/' . $contract . '.pdf';

        $makeDo = new MakecontractDo();
        $ret = $makeDo->wsmrunById($oRemit->loan_id, $filename);
        if (!$ret) {
            return FALSE;
        }
//        $this->htmltoPdf($url, $filename);
        return 'wsm/' . $year . '/' . $month . '/' . $day . '/' . $contract . '.pdf';
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

    /**
     * 每笔规则限制
     * @return bool true:触犯规则 false:未触犯规则
     */
    private function ownRule($oRemit) {
        $oRemitList = new User_remit_list();
        $oMoneyLimit = new Money_limit;
        $start_time = date('Y-m-d 00:00:00');
        $end_time = date('Y-m-d 23:59:59');
        $MaxAmount = $oMoneyLimit->todayTimeMaxMoney(0, User_remit_list::FUND_WEISM);
        $amount = $oRemitList->pushMoney($start_time, $end_time, [User_remit_list::FUND_WEISM], 0, 2);
        if ($amount + $oRemit->real_amount > $MaxAmount) {
            return true;
        }
        return FALSE;
    }

    /**
     * 微神马资金方出款限制
     * @param $channel_id
     * @return bool
     */
    public function hitRule() {
        $time = date('Y-m-d h:i:s');
        $time_28 = date('Y-m-d h:i:s', strtotime('-28 day'));
        $date = date('Y-m-d');
        $oMoneyLimit = new Money_limit;
        $oRemitList = new User_remit_list;

        //1：周六周日不推单
        if (date("w") == 6 || date("w") == 0) {
            return true;
        }

        //2：推单时间和金额限额
        if ($time > $date . ' 16:00:00') {//当天设置的开始时间之前或者16点之后不推单
            return true;
        }
//        $todaySuccessAmount = $oRemitList->todaySuccessMoney(0, User_remit_list::FUND_WEISM);
//        $todayMaxAmount = $oMoneyLimit->todayTimeMaxMoney(0, User_remit_list::FUND_WEISM);
//        if (empty($todayMaxAmount)) {
//            Logger::dayLog("fundrule/weism", $todayMaxAmount, "没有符合出款的时间设置");
//            return true;
//        }
//        if ((bccomp(floatval($todaySuccessAmount), floatval($todayMaxAmount), 2) == 1)) {
//            Logger::dayLog("fundrule/weism", $todaySuccessAmount, $todayMaxAmount, "当日通道出款金额超限");
//            return true;
//        }
        //3：28天内大于5000万
//        $amount_28 = $oRemitList->pushMoney($time_28, $time, [User_remit_list::FUND_WEISM], 0);
//        if ($amount_28 > 50000000) {
//            return true;
//        }

        return false;
    }

    public function isSupport($oLoan) {
        $time = date('Y-m-d h:i:s');
        $date = date('Y-m-d');

        $loan = $oLoan->loan;

        if (!$loan) {
            return false;
        }

        if (!empty($oLoan->remit)) {
            $fundIds = array_map(function($record) {
                return $record->attributes['fund'];
            }, $oLoan->remit);
            if (in_array(CapitalInterface::WEISM, $fundIds)) {
                return false;
            }
        }
        //周六周日不推单
        if (date("w") == 6 || date("w") == 0) {
            return false;
        }
        if ($loan->amount != $loan->withdraw_fee * 10) {
            return false;
        }
        if ($loan->days != 28) {
            return false;
        }
        if ($loan->is_calculation == 0) {
            return false;
        }
        if ($loan->amount > 3000 || $loan->amount < 1000) {
            return false;
        }
        //推单时间
        if ($time > $date . ' 16:00:00') {//当天设置的开始时间之前或者16点之后不推单
            return false;
        }
        return true;
    }

}
