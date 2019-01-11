<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_sobot_session".
 *
 * @property string $id
 * @property string $startDate
 * @property string $endDate
 * @property string $consultSession
 * @property string $consultSession_str
 * @property string $validSession
 * @property string $validSession_str
 * @property string $invalidSession
 * @property string $invalidSession_str
 * @property string $validRate
 * @property string $validReceptionRate
 * @property string $validReceptionRate_str
 * @property string $selfReceptionSession
 * @property string $selfReceptionSession_str
 * @property string $selfRate
 * @property string $selfReceptionRate
 * @property string $selfReceptionRate_str
 * @property string $toHumanSession
 * @property string $toHumanSession_str
 * @property string $humanRate
 * @property string $toHumanSessionRate
 * @property string $toHumanSessionRate_str
 * @property string $avgDuration
 * @property string $avgSessionDuration
 * @property string $avgSessionDuration_str
 * @property string $sessionDuration
 * @property string $customerActiveToHuman
 * @property string $customerActiveToHuman_str
 * @property string $customerActiveRate
 * @property string $customerActiveToHumanRate
 * @property string $customerActiveToHumanRate_str
 * @property string $keywordToHuman
 * @property string $keywordToHuman_str
 * @property string $keywordRate
 * @property string $keywordToHumanRate
 * @property string $keywordToHumanRate_str
 * @property string $res_json
 * @property string $createTime
 * @property string $lastModifyTime
 * @property integer $version
 */
class SobotSession extends BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_sobot_session';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['startDate', 'endDate', 'res_json', 'createTime', 'lastModifyTime'], 'required'],
            [['startDate', 'endDate', 'createTime', 'lastModifyTime'], 'safe'],
            [['res_json'], 'string'],
            [['version'], 'integer'],
            [['consultSession', 'consultSession_str', 'validSession', 'validSession_str', 'invalidSession', 'invalidSession_str', 'validRate', 'validReceptionRate', 'validReceptionRate_str', 'selfReceptionSession', 'selfReceptionSession_str', 'selfRate', 'selfReceptionRate', 'selfReceptionRate_str', 'toHumanSession', 'toHumanSession_str', 'humanRate', 'toHumanSessionRate', 'toHumanSessionRate_str', 'avgDuration', 'avgSessionDuration', 'avgSessionDuration_str', 'sessionDuration', 'customerActiveToHuman', 'customerActiveToHuman_str', 'customerActiveRate', 'customerActiveToHumanRate', 'customerActiveToHumanRate_str', 'keywordToHuman', 'keywordToHuman_str', 'keywordRate', 'keywordToHumanRate', 'keywordToHumanRate_str'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'startDate' => 'Start Date',
            'endDate' => 'End Date',
            'consultSession' => 'Consult Session',
            'consultSession_str' => 'Consult Session Str',
            'validSession' => 'Valid Session',
            'validSession_str' => 'Valid Session Str',
            'invalidSession' => 'Invalid Session',
            'invalidSession_str' => 'Invalid Session Str',
            'validRate' => 'Valid Rate',
            'validReceptionRate' => 'Valid Reception Rate',
            'validReceptionRate_str' => 'Valid Reception Rate Str',
            'selfReceptionSession' => 'Self Reception Session',
            'selfReceptionSession_str' => 'Self Reception Session Str',
            'selfRate' => 'Self Rate',
            'selfReceptionRate' => 'Self Reception Rate',
            'selfReceptionRate_str' => 'Self Reception Rate Str',
            'toHumanSession' => 'To Human Session',
            'toHumanSession_str' => 'To Human Session Str',
            'humanRate' => 'Human Rate',
            'toHumanSessionRate' => 'To Human Session Rate',
            'toHumanSessionRate_str' => 'To Human Session Rate Str',
            'avgDuration' => 'Avg Duration',
            'avgSessionDuration' => 'Avg Session Duration',
            'avgSessionDuration_str' => 'Avg Session Duration Str',
            'sessionDuration' => 'Session Duration',
            'customerActiveToHuman' => 'Customer Active To Human',
            'customerActiveToHuman_str' => 'Customer Active To Human Str',
            'customerActiveRate' => 'Customer Active Rate',
            'customerActiveToHumanRate' => 'Customer Active To Human Rate',
            'customerActiveToHumanRate_str' => 'Customer Active To Human Rate Str',
            'keywordToHuman' => 'Keyword To Human',
            'keywordToHuman_str' => 'Keyword To Human Str',
            'keywordRate' => 'Keyword Rate',
            'keywordToHumanRate' => 'Keyword To Human Rate',
            'keywordToHumanRate_str' => 'Keyword To Human Rate Str',
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