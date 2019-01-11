<?php

namespace app\models\open;

use Yii;

/**
 * This is the model class for table "rt_seting".
 *
 * @property integer $id
 * @property integer $aid
 * @property $day_max_mount
 * @property $create_time

 */
class RtSetting extends \app\models\open\OpenBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rt_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'day_max_mount', 'create_time'], 'required'],

            [['create_time'], 'safe'],



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
            'day_max_mount' => 'Day Max Mount',
            'create_time' => 'Create Time',

        ];
    }

    public function saveNotifyStatus($data) {

        $this->day_max_mount = $data['day_max_mount'];
        $result = $this->save();
        return $result;
    }

    public function updateData($data)
    {
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        } else {
            return $result;
        }
    }

    public function getRemitOne($client_id)
    {
        return self::find()->where(['client_id' => $client_id])->one();
    }
}