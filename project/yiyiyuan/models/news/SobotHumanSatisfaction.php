<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_sobot_human_satisfaction".
 *
 * @property string $id
 * @property string $startDate
 * @property string $endDate
 * @property string $resultDate
 * @property string $resultHour
 * @property string $companyId
 * @property string $groupId
 * @property string $source
 * @property string $staffId
 * @property string $staffName
 * @property string $totalScore
 * @property string $notOpen
 * @property string $effectSessionCount
 * @property string $effectSessionCount_str
 * @property string $totalTimes
 * @property string $totalTimes_str
 * @property string $canpinglv
 * @property string $canpinglv_str
 * @property string $initiative
 * @property string $initiative_str
 * @property string $invites
 * @property string $invites_str
 * @property string $solvedCount
 * @property string $solvedCount_str
 * @property string $solved
 * @property string $solved_str
 * @property string $notSolved
 * @property string $notSolved_str
 * @property string $solvedLv
 * @property string $solvedLv_str
 * @property string $avgNum
 * @property string $avgNum_str
 * @property string $good
 * @property string $good_str
 * @property string $goodLv
 * @property string $goodLv_str
 * @property string $middle
 * @property string $middle_str
 * @property string $middleLv
 * @property string $middleLv_str
 * @property string $bad
 * @property string $bad_str
 * @property string $badLv
 * @property string $badLv_str
 * @property string $canPingRate
 * @property string $solvedRate
 * @property string $commentAvg
 * @property string $goodRate
 * @property string $middleRate
 * @property string $badRate
 * @property string $customActive
 * @property string $customActiveEvRatio
 * @property string $customActiveEvRatio_str
 * @property string $serviceInv
 * @property string $serviceInvEvRatio
 * @property string $serviceInvEvRatio_str
 * @property string $score4
 * @property string $score3
 * @property string $score2
 * @property string $score1
 * @property string $score4_str
 * @property string $score3_str
 * @property string $score2_str
 * @property string $score1_str
 * @property string $inviteCounts
 * @property string $inviteCounts_str
 * @property string $inviteCountsRate
 * @property string $inviteCountsLv
 * @property string $inviteCountsLv_str
 * @property string $res_json
 * @property string $createTime
 * @property string $lastModifyTime
 * @property integer $version
 */
class SobotHumanSatisfaction extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_sobot_human_satisfaction';
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
            [['resultDate', 'resultHour', 'companyId', 'groupId', 'source', 'staffId', 'staffName', 'totalScore', 'notOpen', 'effectSessionCount', 'effectSessionCount_str', 'totalTimes', 'totalTimes_str', 'canpinglv', 'canpinglv_str', 'initiative', 'initiative_str', 'invites', 'invites_str', 'solvedCount', 'solvedCount_str', 'solved', 'solved_str', 'notSolved', 'notSolved_str', 'solvedLv', 'solvedLv_str', 'avgNum', 'avgNum_str', 'good', 'good_str', 'goodLv', 'goodLv_str', 'middle', 'middle_str', 'middleLv', 'middleLv_str', 'bad', 'bad_str', 'badLv', 'badLv_str', 'canPingRate', 'solvedRate', 'commentAvg', 'goodRate', 'middleRate', 'badRate', 'customActive', 'customActiveEvRatio', 'customActiveEvRatio_str', 'serviceInv', 'serviceInvEvRatio', 'serviceInvEvRatio_str', 'score4', 'score3', 'score2', 'score1', 'score4_str', 'score3_str', 'score2_str', 'score1_str', 'inviteCounts', 'inviteCounts_str', 'inviteCountsRate', 'inviteCountsLv', 'inviteCountsLv_str'], 'string', 'max' => 32]
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
            'resultDate' => 'Result Date',
            'resultHour' => 'Result Hour',
            'companyId' => 'Company ID',
            'groupId' => 'Group ID',
            'source' => 'Source',
            'staffId' => 'Staff ID',
            'staffName' => 'Staff Name',
            'totalScore' => 'Total Score',
            'notOpen' => 'Not Open',
            'effectSessionCount' => 'Effect Session Count',
            'effectSessionCount_str' => 'Effect Session Count Str',
            'totalTimes' => 'Total Times',
            'totalTimes_str' => 'Total Times Str',
            'canpinglv' => 'Canpinglv',
            'canpinglv_str' => 'Canpinglv Str',
            'initiative' => 'Initiative',
            'initiative_str' => 'Initiative Str',
            'invites' => 'Invites',
            'invites_str' => 'Invites Str',
            'solvedCount' => 'Solved Count',
            'solvedCount_str' => 'Solved Count Str',
            'solved' => 'Solved',
            'solved_str' => 'Solved Str',
            'notSolved' => 'Not Solved',
            'notSolved_str' => 'Not Solved Str',
            'solvedLv' => 'Solved Lv',
            'solvedLv_str' => 'Solved Lv Str',
            'avgNum' => 'Avg Num',
            'avgNum_str' => 'Avg Num Str',
            'good' => 'Good',
            'good_str' => 'Good Str',
            'goodLv' => 'Good Lv',
            'goodLv_str' => 'Good Lv Str',
            'middle' => 'Middle',
            'middle_str' => 'Middle Str',
            'middleLv' => 'Middle Lv',
            'middleLv_str' => 'Middle Lv Str',
            'bad' => 'Bad',
            'bad_str' => 'Bad Str',
            'badLv' => 'Bad Lv',
            'badLv_str' => 'Bad Lv Str',
            'canPingRate' => 'Can Ping Rate',
            'solvedRate' => 'Solved Rate',
            'commentAvg' => 'Comment Avg',
            'goodRate' => 'Good Rate',
            'middleRate' => 'Middle Rate',
            'badRate' => 'Bad Rate',
            'customActive' => 'Custom Active',
            'customActiveEvRatio' => 'Custom Active Ev Ratio',
            'customActiveEvRatio_str' => 'Custom Active Ev Ratio Str',
            'serviceInv' => 'Service Inv',
            'serviceInvEvRatio' => 'Service Inv Ev Ratio',
            'serviceInvEvRatio_str' => 'Service Inv Ev Ratio Str',
            'score4' => 'Score4',
            'score3' => 'Score3',
            'score2' => 'Score2',
            'score1' => 'Score1',
            'score4_str' => 'Score4 Str',
            'score3_str' => 'Score3 Str',
            'score2_str' => 'Score2 Str',
            'score1_str' => 'Score1 Str',
            'inviteCounts' => 'Invite Counts',
            'inviteCounts_str' => 'Invite Counts Str',
            'inviteCountsRate' => 'Invite Counts Rate',
            'inviteCountsLv' => 'Invite Counts Lv',
            'inviteCountsLv_str' => 'Invite Counts Lv Str',
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
