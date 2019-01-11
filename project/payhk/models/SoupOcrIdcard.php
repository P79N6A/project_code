<?php

namespace app\models;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "soup_ocr_idcard".
 *
 * @property string $id
 * @property string $image_id
 * @property string $request_id
 * @property string $code
 * @property string $message
 * @property string $side
 * @property string $name
 * @property string $number
 * @property string $info
 * @property string $validity
 * @property string $type
 * @property string $create_time
 * @property string $modify_time
 */
class SoupOcrIdcard extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soup_ocr_idcard';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['info', 'validity'], 'string'],
            [['create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['image_id', 'request_id', 'message'], 'string', 'max' => 64],
            [['code', 'side', 'type'], 'string', 'max' => 10],
            [['name'], 'string', 'max' => 32],
            [['number'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'image_id' => Yii::t('app', 'Image ID'),
            'request_id' => Yii::t('app', 'Request ID'),
            'code' => Yii::t('app', 'Code'),
            'message' => Yii::t('app', 'Message'),
            'side' => Yii::t('app', 'Side'),
            'name' => Yii::t('app', 'Name'),
            'number' => Yii::t('app', 'Number'),
            'info' => Yii::t('app', 'Info'),
            'validity' => Yii::t('app', 'Validity'),
            'type' => Yii::t('app', 'Type'),
            'create_time' => Yii::t('app', 'Create Time'),
            'modify_time' => Yii::t('app', 'Modify Time'),
        ];
    }

    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $cur_time = date("Y-m-d H:i:s", time());
        $save_data = [
            'image_id'      => ArrayHelper::getValue($data_set, 'image_id'),
            'request_id'    => ArrayHelper::getValue($data_set, 'request_id'),
            'code'          => ArrayHelper::getValue($data_set, 'code'),
            'message'       => ArrayHelper::getValue($data_set, 'message'),
            'side'          => ArrayHelper::getValue($data_set, 'side'),
            'name'          => ArrayHelper::getValue($data_set, 'name'),
            'number'        => ArrayHelper::getValue($data_set, 'number'),
            'info'          => ArrayHelper::getValue($data_set, 'info'),
            'validity'      => ArrayHelper::getValue($data_set, 'validity'),
            'type'          => ArrayHelper::getValue($data_set, 'type'),
            'create_time'   => $cur_time,
            'modify_time'   => $cur_time,
        ];
        $errors = $this->chkAttributes($save_data);
        if ($errors){
            Logger::dayLog('soup/distinguish', '保存数据错误', json_encode($errors));
            return $this->returnError(null, implode('|', $errors));
        }
        return $this->save();
    }
}