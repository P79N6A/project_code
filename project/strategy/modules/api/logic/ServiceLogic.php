<?php
namespace app\modules\api\logic;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;

use app\models\antifraud\Address;
use app\models\antifraud\Black;
use app\models\antifraud\Contact;
use app\models\antifraud\Detail;
use app\models\antifraud\Report;

class ServiceLogic extends BaseLogic
{
    function __construct()
    {
        parent::__construct();
    }
    /**
     * [getCreditData 接口数据]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function getCreditData($data)
    {
        $operator = [];
        $aid        = ArrayHelper::getValue($data,'aid','');
        $request_id = ArrayHelper::getValue($data,'request_id','');
        $user_id    = ArrayHelper::getValue($data,'user_id','');
        $anti_where = ['and', ['request_id' => $request_id],['aid'=>$aid], ['user_id' => $user_id]];
        //获取决策数据
        $oAddress = new Address();
        $address_select = 'addr_count,addr_loan_count';
        $address_data = $oAddress->getOne($anti_where, $address_select);

        $oContact = new Contact();
        $contact_select = 'com_c_total_mavg,com_r_total_mavg';
        $contact_data = $oContact->getOne($anti_where, $contact_select);

        $oReport = new Report();
        $report_select = 'report_court,report_loan_connect,report_lawyer';
        $report_data = $oReport->getOne($anti_where, $report_select);

        $oDetail = new Detail();
        $detail_select = 'com_valid_mobile,vs_phone_match,com_night_connect_p,com_night_duration_p,com_night_connect,com_night_duration,com_month_call,com_month_answer';
        $detail_data = $oDetail->getOne($anti_where, $detail_select);

        $operator =  [
            'com_valid_mobile' => Arrayhelper::getValue($detail_data,'com_valid_mobile',''),
            'addr_count' => Arrayhelper::getValue($address_data,'addr_count',''),
            'vs_phone_match' => Arrayhelper::getValue($detail_data,'vs_phone_match',''),
            'com_c_total_mavg' => Arrayhelper::getValue($contact_data,'com_c_total_mavg',''),
            'com_r_total_mavg' => Arrayhelper::getValue($contact_data,'com_r_total_mavg',''),
            'report_court' => Arrayhelper::getValue($report_data,'report_court',''),
            'report_lawyer' => Arrayhelper::getValue($report_data,'report_lawyer',''),
            'report_loan_connect' => Arrayhelper::getValue($report_data,'report_loan_connect',''),
            'com_night_connect_p' => Arrayhelper::getValue($detail_data,'com_night_connect_p',''),
            'com_night_duration_p' => Arrayhelper::getValue($detail_data,'com_night_duration_p',''),
            'com_night_connect' => Arrayhelper::getValue($detail_data,'com_night_connect',''),
            'com_night_duration' => Arrayhelper::getValue($detail_data,'com_night_duration',''),
            'com_month_call' => Arrayhelper::getValue($detail_data,'com_month_call',''),
            'com_month_answer' => Arrayhelper::getValue($detail_data,'com_month_answer',''),
            'addr_loan_count' => Arrayhelper::getValue($address_data,'addr_loan_count',''),
        ];
        return $operator;
    }
}