<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_sobot_session_customer".
 *
 * @property string $id
 * @property string $startDate
 * @property string $endDate
 * @property string $totalConsultSession
 * @property string $totalConsultSession_str
 * @property string $totalValidSession
 * @property string $totalValidSession_str
 * @property string $sessionRate
 * @property string $totalValidSessionRate
 * @property string $totalValidSessionRate_str
 * @property string $avgDuration
 * @property string $avgSessionDuration
 * @property string $avgSessionDuration_str
 * @property string $sessionDuration
 * @property string $avgTduration
 * @property string $avgTotalDuration
 * @property string $avgTotalSessionDuration
 * @property string $avgTotalSessionDuration_str
 * @property string $totalInvalidSession
 * @property string $totalInvalidSession_str
 * @property string $res_json
 * @property string $createTime
 * @property string $lastModifyTime
 * @property integer $version
 */
class SobotSessionCustomer extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_sobot_session_customer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['startDate', 'endDate', 'res_json', 'createTime', 'lastModifyTime'], 'required'],
            [['startDate', 'endDate', 'createTime', 'lastModifyTime'], 'safe'],
            [['res_json'], 'string'],
            [['version'], 'integer'],
            [['totalConsultSession', 'totalConsultSession_str', 'totalValidSession', 'totalValidSession_str', 'sessionRate', 'totalValidSessionRate', 'totalValidSessionRate_str', 'avgDuration', 'avgSessionDuration', 'avgSessionDuration_str', 'sessionDuration', 'avgTduration', 'avgTotalDuration', 'avgTotalSessionDuration', 'avgTotalSessionDuration_str', 'totalInvalidSession', 'totalInvalidSession_str'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'totalConsultSession' => 'Total Consult Session',
            'totalConsultSession_str' => 'Total Consult Session Str',
            'totalValidSession' => 'Total Valid Session',
            'totalValidSession_str' => 'Total Valid Session Str',
            'sessionRate' => 'Session Rate',
            'totalValidSessionRate' => 'Total Valid Session Rate',
            'totalValidSessionRate_str' => 'Total Valid Session Rate Str',
            'avgDuration' => 'Avg Duration',
            'avgSessionDuration' => 'Avg Session Duration',
            'avgSessionDuration_str' => 'Avg Session Duration Str',
            'sessionDuration' => 'Session Duration',
            'avgTduration' => 'Avg Tduration',
            'avgTotalDuration' => 'Avg Total Duration',
            'avgTotalSessionDuration' => 'Avg Total Session Duration',
            'avgTotalSessionDuration_str' => 'Avg Total Session Duration Str',
            'totalInvalidSession' => 'Total Invalid Session',
            'totalInvalidSession_str' => 'Total Invalid Session Str',
            'res_json' => 'Res Json',
            'createTime' => 'Create Time',
            'lastModifyTime' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function addRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $time = date('Y-m-d H:i:s');
        $data['lastModifyTime'] = $time;
        $data['createTime'] = $time;
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $data['lastModifyTime'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 查询记录，根据startDate及endDate
     * @param $startDate
     * @param $endDate
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/10/11 16:11
     */
    public function getByStartAndEnd($startDate, $endDate) {
        if (empty($startDate) || empty($endDate)) {
            return null;
        }
        return self::find()->where(['startDate' => $startDate, 'endDate' => $endDate])->one();
    }
}
