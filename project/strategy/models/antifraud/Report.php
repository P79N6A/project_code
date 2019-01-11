<?php

namespace app\models\antifraud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "af_report".
 * 运营商报告
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property integer $report_aomen
 * @property integer $report_110
 * @property integer $report_120
 * @property integer $report_lawyer
 * @property integer $report_court
 * @property integer $report_use_time
 * @property integer $report_shutdown
 * @property integer $report_name_match
 * @property integer $report_fcblack_idcard
 * @property integer $report_fcblack_phone
 * @property integer $report_fcblack
 * @property string $report_operator_name
 * @property integer $report_reliability
 * @property string $report_night_percent
 * @property string $report_loan_connect
 * @property string $create_time
 */
class Report extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'user_id', 'report_aomen', 'report_110', 'report_120', 'report_lawyer', 'report_court', 'report_use_time', 'report_shutdown', 'report_name_match', 'report_fcblack_idcard', 'report_fcblack_phone', 'report_fcblack', 'report_reliability'], 'integer'],
            [['report_night_percent'], 'number'],
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['report_operator_name'], 'string', 'max' => 20],
            [['report_loan_connect'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'request_id' => '请求处理id',
            'user_id' => '用户ID',
            'report_aomen' => '出现澳门电话通话情况',
            'report_110' => '出现与110电话通话记录',
            'report_120' => '出现与120电话通话记录',
            'report_lawyer' => '多次出现与律师电话通话记录',
            'report_court' => '多次出现与法院电话通话记录',
            'report_use_time' => '号码使用时长(月)',
            'report_shutdown' => '关机时长(天)',
            'report_name_match' => '是否实名匹配',
            'report_fcblack_idcard' => '身份证为金融黑名单',
            'report_fcblack_phone' => '手机为金融黑名单',
            'report_fcblack' => '任一金融黑名单',
            'report_operator_name' => '运营商归属全称: 四川移动',
            'report_reliability' => '实名认证0:否; 1:是;',
            'report_night_percent' => '夜间占比',
            'report_loan_connect' => '贷款号码联系情况',
            'create_time' => '创建时间',
        ];
    }

    public function getReport($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            if ($v != 'report_night_percent') {
                $res[$v] = $val ? intval($val) : 0;
            } else {
                $res[$v] = $val ? floatval($val) : 0;
            }
        }
        return $res;
    }
}
