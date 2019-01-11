<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bd_risk".
 *
 * @property integer $id
 * @property string $name
 * @property string $idno
 * @property string $phone
 * @property string $create_time
 * @property string $modify_time
 * @property string $other_orderid
 * @property string $error_code
 * @property string $error_msg
 * @property string $black_level
 * @property string $black_reason
 * @property string $black_detail
 * @property integer $version
 */
class BdRisk extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bd_risk';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'idcard', 'phone', 'create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['black_detail'], 'string'],
            [['version'], 'integer'],
            [['name', 'idcard'], 'string', 'max' => 30],
            [['phone'], 'string', 'max' => 20],
            [['error_code', 'error_msg'], 'string', 'max' => 50],
            [['black_level'], 'string', 'max' => 10],
            [['black_reason'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'idcard' => 'Idno',
            'phone' => 'Phone',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'other_orderid' => 'Other Orderid',
            'error_code' => 'Error Code',
            'error_msg' => 'Error Msg',
            'black_level' => 'Black Level',
            'black_reason' => 'Black Reason',
            'black_detail' => 'Black Detail',
            'version' => 'Version',
        ];
    }
    public function add($postdata){
        if(empty($postdata)) {
            return false;
        }
        $nowTime = date('Y-m-d H:i:s');
        $postdata['create_time'] = $nowTime;
        $postdata['modify_time'] = $nowTime;
		$errors = $this->chkAttributes($postdata);
        if ($errors) {
			return $this->returnError(null, implode('|', $errors));
		}
        $result = $this->save();
		if (!$result) {
			return $this->returnError(null, implode('|', $this->errors));
		}
        return $this;

    }
    public function updateRisk($postdata){
        $nowTime = date('Y-m-d H:i:s');
        $postdata['modify_time'] = $nowTime;
        $errors = $this->chkAttributes($postdata);
        if ($errors) {
			return $this->returnError(null, implode('|', $errors));
        }
        $result = $this->save();
		if (!$result) {
			return $this->returnError(null, implode('|', $this->errors));
		}
        return $result;
    }
}