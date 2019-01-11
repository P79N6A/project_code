<?php

namespace app\commonapi;

class Keywords {

    public static function getBlackUserId() {
        return [1105150, 1120049, 1198815, 1199637, 1200976, 1201100, 1202237, 1203353, 1207765, 1207825, 1209410, 1209621, 1210247, 1219605, 1219630, 1224331, 1227338, 1227611, 1236675, 1237239, 1238087, 1263869, 1263532, 1261385, 1263803, 1255018, 1230202, 1258575, 1202123, 1259719, 1235574, 1287966, 1235574, 1287966, 1251593, 1270562, 1254108, 1187092, 1284008, 1291383, 1289175, 749888, 1350449, 1289624, 1266708, 1291368, 1291431, 1274119, 1188452, 1265683, 1418525, 1415201, 1231750];
    }

    /**
     * 借款敏感词
     * @return array
     */
    public static function getLoanSensitiveword() {
        return array('呵呵', '哈哈', '测试', '嫖娼', '吸毒', '去你妈', 'TM');
    }

    /**
     * 绑卡支持列表
     * 信用卡：
     * 中国银行BOC、农业银行ABC、工商银行ICBC、建设银行CCB、交通银行BCM、邮政储蓄银行POST、中信银行ECITIC、华夏银行HXB、民生银行CMBC、
     * 兴业银行CIB、光大银行CEB、平安银行PINGAN、招商银行CMB、广发银行GDB、北京银行BOB、浦发银行SPDB、上海银行SHB
     * 借记卡：　
     * 中国银行BOC、农业银行ABC、工商银行ICBC、建设银行CCB、交通银行BCM、邮政储蓄银行POST、中信银行ECITIC、华夏银行HXB、民生银行CMBC、
     * 兴业银行CIB，平安银行PINGAN，广发银行GDB，光大银行CEB，北京银行BOB、招商银行CMB、浦发银行SPDB、上海银行SHB,江苏省农村信用社联合社(暂时不加)、
     * @return type
     */
    public static function getBankAbbr() {
        return [
            '0' => ['BOC', 'ABC', 'ICBC', 'CCB', 'POST', 'ECITIC', 'HXB', 'CMBC', 'CIB', 'CEB', 'PINGAN', 'CMB', 'GDB', 'BOB', 'SPDB', 'SHB'], //借记卡
            '1' => ['BOC', 'ABC', 'ICBC', 'CCB', 'BCM', 'POST', 'ECITIC', 'HXB', 'CMBC', 'CIB', 'PINGAN', 'GDB', 'CEB', 'BOB', 'CMB', 'SPDB', 'SHB'], //信用卡
        ];
    }

    /**
     * 出款卡支持列表 只有借记卡
     */
    public static function getOutBankAbbr() {
        return [
            '0' => ['BOC', 'ABC', 'ICBC', 'CCB', 'BCM', 'POST', 'HXB', 'CMBC', 'CIB', 'CEB', 'PINGAN', 'CMB', 'GDB', 'BOB', 'SPDB', 'SHB'],
        ];
    }

    /**
     * 存管出款卡支持列表 只有借记卡
     */
    public static function getOutBankAbbrDe() {
        return [
            '0' => ['ICBC', 'BOC', 'CCB', 'CIB', 'CEB', 'ETIC', 'PGAN', 'MBC', 'GDB', 'PDB', 'BCM', 'CMB',],
        ];
    }

    /**
     * 获取用户职位
     */
    public static function getPosition() {
        return [
            '1' => ['一般管理人员', 'B2905'],
            '2' => ['一般干部', 'B2907'],
            '3' => ['科员', 'B2908'],
            '4' => ['一般员工', 'B2909'],
            '5' => ['私营业主', 'B2912'],
            '6' => ['一般主管/团队长', 'B2915'],
            '7' => ['其他', 'B2910'],
        ];
    }

    /**
     * 学历
     */
    public static function getEdu() {
        return [
            '1' => ['大专', 'B0304'],
            '2' => ['本科', 'B0305'],
            '3' => ['硕士及以上', 'B0306'],
        ];
    }

    /**
     * 联系人关系
     */
    public static function getRelation() {
        return [
            '1' => ['父母', '配偶'],
            '2' => ['朋友', '同事', '兄弟', '姐妹', '其他'],
        ];
    }

    /**
     * 婚姻状态
     * @return type
     */
    public static function getMarriage() {
        return [
            '1' => ['未婚', 'B0501'],
            '2' => ['已婚', 'B0502'],
            '3' => ['丧偶', 'B0503'],
            '4' => ['离异', 'B0504'],
            '5' => ['再婚', 'B0505'],
            '6' => ['未知', 'B0506'],
        ];
    }

    /**
     * H5借款理由
     * @return type
     */
    public static function getLoanDesc() {
        return [
            '1' => ['购买原材料', 'F1103'],
            '2' => ['进货', 'F1105'],
            '3' => ['购买设备', 'F1108'],
            '4' => ['购买家具或家电', 'F1109'],
            '5' => ['学习', 'F1111'],
            '6' => ['个人或家庭消费', 'F1112'],
            '7' => ['资金周转', 'F1113'],
            '8' => ['租房', 'F1114'],
            '9' => ['物流运输', 'F1115'],
            '10' => ['其他', 'F1199'],
        ];
    }

    /**
     * app端借款理由
     * @return type
     */
    public static function getAppLoanDesc() {
        return [
            ['desc_key'=>'F1108','desc'=>'购买设备'],
            ['desc_key'=>'F1109','desc'=>'购买家具或家电'],
            ['desc_key'=>  'F1211', 'desc'=>'购买服饰'],
            ['desc_key' => 'F1213', 'desc' => '购买生活用品'],
            ['desc_key' => 'F1213', 'desc' => '购买电子产品'],
            ['desc_key' => 'F1214', 'desc' => '购买食品'],
            ['desc_key' => 'F1215', 'desc'=>'消费'],
        ];
    }

    /**
     * 行业
     */
    public static function getIndustry() {
        return [
            '1' => ['生产,加工,制造业', 'B1018'],
            '2' => ['批发零售,商业贸易,租赁', 'B1019'],
            '3' => ['工程类(建筑业、装修、园林绿化等)', 'B1020'],
            '4' => ['住宿餐饮', 'B1021'],
            '5' => ['文化科教(出版社,私立学校,培训机构,早教班,辅导班等)', 'B1022'],
            '6' => ['居民服务(美容美发、健身房、车辆美容和维修、家政服务、管道疏通等)', 'B1023'],
            '7' => ['IT 网络,计算机,通信', 'B1024'],
            '8' => ['运输业', 'B1026'],
            '9' => ['养殖业,种植业', 'B1027'],
            '10' => ['广告,娱乐', 'B1028'],
            '11' => ['中介,外包', 'B1029'],
            '12' => ['采矿业', 'B1030'],
            '13' => ['电力、热力、燃气及水生产和供应业', 'B1031'],
            '14' => ['科学研究和技术服务业', 'B1032'],
            '15' => ['零售业', 'B1033'],
            '16' => ['水利、环境和公共设施管理业', 'B1034'],
            '17' => ['卫生和社会工作', 'B1035'],
            '18' => ['文化、体育和娱乐业', 'B1036'],
        ];
    }

    /**
     * 职业
     */
    public static function getProfession() {
        return [
            '1' => ['一般职业', 'F12301'],
            '2' => ['工厂', 'F1230102'],
            '3' => ['农牧业', 'F12302'],
            '4' => ['渔业', 'F12303'],
            '5' => ['木材、森林业', 'F12304'],
            '6' => ['矿业、采石业', 'F12305'],
            '7' => ['交通运输业', 'F12306'],
            '8' => ['旅游业', 'F1230701'],
            '9' => ['餐饮业', 'F1230703'],
            '10' => ['制造业', 'F12309'],
            '11' => ['医院、诊所', 'F1231101'],
            '12' => ['娱乐业', 'F12312'],
            '13' => ['文教', 'F12313'],
            '14' => ['教育机构', 'F1231301'],
            '15' => ['邮政', 'F1231501'],
            '16' => ['零售', 'F1231602'],
            '17' => ['服务业', 'F12318'],
            '18' => ['家庭主妇', 'F123190101'],
            '19' => ['保姆、家庭护理', 'F123190102'],
            '20' => ['治安人员', 'F12320'],
            '21' => ['体育', 'F12322'],
            '22' => ['资讯', 'F12323'],
            '23' => ['个体商贩', 'F123240101'],
            '24' => ['其它', 'F12324'],
        ];
    }

    /**
     * 还款渠道
     */
    public static function getRepaymentChannel() {
        return [
            '101' => 3, //易宝投资通
            '102' => 2, //易宝一键支付
            '104' => 6, //连连支付（一亿元）
            '107' => 9, //宝付认证支付（一亿元）
            '108' => 10, //连连认证支付（花生米富）
            '109' => 11, //易宝代扣
            '110' => 12, //融宝快捷（一亿元）
            '112' => 13, //融宝快捷（米富）
            '113' => 14, //宝付（一亿元）
            '114' => 15, //宝付（米富）
            '128' => 16, //融宝(逾期)
            '123' => 17, //宝付（逾期）
            '117' => 18, //畅捷
            '131' => 19, //畅捷快捷
            '105' => 20, //融宝快捷（花生米富）
            '106' => 21, //宝付代扣
            '139' => 22, //新微信
            '140' => 23, //新支付宝 废弃
            '141' => 24, //新微信（逾期）
            '142' => 25, //新支付宝（逾期）
            '147' => 26, //存管还款
            '150' => 27, //存管还款（新）
            '153' => 28, //支付宝（新）
            '155' => 29, //易倍加微信支付
            '156' => 30, //霍尔果斯普罗米-支付宝
            '157' => 31, //畅捷快捷
            '158' => 32, //京东快捷-天津有信
            '159' => 33, //京东快捷-霍尔果斯
            '160' => 34, //京东快捷-萍乡
            '161' => 35, //一麻袋支付宝
            '162' => 36, //畅捷支付宝
            '167' => 37, //融宝快捷
            '168' => 38, //融宝协议支付
            '169' => 39, //畅捷	畅捷快捷(萍乡智海融数金融)
            '170' => 40, //支付宝-萍乡	一麻袋(萍乡海桐)
            '171' => 41, //宝付	宝付协议支付(萍乡海桐)
            '172' => 42, //宝付	宝付快捷支付(萍乡海桐)
            '163' => 43, //宝付协议支付(天津有信)
            '166' => 44, //畅捷支付宝(天津)
            '173' => 45, //新商城代付
            '174' => 46, //支付宝-萍乡海桐（畅捷支付宝）
            '175' => 47, //融宝（融宝协议支付-萍乡海桐）
            '176' => 48, //融宝（融宝快捷支付-萍乡海桐）
            '177' => 49, //畅捷协议支付（萍乡海桐）
            '181' => 50, //宝付协议支付（萍乡海桐2）
            '182' => 51, //宝付快捷认证支付（萍乡海桐2）
            '183' => 52, //宝付国科金计（畅捷）
        ];
    }

    /**
     * 还款渠道后台显示
     */
    public static function showRepaymentChannel() {
        return [
            '1' => '线下',
            '2' => '易宝一键支付', //易宝一键支付
            '3' => '易宝投资通', //易宝投资通
            '4' => '微信支付',
            '5' => '支付宝',
            '6' => '连连支付（一亿元）', //连连支付（一亿元）
            '7' => '微信（逾期）', //微信逾期还款
            '8' => '支付宝（逾期）', //支付宝逾期还款
            '9' => '宝付认证支付（一亿元）', //宝付认证支付（一亿元）
            '10' => '连连认证支付（花生米富）', //连连认证支付（花生米富）
            '11' => '易宝代扣', //易宝代扣
            '12' => '融宝快捷（一亿元）', //融宝快捷（一亿元）
            '13' => '融宝快捷（米富）', //融宝快捷（一亿元）
            '14' => '宝付快捷（一亿元）', //融宝快捷（一亿元）
            '15' => '宝付快捷（米富）', //融宝快捷（一亿元）
            '16' => '融宝(逾期)',
            '17' => '宝付(逾期)',
            '18' => '畅捷出款',
            '19' => '畅捷快捷',
            '20' => '存管', //原新支付宝
            '21' => '新支付宝(逾期)', //废弃
            '22' => '新微信',
            '23' => '新支付宝',
            '24' => '新微信(逾期)',
            '25' => '新支付宝(逾期)',
            '26' => '存管还款',
            '27' => '存管还款（新）',
            '28' => '支付宝（新）',
            '29' => '易倍加微信支付',
            '30' => '霍尔果斯普罗米-支付宝',
            '31' => '畅捷快捷',
            '32' => '京东快捷-天津有信',
            '33' => '京东快捷-霍尔果斯',
            '34' => '京东快捷-萍乡',
            '35' => '一麻袋支付宝',
            '36' => '畅捷支付宝',
            '37' => '融宝快捷',
            '38' => '融宝协议支付',
            '39' => '畅捷快捷(萍乡智海融数金融)',
            '40' => '支付宝-萍乡',
            '41' => '宝付协议支付',
            '42' => '宝付快捷支付',
            '43' => '宝付协议支付(天津有信)',
            '44' => '畅捷支付宝(天津)',
            '45' => '新商城代付',
            '46' => '支付宝-萍乡海桐（畅捷支付宝）',
            '47' => '融宝（融宝协议支付-萍乡海桐）',
            '48' => '融宝（融宝快捷支付-萍乡海桐）',
            '49' => '畅捷协议支付（萍乡海桐）',
            '50' => '宝付协议支付（萍乡海桐2）',
            '51' => '宝付快捷认证支付（萍乡海桐2）',
            '52' => '宝付国科金计（畅捷）',
        ];
    }

    /**
     * 出款状态
     */
    public static function getLoanRemitStatus() {
        return [
            'INIT' => '出款中',
            'LOCK' => '出款中',
            'DOREMIT' => '出款中',
            'SUCCESS' => '成功',
            'FAIL' => '失败',
            'REJECT' => '驳回',
        ];
    }

    /**
     * 借款来源
     * @return array
     */
    public static function getLoanSource() {
        return [
            '1' => '公众号',
            '2' => 'ios',
            '3' => 'ios',
            '4' => 'andraoid',
            '5' => 'H5',
            '6' => '百融',
            '8' => '融360',
            '9' => '借点钱',
            '10' => '现金白卡',
            '11' => '借了吗',
            '12' => '借钱用',
            '13' => '360贷款导航',
            '14' => '有鱼',
        ];
    }

    /**
     * 出款状态
     * @return array
     */
    public static function getLoanStatus() {
        return array(
            'INIT' => '出款中', //初始,
            'WILLAUTHED' => '待活体认证',
            'AUTHED' => '审核通过',
            'PREREMIT' => '出款中', //'出款预处理',
            'WAITREMIT' => '等待处理',
            'WILLREMIT' => '等待出款',
            'DOREMIT' => '出款中',
            'SUCCESS' => '出款成功',
            'FAIL' => '出款失败',
            'REJECT' => '驳回出款',
            'LOCK' => '出款中'
        );
    }

    /**
     * 驳回理由弹层
     * @return array
     */
    public static function getRejectReason() {
        return [
            '1' => '请30天后再次尝试借款',
            '2' => '请一周后再次尝试发起借款',
            '3' => '借款申请暂时无法通过，期待下次为您服务'
        ];
    }

    /**
     * 出款类型
     * @param $payment_channel
     * @param string $fund
     * @return array
     */
    public static function typeOfPayment($payment_channel, $fund = '') {
        $fundarray = self::fund();
        $payment_channel_data = [
            '1' => '新浪出款',
            '2' => '中信出款',
            '4' => '恒丰出款',
            '5' => '广发出款',
            '7' => '存管出款',
            '110' => '融宝一亿元',
            '112' => '融宝米富',
            '113' => '宝付（米富）',
            '114' => '宝付（一亿元）',
            '117' => '畅捷出款',
            '131' => '畅捷快捷',
        ];
        if (!empty($fundarray[$fund])) {
            if (!empty($payment_channel_data[$payment_channel])) {
                return $payment_channel_data[$payment_channel] . "(" . $fundarray[$fund] . ")";
            } else {
                return "未知" . "(" . $fundarray[$fund] . ")";
            }
        } else {
            $fund = "(未知)";
            if (!empty($payment_channel_data[$payment_channel])) {
                return $payment_channel_data[$payment_channel];
            } else {
                return "未知" . $fund;
            }
        }
    }

    /**
     * 收入映射
     * @param $money_value
     * @return string
     */
    public static function incomeValue($money_value) {
        if ($money_value < 2000) {
            return "2000以下";
        } elseif ($money_value >= 2000 && $money_value <= 2999) {
            return "2000-2999";
        } elseif ($money_value >= 3000 && $money_value <= 3999) {
            return "3000-3999";
        } elseif ($money_value >= 4000 && $money_value <= 4999) {
            return "4000-4999";
        } else {
            return "5000以上";
        }
    }

    /**
     * 重复连点时间
     * 1、发起借款 30秒限制，2、确认借款10分钟限制
     */
    public static function repeatTime() {
        $array = [
            '1' => 30,
            '2' => 10 * 60,
        ];
        return $array;
    }

    /**
     * 后台出款通道
     */
    public static function outletPassage() {
        return "
        html += '<input type=\"radio\" name=\"payment_channel\" value=\"0\" style=\"display: none;\" checked=\"checked\" /><p>';
       ";
    }

    /**
     * 信调测试手机号，不走注册决策，借款决策，运营商
     * @return type
     */
    public static function xindiao() {
        return ['15001398996'];
    }

    /**
     * 资方：1、花生米福，2、玖富，3、联交所，4、金联储, 5、小诺， 6、微神马， 10、银行存管
     * @return array
     */
    public static function fund() {
        return [
            '1' => '花生米富',
            '2' => '玖富',
            '3' => '联交所',
            '4' => '金联储',
            '5' => '小诺',
            '6' => '微神马',
            '10' => '银行存管',
            '11' => '其他'
        ];
    }

    /**
     * 用户状态
     * @param $userStatus
     * @return string
     */
    public static function getUserStatus($userStatus) {
        switch ($userStatus) {
            case 3 :
                $status = "审核通过";
                break;
            case 5:
                $status = "拉黑";
                break;
            default:
                $status = "待审核";
                break;
        }
        return $status;
    }

    /**
     * 单期借款是否开启开关
     * @return int
     */
    public static function oneTermOpen() {
        return 1; //1开启，2关闭
    }

    /**
     * 分期借款是否开启开关
     * @return int
     */
    public static function machTermOpen() {
        return 2; //1开启，2关闭
    }

    /**
     * 299利率,借款表type字段开关
     * @return int
     */
    public static function feeOpen() {
        return 1; //1 fee=0.00098,type=2，2 fee=0.00049,type=3
    }

    /**
     * 购买保险配置信息
     * @return array
     */
    public static function buyInsurance() {
        return [
            'isChk' => 1, //默认是否勾选 1：勾选 2：不勾选
            'alertNum' => 3, //弹窗次数
        ];
    }

    public static function getDesc() {
        return [
//            [
//                'loan_reason' => '旅游',
//                'loan_reason_img' => '298/images/reason_img1.png',
//            ],
//            [
//                'loan_reason' => '进货',
//                'loan_reason_img' => '298/images/reason_img2.png',
//            ],
            [
                'loan_reason' => '购买设备',
                'loan_reason_img' => '298/images/reason_img3.png',
            ],
            [
                'loan_reason' => '购买家具或家电',
                'loan_reason_img' => '298/images/reason_img4.png',
            ],
//            [
//                'loan_reason' => '学习',
//                'loan_reason_img' => '298/images/reason_img5.png',
//            ],
            [
                'loan_reason' => '消费',
                'loan_reason_img' => '298/images/reason_img6.png',
            ],
//            [
//                'loan_reason' => '租房',
//                'loan_reason_img' => '298/images/reason_img8.png',
//            ],
        ];
    }

    /**
     * 智融钥匙H5是否开启开关
     * @return int
     */
    public static function h5Open() {
        return 1; //1开启，2关闭
    }

    /**
     * 导流页面背景图配置
     * @return array
     */
    public static function getTrafficimg() {
        return [
            'a' => [],
            'b' => [],
            'c' => []
        ];
    }

    /**
     * 导流页面视图页
     * @return array
     */
    public static function getTrafficView() {
        return [
            'a' => 'regtraffic',
            'b' => 'bluetraffic',
            'c' => 'redtraffic'
        ];
    }

    /**
     * 存管绑卡新流程开关（跳转页面）
     * @return int
     */
    public static function isOpenBank() {
        return 1; //1开启，2关闭
    }

    /**
     * 获取默认最大额度金额（借款首页）
     * @return int
     */
    public static function getMaxCreditAmounts() {
//        return 10000;
        return 5000;
    }

    /**
     * 直接激活按钮是否显示 1：显示 0：不显示
     * @return int
     */
    public static function getIsCreditShow()
    {
        return 1;
    }

    /**
     * 获取最小额度可借金额
     * @return int
     */
    public static function getMinCreditAmounts() {
        return 500;
    }

    /**
     * 最小借款天数（监测时使用）
     * @return int
     */
    public static function getMinDays(){
        return 7;
    }

    /**
     * 最大借款天数（监测时使用）
     * @return int
     */
    public static function getMaxDays() {
        return 56;
    }

    /**
     * 获取最新借款金额
     * @return array
     * @author 王新龙
     * @date 2018/8/7 16:38
     */
    public static function getMinAmounts() {
        return [
            '7' => 500,
            '14' => 500,
            '21' => 500,
            '28' => 500,
            '56' => 500
        ];
    }

    /**
     * 支付宝账户信息（H5）
     * @return array
     * @author 王新龙
     * @date 2018/7/20 20:49
     */
    public static function getAlipayInfo() {
        return [
            'username' => '萍乡海桐技术服务外包有限公司',
            'account' => 'pxht@xianhuahua.com'
        ];
    }

    /**
     * 评测驳回展示导流code码
     * @return array
     * @author 王新龙
     * @date 2018/8/1 14:32
     */
    public static function getCreditReject() {
        return [
            '80000002',
            '80000003',
            '80010001',
            '80010002',
            '80010003',
            '80010004',
            '80010005',
            '80010006',
            '80010007',
            '80010008',
            '80010009',
            '80010010',
            '80010011',
            '80010012',
            '80010013',
            '80010014',
            '80010015',
            '80010100',
            '80030004',
            '80030005',
            '80030006',
            '80005003'
        ];
    }

    /**
     * 贷超显示开关
     * @return int  1显示 2不显示
     */
    public static function supermarketOpen(){
        return 2;
    }

    /**
     * 检查进场开关
     * @return int  1离场 2进场
     */
    public static function inspectOpen(){
        return 1;
    }

    /**
     * 续期检查进场开关
     * @return int  1离场 2进场
     */
    public static function renewalInspectOpen(){
        return 1;
    }

    /**
     * 借款合同开关
     * @return int 1显示 2不显示
     */
    public static function contract(){
        return 2;
    }

    /**
     * 白名单
     * @return array
     */
    public static function listWhiteList(){
        return [
            '13466604662',
            '13439660605',
            '18500310315',
            '18500597522',
            '15910690412',
            '18610291548',
            '17600664664'
        ];
    }
}
