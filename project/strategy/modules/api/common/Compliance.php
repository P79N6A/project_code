<?php
/**
 *合规逻辑
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/7
 * Time: 14:13
 */
namespace app\modules\api\common;
use app\commands\yyy\common\AllinApi;
use app\models\antifraud\Address;
use app\models\antifraud\Contact;
use app\models\antifraud\Detail;
use app\models\antifraud\DetailOther;
use app\models\antifraud\Report;
use app\models\cloud\BlackIdcard;
use app\models\cloud\BlackPhone;
use app\models\cloud\DcForeignBlackIdcard;
use app\models\cloud\DcForeignBlackPhone;
use app\models\cloud\DcOrigin;
use app\models\cloud\MultiIdcard;
use app\models\cloud\MultiPhone;
use app\models\Result;
use app\models\yyy\UserLoan;
use Yii;
use yii\helpers\ArrayHelper;

class Compliance
{
    public function logicalProcessing($data_set)
    {
        $verifyParams = $this->verifyParams($data_set);
        if ($verifyParams != '0000' ){
            return $this->returnMsg($verifyParams);
        }

        $res_data = $this->getFraudReport($data_set);
        if ($res_data[0] != '0000'){
            return $this->returnMsg($res_data[0]);
        }
        return ['res_code'=>'0000', 'res_data' => $res_data[1]];
    }

    /**
     * 防范欺诈报告(借款申请)
     * @param $reqeust_data
     * @return array
     */
    private function getFraudReport($reqeust_data)
    {
        if (empty($reqeust_data)){
            return ['100011'];
        }

        $phone = ArrayHelper::getValue($reqeust_data, 'phone', '');
        $idcard = ArrayHelper::getValue($reqeust_data, 'idcard', '');  #身份证号
        $user_id = ArrayHelper::getValue($reqeust_data, 'user_id', '');
        $aid = ArrayHelper::getValue($reqeust_data, 'aid', '');
        $loan_id = ArrayHelper::getValue($reqeust_data, 'loan_id', '');
        $request_id = ArrayHelper::getValue($reqeust_data, 'request_id', '');
        $loan_create_time = ArrayHelper::getValue($reqeust_data, 'loan_create_time', '');
        if (empty($loan_create_time)){
            $create_time = date("Y-m-d H:i:s", time());
        }else {
            $create_time = date("Y-m-d H:i:s", strtotime($loan_create_time) + 60 * 60);
        }
        $data_set = [];

        //1.获取dc_multi_idcard
        $oMultiIdcard = new MultiIdcard();
        $multi_id_card_where = [
            'AND',
            ['=', 'idcard', $idcard],
            ['<', 'create_time', $create_time]
        ];
        $multi_idcard_data = $oMultiIdcard->getIdMultiData($multi_id_card_where);
        $format_multi_idcard = $this->formatMultiIdcardData($multi_idcard_data);
        $data_set = array_merge($data_set, $format_multi_idcard);


        //2.获取dc_multi_phone
        $oMultiPhone = new MultiPhone();
        $multi_id_phone_where = [
            'AND',
            ['=', 'phone', $phone],
            ['<', 'create_time', $create_time]
        ];
        $multi_info_data = $oMultiPhone->getPhMultiData($multi_id_phone_where);
        $format_multi_phone = $this->formatMultiPhone($multi_info_data);
        $data_set = array_merge($data_set, $format_multi_phone);

        //3.获取dc_black_phone
        $oBlackPhone = new BlackPhone();
        $black_phone_where = [
            'AND',
            ['=', 'phone', $phone],
            ['<', 'create_time', $create_time]
        ];
        $black_phone_data = $oBlackPhone->getPhBlackData($black_phone_where);
        $format_black_phone = $this->formatBlackPhone($black_phone_data);
        $data_set = array_merge($data_set, $format_black_phone);

        //4.获取dc_black_idcard
        $oBlackIdcard = new BlackIdcard();
        $black_idcard_where = [
            'AND',
            ['=', 'idcard', $idcard],
            ['<', 'create_time', $create_time]
        ];
        $black_idcard_data = $oBlackIdcard->getIdBlackData($black_idcard_where);
        $format_black_idcard = $this->formatBlackIdcard($black_idcard_data);
        $data_set = array_merge($data_set, $format_black_idcard);
        
        //5.获取dc_foreign_black_idcard
        $oDcForeignBlackIdcard = new DcForeignBlackIdcard();
        $f_black_idcard_where = [
            'AND',
            ['=', 'idcard', $idcard],
            ['<', 'create_time', $create_time]
        ];
        $foreign_data = $oDcForeignBlackIdcard->getForeignBlackIdcardData($f_black_idcard_where);
        $format_foreign_idcard = $this->formatForeignIdcard($foreign_data);
        $data_set = array_merge($data_set, $format_foreign_idcard);

        //6.获取dc_foreign_black_phone
        $oDcForeignBlackPhone = new DcForeignBlackPhone();
        $f_black_phone_where = [
            'AND',
            ['=', 'phone', $phone],
            ['<', 'create_time', $create_time]
        ];
        $foreign_black_phone_data = $oDcForeignBlackPhone->getForeignBlackPhoneData($f_black_phone_where);
        $format_foreign_phone = $this->formatForeignPhone($foreign_black_phone_data);
        $data_set = array_merge($data_set, $format_foreign_phone);

        //7.获取dc_origin
        $oDcOrigin = new DcOrigin();
        $origin_where = [
            'AND',
            ['=', 'user_id', $user_id],
            ['=', 'idcard', $idcard],
            ['=', 'aid', $aid],
            ['<', 'create_time', $create_time]
        ];
        $origin_data = $oDcOrigin->getOriginData($origin_where, 'is_black');
        $format_origin = $this->formatOrigin($origin_data);
        $data_set = array_merge($data_set, $format_origin);

        //8.获取af_report
        $oReport = new Report();
        $report_where = [
            'AND',
            ['=', 'request_id', $request_id],
            ['=', 'aid', $aid],
            ['=', 'user_id', $user_id],
            ['<', 'create_time', $create_time]
        ];
        $report_data = $oReport->getReport($report_where, 'report_fcblack,report_shutdown');
        $format_report = $this->formatReport($report_data);
        $data_set = array_merge($data_set, $format_report);


        //9.获取af_detail_other
        $oDetailOther = new DetailOther();
        $af_de_o_where = [
            'AND',
            ['=', 'request_id', $request_id],
            ['=', 'user_id', $user_id],
            ['=', 'aid', $aid],
            ['<', 'create_time', $create_time]
        ];
        $detail_other_data = $oDetailOther->getData($af_de_o_where, 'phone_register_month');
        $format_detail_other = $this->foramtDetailOther($detail_other_data);
        $data_set = array_merge($data_set, $format_detail_other);

        //10.获取af_detail
        $oDetail = new Detail();
        $af_detail_where = [
            'AND',
            ['=', 'request_id', $request_id],
            ['=', 'user_id', $user_id],
            ['=', 'aid', $aid],
            ['<', 'create_time', $create_time]
        ];
        $detail_data = $oDetail->getDetail($af_detail_where);
        $detail_data = $this->format_detail($detail_data);
        $data_set = array_merge($data_set, $detail_data);

        //11.获取af_address
        $oAddress = new Address();
        $address_where = [
            'AND',
            ['=', 'request_id', $request_id],
            ['=', 'user_id', $user_id],
            ['=', 'aid', $aid],
            ['<', 'create_time', $create_time]
        ];
        $address_data = $oAddress->getAddress($address_where);
        $address_data = $this->formatAddress($address_data);
        $data_set = array_merge($data_set, $address_data);

        //12.获取af_contact
        $oContact = new Contact();
        $contact_where = [
            'AND',
            ['=', 'request_id', $request_id],
            ['=', 'user_id', $user_id],
            ['=', 'aid', $aid],
            ['<', 'create_time', $create_time]
        ];
        $contact_data = $oContact->getContact($contact_where);
        $contact_data = $this->formatContactData($contact_data);
        $data_set = array_merge($data_set, $contact_data);
        //================================================


        //13.计算“客户当日申请次数超限制”， “客户近7日申请次数超限制”
        $oCloudApi = new CloudApi();
        $limit_data = $oCloudApi->getMultiLoanHg(['identity_id'=>$user_id, 'create_time'=> $create_time]);
        $limit_data = $this->formatLimitData($limit_data);
        $data_set = array_merge($data_set, $limit_data);

        //14.计算客户过去3个月逾期相关
        $oAllinApi = new AllinApi();
        $be_overdue = $oAllinApi->getReloanDatesHg($user_id, $create_time);
        $be_overdue = $this->formatBeOverdue($be_overdue);
        $data_set = array_merge($data_set, $be_overdue);

        //15.获取“只算56天产品的借款次数状态是8”
        $oUserLoan = new UserLoan();
        $loan_count = $oUserLoan->getSuLoanData($user_id, $create_time);
        $data_set = array_merge($data_set, ['success_num' => $loan_count]);

        //16.计算“借款时间-身份证出生时间”
        $calc_idcard = $this->CalcIdcard($idcard, $create_time);
        $data_set = array_merge($data_set, ['age' => $calc_idcard]); //客户年龄限制

        //17.计算“客户地域限制”
        $place_ofBe_longing = $this->placeOfBelonging($idcard);
        $data_set = array_merge($data_set, ['identity_address' => $place_ofBe_longing]);

        //18.获取st_result数据
        $oResult = new Result();
        $result_where = [
            'AND',
            //['=', 'request_id', $request_id],
            ['=', 'user_id', $user_id],
            ['=', 'from', 6],
            ['<', 'create_time', $create_time]
        ];
        $get_result_data = $oResult->getResultiData($result_where);
        $format_result = $this->formatResult($get_result_data);
        $data_set = array_merge($data_set, $format_result);
        //返回数据
        return ['0000', $data_set];

    }

    /**
     * 格式multi_idcard数据
     * @param array $data
     * @return array
     */
    private function formatMultiIdcardData($data = [])
    {
        return [
            'mi_mid_fm_seven_d'            => ArrayHelper::getValue($data, 'mid_fm_seven_d', ''), //7天身份证多平台申请
            'mi_mid_fm_one_m'              => ArrayHelper::getValue($data, 'mid_fm_one_m', ''), //一个月身份证多平台申请
            'mi_mid_fm_three_m'            => ArrayHelper::getValue($data, 'mid_fm_three_m', ''), //三个月身份证多平台申请
            'mi_modify_time'               => ArrayHelper::getValue($data, 'modify_time', ''), //身份证号多头数据获取时间
        ];

    }

    /**
     * 格式multi_phone
     * @param array $data
     * @return array
     */
    private function formatMultiPhone($data = [])
    {
        return [
            'mp_mph_fm_seven_d'        => ArrayHelper::getValue($data, 'mph_fm_seven_d', ''), //7天手机号多平台申请
            'mp_mph_fm_one_m'          => ArrayHelper::getValue($data, 'mph_fm_one_m', ''), //一个月手机号多平台申请
            'mp_mph_fm_three_m'        => ArrayHelper::getValue($data, 'mph_fm_three_m', ''), //三个月手机号多平台申请
            'mp_modify_time'           => ArrayHelper::getValue($data, 'modify_time', ''), //手机号多头数据获取时间
        ];
    }

    /**
     * 格式 black_phone
     * @param array $data
     * @return array
     */
    private function formatBlackPhone($data = [])
    {
        $phone = ArrayHelper::getValue($data, 'phone', '');
        $bp_bph_fm_sx_state = 0;
        if (!empty($phone)){
            $bp_bph_fm_sx_state = 1;
        }
        return [
            'bp_bph_fm_sx'         => ArrayHelper::getValue($data, 'bph_fm_sx', 0), // 手机号同盾失信证据库
            'bp_bph_fm_fack'       => ArrayHelper::getValue($data, 'bph_fm_fack', 0), // 手机号同盾虚假号码库
            'bp_bph_fm_small'      => ArrayHelper::getValue($data, 'bph_fm_small', 0), // 手机号同盾小号库
            'bp_bph_fm_sx_state'   => $bp_bph_fm_sx_state, // 亲属联系人手机号同盾失信证据库
            'bp_bph_br'            => ArrayHelper::getValue($data, 'bph_br', 0), //手机号百融黑名单
            'bpbph_other'          => ArrayHelper::getValue($data, 'bph_other', 0), //手机号三方黑名单
            'bp_bph_y'             => ArrayHelper::getValue($data, 'bph_y', 0), //手机号先花花黑名单

        ];
    }

    /**
     * 格式 black_idcard
     * @param array $data
     * @return array
     */
    private function formatBlackIdcard($data = [])
    {
        return [
            'bi_bid_fm_sx'                 => ArrayHelper::getValue($data, 'bid_fm_sx', 0), //身份证同盾失信证据库
            'bi_bid_fm_court_sx'           => ArrayHelper::getValue($data, 'bid_fm_court_sx', 0), //身份证同盾法院失信证据库
            'bi_bid_fm_court_enforce'      => ArrayHelper::getValue($data, 'bid_fm_court_enforce', 0), //身份证同盾法院执行证据库
            'bi_bid_fm_lost'               => ArrayHelper::getValue($data, 'bid_fm_lost', 0), //身份证同盾失联证据库
            'bi_bid_br'                    => ArrayHelper::getValue($data, 'bid_br', 0), //身份证百融黑名单
            'bi_bid_other'                 => ArrayHelper::getValue($data, 'bid_other', 0), //身份证三方黑名单
            'bi_bid_y'                     => ArrayHelper::getValue($data, 'bid_y', 0), //身份证先花花黑名单

        ];
    }

    /**
     * 格式 foreign_black_idcard
     * @param array $data
     * @return array
     */
    private function formatForeignIdcard($data = [])
    {
        return [
            'fi_id_collection_black'           => ArrayHelper::getValue($data, 'match_status', 0), //身份证号催收黑名单
        ];
    }

    /**
     * 格式 foreign_black_phone
     * @param array $data
     * @return array
     */
    private function formatForeignPhone($data = [])
    {
        return [
            'fp_ph_collection_black'           => ArrayHelper::getValue($data, 'match_status', 0), //手机号催收黑名单

        ];
    }

    /**
     * 格式 dc_origin
     * @param array $data
     * @return array
     */
    private function formatOrigin($data = [])
    {
        return [
            'o_is_black_tq'       => ArrayHelper::getValue($data, 'is_black', 0), //天启黑名单
        ];
    }

    /**
     * 格式 af_report
     * @param array $data
     * @return array
     */
    private function formatReport($data = [])
    {
        return [
            'r_report_fcblack'          => ArrayHelper::getValue($data, 'report_fcblack', 0), //聚信立黑名单
            'r_report_shutdown'         => ArrayHelper::getValue($data, 'report_shutdown', ''), //手机关机时长
        ];
    }

    /**
     * 格式detail_other
     * @param array $data
     * @return array
     */
    private function foramtDetailOther($data = [])
    {
        return [
            'report_use_time'  => ArrayHelper::getValue($data, 'phone_register_month', ''), //手机注册时长
        ];
    }

    /**
     * 格式af_detail
     * @param array $data
     * @return array
     */
    private function format_detail($data = [])
    {
        return [
            'afd_com_night_duration_p'          => ArrayHelper::getValue($data, 'com_night_duration_p', ''), //夜间通话时长占比
            'afd_com_night_connect_p'           => ArrayHelper::getValue($data, 'com_night_connect_p', ''), //夜间通话次数占比
            'afd_com_valid_mobile'              => ArrayHelper::getValue($data, 'com_valid_mobile', ''), //有效联系人个数
            'afd_vs_phone_match'                => ArrayHelper::getValue($data, 'vs_phone_match', ''), //通讯录与通话记录匹配情况
        ];
    }

    /**
     * 格式 af_address
     * @param array $data
     * @return array
     */
    private function formatAddress($data = [])
    {
        return [
            'addr_relative_count'       => ArrayHelper::getValue($data, 'addr_relative_count', ''), //通讯录与亲属联系人情况
            'addr_contacts_count'       => ArrayHelper::getValue($data, 'addr_contacts_count', ''), //通讯录与常用联系人情况

        ];
    }

    /**
     * 格式 af_contact
     * @param array $data
     * @return array
     */
    private function formatContactData($data = [])
    {
        return [
            'com_r_total'       => ArrayHelper::getValue($data, 'com_r_total', ''), //通话记录与亲属联系人情况
            'com_c_total'       => ArrayHelper::getValue($data, 'com_c_total', ''), //通话记录与常用联系人情况

        ];
    }

    /**
     * 格式限制数据
     * @param array $data
     * @return array
     */
    private function formatLimitData($data = [])
    {
        return [
            'one_more_loan_value'       => ArrayHelper::getValue($data, 'one_more_loan_value', ''), //客户当日申请次数超限制
            'seven_more_loan_value'     => ArrayHelper::getValue($data, 'seven_more_loan_value', ''), //客户近7日申请次数超限制
        ];
    }

    /**
     * 格式逾期相关
     * @param array $data
     * @return array
     */
    private function formatBeOverdue($data = [])
    {
        return [
            'wst_dlq_sts'       => ArrayHelper::getValue($data, 'wst_dlq_sts', ''), //客户历史最坏逾期天数
            'mth3_dlq_num'      => ArrayHelper::getValue($data, 'mth3_dlq_num', ''), //客户过去3个月逾期次数（按照贷款记）
            'mth3_wst_sys'      => ArrayHelper::getValue($data, 'mth3_wst_sys', ''), //客户过去3个月最坏逾期天数
            'mth3_dlq7_num'     => ArrayHelper::getValue($data, 'mth3_dlq7_num', ''), //客户过去3个月逾期超过7天的贷款数
            'mth6_dlq_ratio'    => ArrayHelper::getValue($data, 'mth6_dlq_ratio', ''), //客户过去6个月有过逾期的贷款比例

        ];
    }

    /**
     * 计算：借款时间-身份证出生时间
     * @param $idcard
     * @param $loan_create_time
     * @return int
     */
    private function CalcIdcard($idcard, $loan_create_time)
    {
        if (empty($idcard) || empty($loan_create_time)){
            return 0;
        }
        $birthday = strlen($idcard)==15 ? ('19' . substr($idcard, 6, 6)) : substr($idcard, 6, 8);
        $clac = (strtotime($loan_create_time) - strtotime($birthday)) /(86400 * 365);
        return (int)$clac;
    }

    private function placeOfBelonging($idcard)
    {
        if (empty($idcard)){
            return '';
        }
        $idcard_num = substr($idcard, 0, 6);
        $oIdcardAddress = new IdcardAddress();
        $address = $oIdcardAddress->gethometownByIdcard();
        return ArrayHelper::getValue($address, $idcard_num, '');
    }

    private function formatResult($data = [])
    {
        $res_info = ArrayHelper::getValue($data, 'res_info', '');
        if (empty($res_info)){
            return [
                'prome_v4_score'          => 0,
            ];
        }
        $res_info = json_decode($res_info, true);
        return [
            'prome_v4_score'  => ArrayHelper::getValue($res_info, 'PROME_V3_SCORE', 0),
        ];

    }


    /**
     * 验证
     * @param $data_set
     * @return mixed|string
     */
    private function verifyParams($data_set)
    {
        if (empty($data_set)){
            return "100002";
        }
        $verify_data = [
            'idcard'        => '100009',
            'phone'         => '100010',
            'user_id'       => '100002',
            'request_id'    => '100005',
            'aid'           => '100006',
        ];
        foreach($verify_data as $key=>$value){
            if (empty($data_set[$key])){
                return $value;
            }
        }
        return '0000';
    }

    private function errorMsg()
    {
        return [
            '100001'    => '请求数据异常',
            '100002'    => 'user_id不能为空！', //user_id
//            '100003'    => 'loan_id不能为空!', //loan_id
//            '100004'    => '请求评测时决策不能为空！', //strategy_req_id（请求评测时决策返回的ID）
            '100005'    => '', //request_id（请求运营商报告时的ID）
            '100006'    => '', //aid
//            '100007'    => '', //loan_create_time(借款时间或评测时间)
//            '100008'    => '', //relation_phone(亲属联系人)
            '100009'    => '身份证号不能为空！', //idcard
            '100010'    => '手机号不能为空！', //phone
            '100011'    => '参数不为空！', //phone
        ];
    }

    public function returnMsg($code)
    {
        $msg = ArrayHelper::getValue($this->errorMsg(), $code, '未知错误');

        return ['res_code'=>$code, 'res_data' => $msg];
    }

}