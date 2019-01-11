<?php

namespace app\models\anti;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "af_contact_analysis_list".
 *
 * @property string $id
 * @property string $phone
 * @property string $source
 * @property string $behavior_score
 * @property string $contact_blacklist_analysis
 * @property string $carrier_consumption_stats
 * @property string $carrier_consumption_stats_per_month
 * @property string $modify_time
 * @property string $create_time
 */
class AfContactAnalysisList extends AntiBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_contact_analysis_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'source'], 'required'],
            [['source'], 'integer'],
            [['behavior_score', 'contact_blacklist_analysis', 'carrier_consumption_stats', 'carrier_consumption_stats_per_month'], 'string'],
            [['modify_time', 'create_time'], 'safe'],
            [['phone'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '用户手机号',
            'source' => '报告来源',
            'behavior_score' => '行为评分',
            'contact_blacklist_analysis' => '联系人黑名单分析',
            'carrier_consumption_stats' => '运营商消费统计',
            'carrier_consumption_stats_per_month' => '每个月运营商消费统计',
            'modify_time' => 'Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function getContact($where)
    {   
        return static::find()->where($where)->orderBy('ID DESC')->one();
    }

    public function saveData($data)
    {
        $time = date("Y-m-d H:i:s"); 
        $data['modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data); 
        if ($error) { 
            Logger::dayLog("anti/contactAnalysisList","save failed", $data, $error);
            return false;
        }
        return $this->save();
    }

    public function updateDate($data)
    {
        $this->behavior_score = $this->getValue($data,'behavior_score');
        $this->contact_blacklist_analysis = $this->getValue($data,'contact_blacklist_analysis');
        $this->carrier_consumption_stats = $this->getValue($data,'carrier_consumption_stats');
        $this->carrier_consumption_stats_per_month = $this->getValue($data,'carrier_consumption_stats_per_month');
        $this->modify_time = date("Y-m-d H:i:s");
        return $this->save();
    }   
}
