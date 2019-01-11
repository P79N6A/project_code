<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "st_strategy_request".
 *
 * @property string $id
 * @property integer $aid
 * @property integer $req_id
 * @property integer $user_id
 * @property integer $loan_id
 * @property integer $status
 * @property string $callbackurl
 * @property string $create_time
 * @property string $modify_time
 * @property integer $version
 */
class StrategyRequest extends BaseModel
{
    const INIT_STATUS = 0; //初始状态
    const OPERA_DOING = 1; //运营商分析中
    const OPERA_SUCCESS = 2; //运营商分析成功
    const JAVA_DOING = 3; //java决策分析中
    const JAVA_SUCCESS = 4; //java决策分析成功
    const STATUS_TEST_AllIN_LOCK = 5; // allin埋点测试锁定状态
    const STATUS_TEST_AllIN_SUCCESS = 6; // allin埋点测试完成状态
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_strategy_request';
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'user_id', 'req_id', 'loan_id', 'status', 'come_from', 'version'], 'integer'],
            [['create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['callbackurl'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'req_id' => 'Req ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'status' => 'Status',
            'callbackurl' => 'Callbackurl',
            'create_time' => 'Create Time',
            'come_from' => 'Come From',
            'modify_time' => 'Modify Time',
            'version' => 'Version',
        ];
    }
    /**
     * 表关联关系
     */
    public function getRequest() {
        return $this->hasOne(Request::className(), ['req_id' => 'id']);
    }

    public function saveRequest($postData)
    {
        $addData = [
            'aid' => ArrayHelper::getValue($postData,'aid'),
            'req_id' => ArrayHelper::getValue($postData,'req_id'),
            'user_id' => ArrayHelper::getValue($postData,'user_id'),
            'loan_id' => ArrayHelper::getValue($postData,'loan_id',0),
            'callbackurl' => ArrayHelper::getValue($postData,'callbackurl'),
            'come_from' => ArrayHelper::getValue($postData,'come_from'),
        ];
        $nowtime = date('Y-m-d H:i:s');
        $addData['status'] = self::INIT_STATUS;
        $addData['create_time'] = $nowtime;
        $addData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($addData);
        if ($error) {
            return $this->returnError(false, $error);
        }
        $res = $this->save();
        if (!$res) {
            return false;
        }
        return $id = Yii::$app->db->getLastInsertId();
    }

    public function getByStatus($status,$come_from,$aid){
        $where = [
            'and',
            ['status' => $status],
            ['aid' => $aid],
            ['come_from' => $come_from],
        ];
        $afraudDatas = static::find()
            ->where($where)
            ->limit(500)
            ->asArray()
            ->all();
        return $afraudDatas;
    }

    public function getByStatusOnTime($status,$come_from,$aid){
        $time = date("Y-m-d H:i:s",strtotime("-20 minute"));
        $where = [
            'and',
            ['status' => $status],
            ['come_from'=>$come_from],
            ['aid'=>$aid],
            ['>=','create_time',$time],
        ];
        $afraudDatas = static::find()
            ->where($where)
            ->limit(500)
            ->asArray()
            ->all();
        return $afraudDatas;
    }

    /**
     * @param $ids
     * @param $status
     * @return int
     */
    public function lockStrateReq($ids, $status){
        $now = date('Y-m-d H:i:s', time());
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $res = static::updateAll([
            'status' => $status,
            'modify_time' => $now
        ], ['id' => $ids]);
        return $res;
    }

    /**
     * @param $id
     * @param $status
     * @return bool
     */
    public function updateStatus($id, $status) {
        if (!$id) {
            return false;
        }
        if (!is_numeric($status)) {
            return false;
        }
        $oStrategy = static::find()->where(['id' => $id])->limit(1)->one();
        if (!$oStrategy) {
            return false;
        }
        $oStrategy->status = $status;
        $oStrategy->modify_time = date('Y-m-d H:i:s', time());
        return $oStrategy->save();
    }

}