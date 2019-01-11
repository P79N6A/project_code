<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "st_credit_request".
 *
 * @property string $credit_id
 * @property string $mobile
 * @property integer $aid
 * @property string $create_time
 * @property string $modify_time
 * @property string $basic_id
 * @property string $credit_data
 */
class StCreditRequest extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_credit_request';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid'], 'required'],
            [['aid', 'basic_id','afbase_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['credit_data'], 'string'],
            [['mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'credit_id' => '授信评测ID（唯一）',
            'mobile' => '借款手机号',
            'aid' => '决策请求来源',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
            'basic_id' => 'cloud基本表唯一标识',
            'afbase_id' => '反欺诈af_base表唯一标示',
            'credit_data' => '授信请求数据集',
        ];
    }

    public function saveData($postData)
    {
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $postData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(false, $error);
        }
        $res = $this->save();
        if (!$res) {
            return false;
        }
        return $id = Yii::$app->db->getLastInsertId();
    }

    public function bindCreditRequest($postdata,$ret_info)
    {
        $credit_id = ArrayHelper::getValue($postdata,'credit_id');
        $basic_id = ArrayHelper::getValue($ret_info,'basic_id');
        $afbase_id = ArrayHelper::getValue($ret_info,'base_id');
        $obj = $this->findOne($credit_id);
        if (!$obj) {
            return false;
        }
        if ($basic_id) {
            $obj->basic_id = $basic_id;
        }
        if ($afbase_id) {
            $obj->afbase_id = $afbase_id;
        }
        $obj->modify_time = date('Y-m-d H:i:s');
        $res = $obj->save();
        if (!$res) {
            Logger::dayLog('api/bindRequest',$postdata,$ret_info,$obj->errors);
            return $res;
        }
        return $res;
    }

    public function getOne($credit_id)
    {
        if (empty($credit_id)){
            return False;
        }
        $data_set = self::find()->where(['credit_id' => $credit_id, 'aid'=>16])->orderBy("credit_id desc")->one();
        return $data_set;
    }
}
