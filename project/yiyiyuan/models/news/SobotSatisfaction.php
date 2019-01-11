<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_sobot_satisfaction".
 *
 * @property string $id
 * @property string $startDate
 * @property string $endDate
 * @property string $resultDate
 * @property string $resultHour
 * @property string $companyId
 * @property string $source
 * @property string $robotId
 * @property string $robotName
 * @property string $effectSessionCount
 * @property string $effectSessionCount_str
 * @property string $totalTimes
 * @property string $totalTimes_str
 * @property string $canPingRate
 * @property string $canpinglv
 * @property string $canpinglv_str
 * @property string $solved
 * @property string $solved_str
 * @property string $notSolved
 * @property string $notSolved_str
 * @property string $solvedRate
 * @property string $solvedLv
 * @property string $solvedLv_str
 * @property string $res_json
 * @property string $createTime
 * @property string $lastModifyTime
 * @property integer $version
 */
class SobotSatisfaction extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_sobot_satisfaction';
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
            [['resultDate', 'resultHour', 'companyId', 'source', 'robotId', 'robotName', 'effectSessionCount', 'effectSessionCount_str', 'totalTimes', 'totalTimes_str', 'canPingRate', 'canpinglv', 'canpinglv_str', 'solved', 'solved_str', 'notSolved', 'notSolved_str', 'solvedRate', 'solvedLv', 'solvedLv_str'], 'string', 'max' => 32]
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
            'source' => 'Source',
            'robotId' => 'Robot ID',
            'robotName' => 'Robot Name',
            'effectSessionCount' => 'Effect Session Count',
            'effectSessionCount_str' => 'Effect Session Count Str',
            'totalTimes' => 'Total Times',
            'totalTimes_str' => 'Total Times Str',
            'canPingRate' => 'Can Ping Rate',
            'canpinglv' => 'Canpinglv',
            'canpinglv_str' => 'Canpinglv Str',
            'solved' => 'Solved',
            'solved_str' => 'Solved Str',
            'notSolved' => 'Not Solved',
            'notSolved_str' => 'Not Solved Str',
            'solvedRate' => 'Solved Rate',
            'solvedLv' => 'Solved Lv',
            'solvedLv_str' => 'Solved Lv Str',
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
