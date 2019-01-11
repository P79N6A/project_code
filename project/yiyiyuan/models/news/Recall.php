<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_recall".
 *
 * @property string $id
 * @property string $user_id
 * @property string $recive_mobile
 * @property integer $source
 * @property integer $sms_type
 * @property string $content
 * @property integer $status
 * @property string $send_time
 * @property string $create_time
 */
class Recall extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_recall';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'recive_mobile'], 'required'],
            [['user_id', 'loan_id', 'source', 'sms_type', 'status'], 'integer'],
            [['send_time', 'create_time'], 'safe'],
            [['recive_mobile'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'recive_mobile' => 'Recive Mobile',
            'source' => 'Source',
            'sms_type' => 'Sms Type',
            'status' => 'Status',
            'send_time' => 'Send Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 根据条件查询
     * @param $userId   用户id
     * @param $loanId   借款id
     * @param int $type 召回类型 1:放款后 2:还款后 3:老用户 4:历史驳回 5:距还款时间3天
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getRecallByUserId($userId, $loanId, $type = 1)
    {
        if (empty($userId) || empty($loanId)) {
            return null;
        }
        $where = [
            'user_id' => $userId,
            'loan_id' => $loanId,
            'sms_type' => $type
        ];
        $recall = Recall::find()->where($where)->one();
        return $recall;
    }

    /**
     * 新增召回
     * @param $condition
     * @return bool
     */
    public function addRecall($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**老用户、历史驳回添加
     * @param $userId   用户id
     * @param $mobile   用户手机号
     * @param $code     用户来源
     * @param $type 3老用户 4历史驳回
     * @return bool
     */
    public function addRecallByOld($userId = 0, $mobile = '', $code = 0, $type = 0)
    {
        if (empty($userId) || empty($mobile) || empty($code) || empty($type)) {
            return false;
        }
        //查询老用户召回开关
        $configPath = Yii::$app->basePath . "/commands/recall/config.php";
        if (!file_exists($configPath)) {
            return false;
        }
        $config = include($configPath);
        if (!isset($config[$code]) || $config[$code]['is_send_old'] != 1) {
            return false;
        }
        //查询是否已添加召回短信
        $info = self::find()->where(['user_id' => $userId, 'recive_mobile' => $mobile, 'source' => $code, 'sms_type' => $type])->one();
        if ($info) {
            return false;
        }
        $condition = [
            'user_id' => $userId,
            'recive_mobile' => $mobile,
            'source' => $code,
            'sms_type' => $type
        ];
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 修改
     * @param $condition
     * @return bool
     */
    public function updateRecall($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
