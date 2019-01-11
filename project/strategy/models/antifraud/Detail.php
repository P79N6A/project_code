<?php

namespace app\models\antifraud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "af_detail".
 * 运营商详情
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property string $com_start_time
 * @property string $com_end_time
 * @property integer $com_days
 * @property string $com_month_num
 * @property integer $com_use_time
 * @property integer $com_count
 * @property integer $com_call
 * @property integer $com_answer
 * @property string $com_duration
 * @property string $com_call_duration
 * @property string $com_answer_duration
 * @property string $com_month_connects
 * @property string $com_month_call
 * @property string $com_month_answer
 * @property string $com_month_duration
 * @property string $com_month_call_duration
 * @property string $com_month_answer_duration
 * @property integer $com_people
 * @property integer $com_mobile_people
 * @property integer $com_tel_people
 * @property string $com_month_people
 * @property string $com_mobile_people_mavg
 * @property string $com_tel_people_mavg
 * @property integer $com_night_connect
 * @property string $com_night_duration
 * @property string $com_night_connect_mavg
 * @property string $com_night_duration_mavg
 * @property string $com_night_connect_p
 * @property string $com_night_duration_p
 * @property integer $com_day_connect
 * @property integer $com_days_call
 * @property integer $com_days_answer
 * @property string $com_day_connect_mavg
 * @property string $com_days_call_mavg
 * @property string $com_days_answer_mavg
 * @property integer $com_hours_connect
 * @property integer $com_hours_call
 * @property integer $com_hours_answer
 * @property string $com_hours_connect_davg
 * @property string $com_hours_call_davg
 * @property string $com_hours_answer_davg
 * @property integer $com_people_90
 * @property integer $com_shutdown_total
 * @property integer $com_offen_connect
 * @property integer $com_offen_duration
 * @property integer $com_max_mobile_connect
 * @property string $com_max_mobile_duration
 * @property integer $com_max_tel_connect
 * @property string $com_max_tel_duration
 * @property integer $com_valid_all
 * @property integer $com_valid_mobile
 * @property integer $vs_valid_match
 * @property integer $vs_connect_match
 * @property integer $vs_duration_match
 * @property integer $vs_phone_match
 * @property string $create_time
 */
class Detail extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'user_id', 'com_days', 'com_use_time', 'com_count', 'com_call', 'com_answer', 'com_duration', 'com_call_duration', 'com_answer_duration', 'com_people', 'com_mobile_people', 'com_tel_people', 'com_night_connect', 'com_night_duration', 'com_day_connect', 'com_days_call', 'com_days_answer', 'com_hours_connect', 'com_hours_call', 'com_hours_answer', 'com_people_90', 'com_shutdown_total', 'com_offen_connect', 'com_offen_duration', 'com_max_mobile_connect', 'com_max_mobile_duration', 'com_max_tel_connect', 'com_max_tel_duration', 'com_valid_all', 'com_valid_mobile', 'vs_valid_match', 'vs_connect_match', 'vs_duration_match', 'vs_phone_match'], 'integer'],
            [['com_start_time', 'com_end_time', 'create_time'], 'safe'],
            [['com_month_num', 'com_month_connects', 'com_month_call', 'com_month_answer', 'com_month_duration', 'com_month_call_duration', 'com_month_answer_duration', 'com_month_people', 'com_mobile_people_mavg', 'com_tel_people_mavg', 'com_night_connect_mavg', 'com_night_duration_mavg', 'com_night_connect_p', 'com_night_duration_p', 'com_day_connect_mavg', 'com_days_call_mavg', 'com_days_answer_mavg', 'com_hours_connect_davg', 'com_hours_call_davg', 'com_hours_answer_davg'], 'number']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'request_id' => '用户ID',
            'user_id' => '用户ID',
            'com_start_time' => '最早通话时间',
            'com_end_time' => '最晚通话时间',
            'com_days' => '通话间隔,天数',
            'com_month_num' => '通话间隔,月',
            'com_use_time' => '手机使用历史过短',
            'com_count' => '总通话数',
            'com_call' => '总主叫数',
            'com_answer' => '总被叫数',
            'com_duration' => '总通话时长(秒)',
            'com_call_duration' => '总主叫时长',
            'com_answer_duration' => '总被叫时长',
            'com_month_connects' => '月均:通话数',
            'com_month_call' => '月均:主叫数',
            'com_month_answer' => '月均:被叫数',
            'com_month_duration' => '月均:通话时长',
            'com_month_call_duration' => '月均:主叫时长',
            'com_month_answer_duration' => '月均:被叫时长',
            'com_people' => '总联系人',
            'com_mobile_people' => '手机通话联系人',
            'com_tel_people' => '固话通话联系人',
            'com_month_people' => '月均:联系人数',
            'com_mobile_people_mavg' => '月均:手机通话联系人过少',
            'com_tel_people_mavg' => '月均:有固话通话联系人过少',
            'com_night_connect' => '夜间:总次数',
            'com_night_duration' => '夜间:总时长',
            'com_night_connect_mavg' => '月均:夜间: 总次数 >=40',
            'com_night_duration_mavg' => '月均:夜间: 总时长 30小时',
            'com_night_connect_p' => '夜间: 次数占比>40%',
            'com_night_duration_p' => '夜间: 时长占比>40%',
            'com_day_connect' => '天数:通话天数',
            'com_days_call' => '天数:主叫天数',
            'com_days_answer' => '天数:被叫天数',
            'com_day_connect_mavg' => '月均天数:通话',
            'com_days_call_mavg' => '月均天数:主叫',
            'com_days_answer_mavg' => '月均天数:被叫',
            'com_hours_connect' => '时段: 总通话时段（过去时期=90天）',
            'com_hours_call' => '时段: 总主叫时段（过去时期=90天）',
            'com_hours_answer' => '时段: 总被叫时段（过去时期=90天）',
            'com_hours_connect_davg' => '日均时段: 通话时段',
            'com_hours_call_davg' => '日均时段: 主叫时段',
            'com_hours_answer_davg' => '日均时段: 被叫时段',
            'com_people_90' => '未完 过去时期总通话联系人过少（过去时期=90天）',
            'com_shutdown_total' => '长时间关机次数过多',
            'com_offen_connect' => '频繁联系手机号码通话次数过少（频繁的定义：前10）',
            'com_offen_duration' => '频繁联系手机号码通话时长过短（频繁的定义：前10）',
            'com_max_mobile_connect' => '最频繁联系手机号码通话次数过少',
            'com_max_mobile_duration' => '最频繁联系手机号码通话时长过短',
            'com_max_tel_connect' => '最频繁通话固话号码通话次数过少',
            'com_max_tel_duration' => '最频繁通话固话号码通话时长过短',
            'com_valid_all' => '通话次数>=15次',
            'com_valid_mobile' => '有效手机号: 1.通话次数>=15次，2.为正常手机号',
            'vs_valid_match' => '有效手机号与通讯录匹配数',
            'vs_connect_match' => '运营商前40位通话时长手机号与通讯录匹配度',
            'vs_duration_match' => '运营商前40位通话次数手机号与通讯录匹配度',
            'vs_phone_match' => '运营商手机与通讯录匹配度',
            'create_time' => '创建时间',
        ];
    }

    public function getDetail($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? (float)(sprintf('%.2f',$val)) : 0;
        }
        return $res;
    }
}
