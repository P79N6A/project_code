<?php

namespace app\models;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "soup_pic".
 *
 * @property string $id
 * @property string $pic_file_path
 * @property integer $pic_type
 * @property string $code
 * @property string $message
 * @property string $request_id
 * @property string $create_time
 * @property string $modify_time
 */
class SoupPic extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soup_pic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pic_type'], 'integer'],
            [['create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['message', 'request_id', 'pic_id'], 'string', 'max' => 64],
            [['pic_file_path'], 'string', 'max' => 128],
            [['code'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'pic_file_path' => Yii::t('app', 'Pic File Path'),
            'pic_type' => Yii::t('app', 'Pic Type'),
            'code' => Yii::t('app', 'Code'),
            'message' => Yii::t('app', 'Message'),
            'request_id' => Yii::t('app', 'Request ID'),
            'pic_id' => Yii::t('app', 'PIC ID'),
            'create_time' => Yii::t('app', 'Create Time'),
            'modify_time' => Yii::t('app', 'Modify Time'),
        ];
    }

    public function getData($pic_file_path, $pic_type)
    {
        if (empty($pic_file_path) || empty($pic_type)){
            return false;
        }
        $where = [
            'pic_file_path'         => $pic_file_path,
            'pic_type'              => $pic_type,
            'code'                  => 1000
        ];
        return self::find()->where($where) -> orderBy("id desc")->one();
    }

    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $cur_time = date("Y-m-d H:i:s", time());
        $save_data = [
            'pic_file_path'     => ArrayHelper::getValue($data_set, 'pic_file_path'),
            'pic_type'          => ArrayHelper::getValue($data_set, 'pic_type', 1),
            'pic_id'            => ArrayHelper::getValue($data_set, 'pic_id', ''),
            'create_time'       => $cur_time,
            'modify_time'       => $cur_time,
        ];
        $errors = $this->chkAttributes($save_data);
        if ($errors){
            Logger::dayLog('soup/image_file', '保存数据错误', json_encode($errors));
            return $this->returnError(null, implode('|', $errors));
        }
        return $this->save();
    }

    public function updateData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $this->modify_time = date("Y-m-d H:i:s", time());
        foreach($data_set as $key=>$value){
            $this->$key = $value;
        }
        return $this->save();
    }
}