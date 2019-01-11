<?php

namespace app\models\down;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "phone_num_list".
 *
 * @property string $phone
 * @property string $id
 */
class PhoneNumList extends DownBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'phone_num_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'required'],
            [['phone'], 'string', 'max' => 20],
            [['phone'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'phone' => '手机号',
            'id' => 'ID',
        ];
    }

    public function getAllByPhones($phone_list){
        return $this->find()->where(['in','phone',$phone_list])->asArray()->all();
    }

    public function saveOne($phone){
        if (!$phone) {
            return false;
        }
        $data['phone'] = $phone;
        $error = $this->chkAttributes($data); 
        if ($error) { 
            Logger::dayLog("PhoneNumList","save failed", $data, $error);
            return false;
        }
        return $this->save();
    }
}
