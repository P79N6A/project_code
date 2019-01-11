<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
use app\models\news\User;

/**
 * This is the model class for table "yi_user_label".
 *
 * @property string $id
 * @property string $mobile
 * @property string $label
 * @property string $create_time
 */
class User_label extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_label';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['create_time'], 'safe'],
            [['mobile', 'label'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'label' => 'Label',
            'create_time' => 'Create Time',
        ];
    }
    /*
     * 是否是后置用户
     */
    public function isChargeUser($mobile){
        $charge_info = self::find()->where(['mobile'=>$mobile, 'label'=>'charge'])->one();
        if(!empty($charge_info)){
            return TRUE;
        }
        return FALSE;
    }

    public function addLabel($condition){
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
