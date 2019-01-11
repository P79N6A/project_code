<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "xhh_bank_valid_plateform".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $cardno
 * @property string $idcard
 * @property string $username
 * @property string $phone
 * @property string $create_time
 * @property string $order_no
 * @property integer $plateform
 */
class BankValidPlateform extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_bank_valid_plateform';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'cardno', 'idcard', 'username', 'phone', 'create_time', 'order_no', 'plateform'], 'required'],
            [['aid', 'plateform'], 'integer'],
            [['create_time'], 'safe'],
            [['cardno'], 'string', 'max' => 50],
            [['idcard', 'username', 'phone'], 'string', 'max' => 20],
            [['order_no'], 'string', 'max' => 64]
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
            'cardno' => 'Cardno',
            'idcard' => 'Idcard',
            'username' => 'Username',
            'phone' => 'Phone',
            'create_time' => 'Create Time',
            'order_no' => 'Order No',
            'plateform' => 'Plateform',
        ];
    }

    // 保存请求的数据
    public function saveData($postData) {
        //1 数据验证
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(false, "数据不能为空");
        }
        if (empty($postData['username'])) {
            return $this->returnError(false, "用户名不能为空");
        }
        if (empty($postData['idcard'])) {
            return $this->returnError(false, "身份证不能为空");
        }
        if (empty($postData['cardno'])) {
            return $this->returnError(false, "卡号不能为空");
        }
        if (empty($postData['phone'])) {
            return $this->returnError(false, "手机号不能为空");
        }
        $postData['create_time'] = date('Y-m-d H:i:s');
        // 参数检证是否有错
        if ($errors = $this->chkAttributes($postData)) {
            return $this->returnError(false, implode('|', $errors));
        }
         
        $result = $this->save($postData);
        if (!$result) {
            return $this->returnError(false, implode('|', $this->errors));
        }
        return $result;
    }
}
