<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_sobot_session_message".
 *
 * @property string $id
 * @property string $startDate
 * @property string $endDate
 * @property string $totalMessage
 * @property string $totalMessage_str
 * @property string $robotMessage
 * @property string $robotMessage_str
 * @property string $totalCustomerMessage
 * @property string $totalCustomerMessage_str
 * @property string $serviceMessage
 * @property string $serviceMessage_str
 * @property string $avgMessage
 * @property string $avgMessage_str
 * @property string $avgCustomerMessage
 * @property string $avgCustomerMessage_str
 * @property string $avgServiceMessge
 * @property string $avgServiceMessge_str
 * @property string $customerToRobotMessage
 * @property string $customerToRobotMessage_str
 * @property string $robotAvgCustomerMessage
 * @property string $robotAvgCustomerMessage_str
 * @property string $robotAvgMessage
 * @property string $robotAvgMessage_str
 * @property string $totalHumanMessage
 * @property string $totalHumanMessage_str
 * @property string $customerToServiceMessage
 * @property string $customerToServiceMessage_str
 * @property string $answerThan
 * @property string $answerThan_str
 * @property string $serviceWord
 * @property string $serviceWord_str
 * @property string $avgServiceWord
 * @property string $avgServiceWord_str
 * @property string $humanAvgMessage
 * @property string $humanAvgMessage_str
 * @property string $humanAvgCustomerMessage
 * @property string $humanAvgCustomerMessage_str
 * @property string $humanAvgServiceMessage
 * @property string $humanAvgServiceMessage_str
 * @property string $serviceOfflineNum
 * @property string $serviceOfflineNum_str
 * @property string $sessionConsult
 * @property string $humanValidSession
 * @property string $robotValidSession
 * @property string $dateTime
 * @property string $source
 * @property string $res_json
 * @property string $createTime
 * @property string $lastModifyTime
 * @property integer $version
 */
class SobotSessionMessage extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_sobot_session_message';
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
            [['totalMessage', 'totalMessage_str', 'robotMessage', 'robotMessage_str', 'totalCustomerMessage', 'totalCustomerMessage_str', 'serviceMessage', 'serviceMessage_str', 'avgMessage', 'avgMessage_str', 'avgCustomerMessage', 'avgCustomerMessage_str', 'avgServiceMessge', 'avgServiceMessge_str', 'customerToRobotMessage', 'customerToRobotMessage_str', 'robotAvgCustomerMessage', 'robotAvgCustomerMessage_str', 'robotAvgMessage', 'robotAvgMessage_str', 'totalHumanMessage', 'totalHumanMessage_str', 'customerToServiceMessage', 'customerToServiceMessage_str', 'answerThan', 'answerThan_str', 'serviceWord', 'serviceWord_str', 'avgServiceWord', 'avgServiceWord_str', 'humanAvgMessage', 'humanAvgMessage_str', 'humanAvgCustomerMessage', 'humanAvgCustomerMessage_str', 'humanAvgServiceMessage', 'humanAvgServiceMessage_str', 'serviceOfflineNum', 'serviceOfflineNum_str', 'sessionConsult', 'humanValidSession', 'robotValidSession', 'dateTime', 'source'], 'string', 'max' => 32]
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
            'totalMessage' => 'Total Message',
            'totalMessage_str' => 'Total Message Str',
            'robotMessage' => 'Robot Message',
            'robotMessage_str' => 'Robot Message Str',
            'totalCustomerMessage' => 'Total Customer Message',
            'totalCustomerMessage_str' => 'Total Customer Message Str',
            'serviceMessage' => 'Service Message',
            'serviceMessage_str' => 'Service Message Str',
            'avgMessage' => 'Avg Message',
            'avgMessage_str' => 'Avg Message Str',
            'avgCustomerMessage' => 'Avg Customer Message',
            'avgCustomerMessage_str' => 'Avg Customer Message Str',
            'avgServiceMessge' => 'Avg Service Messge',
            'avgServiceMessge_str' => 'Avg Service Messge Str',
            'customerToRobotMessage' => 'Customer To Robot Message',
            'customerToRobotMessage_str' => 'Customer To Robot Message Str',
            'robotAvgCustomerMessage' => 'Robot Avg Customer Message',
            'robotAvgCustomerMessage_str' => 'Robot Avg Customer Message Str',
            'robotAvgMessage' => 'Robot Avg Message',
            'robotAvgMessage_str' => 'Robot Avg Message Str',
            'totalHumanMessage' => 'Total Human Message',
            'totalHumanMessage_str' => 'Total Human Message Str',
            'customerToServiceMessage' => 'Customer To Service Message',
            'customerToServiceMessage_str' => 'Customer To Service Message Str',
            'answerThan' => 'Answer Than',
            'answerThan_str' => 'Answer Than Str',
            'serviceWord' => 'Service Word',
            'serviceWord_str' => 'Service Word Str',
            'avgServiceWord' => 'Avg Service Word',
            'avgServiceWord_str' => 'Avg Service Word Str',
            'humanAvgMessage' => 'Human Avg Message',
            'humanAvgMessage_str' => 'Human Avg Message Str',
            'humanAvgCustomerMessage' => 'Human Avg Customer Message',
            'humanAvgCustomerMessage_str' => 'Human Avg Customer Message Str',
            'humanAvgServiceMessage' => 'Human Avg Service Message',
            'humanAvgServiceMessage_str' => 'Human Avg Service Message Str',
            'serviceOfflineNum' => 'Service Offline Num',
            'serviceOfflineNum_str' => 'Service Offline Num Str',
            'sessionConsult' => 'Session Consult',
            'humanValidSession' => 'Human Valid Session',
            'robotValidSession' => 'Robot Valid Session',
            'dateTime' => 'Date Time',
            'source' => 'Source',
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
