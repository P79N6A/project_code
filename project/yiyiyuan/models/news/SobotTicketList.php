<?php

namespace app\models\news;

use app\commonapi\Logger;
use Yii;

/**
 * This is the model class for table "yi_sobot_ticket_list".
 *
 * @property string $id
 * @property string $ticketId
 * @property string $ticketCode
 * @property string $companyId
 * @property string $ticketTitle
 * @property string $ticketContent
 * @property string $ticketLevel
 * @property string $ticketStatus
 * @property string $startType
 * @property string $startUserId
 * @property string $startName
 * @property string $dealUserId
 * @property string $dealUserName
 * @property string $dealGroupId
 * @property string $dealGroupName
 * @property string $createTime
 * @property string $updateTime
 * @property string $updateServiceId
 * @property string $updateServiceName
 * @property string $completeTime
 * @property string $ticketFrom
 * @property string $firstAcceptTime
 * @property string $hopeAcceptTime
 * @property string $customerId
 * @property string $recordId
 * @property string $dealTimer
 * @property string $ticketTypeId
 * @property string $ticketTypeName
 * @property string $firstCompleteTime
 * @property string $hopeCompleteTime
 * @property string $isReminder
 * @property string $reminderTime
 * @property string $closedTime
 * @property string $nick
 * @property string $uname
 * @property string $tel
 * @property string $email
 * @property string $source
 * @property string $qq
 * @property string $remark
 * @property string $enterpriseId
 * @property string $enterpriseName
 * @property string $ticketTypeIdPath
 * @property string $ticketTypeNamePath
 * @property string $stringFields
 * @property string $doubleFields
 * @property string $departmentId
 * @property string $dealFields
 * @property string $res_json
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class SobotTicketList extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_sobot_ticket_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['res_json', 'create_time', 'last_modify_time'], 'required'],
            [['ticketContent', 'stringFields', 'doubleFields', 'dealFields', 'departmentId', 'res_json'], 'string'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['version'], 'integer'],
            [['ticketId', 'ticketCode', 'companyId', 'ticketLevel', 'ticketStatus', 'startType', 'startUserId', 'startName', 'dealUserId', 'dealUserName', 'dealGroupId', 'dealGroupName', 'createTime', 'updateTime', 'updateServiceId', 'updateServiceName', 'completeTime', 'ticketFrom', 'firstAcceptTime', 'hopeAcceptTime', 'customerId', 'recordId', 'dealTimer', 'ticketTypeId', 'ticketTypeName', 'firstCompleteTime', 'hopeCompleteTime', 'isReminder', 'reminderTime', 'closedTime', 'nick', 'uname', 'tel', 'email', 'source', 'qq', 'remark', 'enterpriseId', 'enterpriseName', 'ticketTypeIdPath', 'ticketTypeNamePath'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'ticketId' => 'Ticket ID',
            'ticketCode' => 'Ticket Code',
            'companyId' => 'Company ID',
            'ticketTitle' => 'Ticket Title',
            'ticketContent' => 'Ticket Content',
            'ticketLevel' => 'Ticket Level',
            'ticketStatus' => 'Ticket Status',
            'startType' => 'Start Type',
            'startUserId' => 'Start User ID',
            'startName' => 'Start Name',
            'dealUserId' => 'Deal User ID',
            'dealUserName' => 'Deal User Name',
            'dealGroupId' => 'Deal Group ID',
            'dealGroupName' => 'Deal Group Name',
            'createTime' => 'Create Time',
            'updateTime' => 'Update Time',
            'updateServiceId' => 'Update Service ID',
            'updateServiceName' => 'Update Service Name',
            'completeTime' => 'Complete Time',
            'ticketFrom' => 'Ticket From',
            'firstAcceptTime' => 'First Accept Time',
            'hopeAcceptTime' => 'Hope Accept Time',
            'customerId' => 'Customer ID',
            'recordId' => 'Record ID',
            'dealTimer' => 'Deal Timer',
            'ticketTypeId' => 'Ticket Type ID',
            'ticketTypeName' => 'Ticket Type Name',
            'firstCompleteTime' => 'First Complete Time',
            'hopeCompleteTime' => 'Hope Complete Time',
            'isReminder' => 'Is Reminder',
            'reminderTime' => 'Reminder Time',
            'closedTime' => 'Closed Time',
            'nick' => 'Nick',
            'uname' => 'Uname',
            'tel' => 'Tel',
            'email' => 'Email',
            'source' => 'Source',
            'qq' => 'Qq',
            'remark' => 'Remark',
            'enterpriseId' => 'Enterprise ID',
            'enterpriseName' => 'Enterprise Name',
            'ticketTypeIdPath' => 'Ticket Type Id Path',
            'ticketTypeNamePath' => 'Ticket Type Name Path',
            'stringFields' => 'String Fields',
            'doubleFields' => 'Double Fields',
            'departmentId' => 'Department ID',
            'dealFields' => 'Deal Fields',
            'res_json' => 'Res Json',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
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
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            Logger::dayLog('models/error', 'yi_sobot_ticket_list=>addRecord', $error);
            return false;
        }
        return $this->save();
    }

    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            Logger::dayLog('models/error', 'yi_sobot_ticket_list=>updateRecord', $error);
            return false;
        }
        return $this->save();
    }

    /**
     * 查询记录，根据ticketId
     * @param $ticketId
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/10/16 19:47
     */
    public function getByTicketId($ticketId) {
        if (empty($ticketId)) {
            return null;
        }
        return self::find()->where(['ticketId' => $ticketId])->one();
    }
}
