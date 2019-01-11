<?php

namespace app\models\phonelab;

use Yii;
use app\models\repo\CloudBase;
use app\common\Logger;

/**
 * This is the model class for table "dc_tellab_record".
 *
 * @property string $id
 * @property string $phone
 * @property string $last_query_time
 * @property string $create_time
 */
class DcTellabRecord extends CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_tellab_record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['last_query_time', 'create_time'], 'required'],
            [['last_query_time', 'create_time'], 'safe'],
            [['phone'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '手机号',
            'last_query_time' => '请求时间',
            'create_time' => '请求时间',
            'user_id' => 'user_id',
        ];
    }

    public function getRecord($where, $field = '*')
    {
        if (empty($where)) {
            return false;
        }
        return static::find()->where($where)->select($field)->asArray()->limit(500)->orderBy('create_time desc')->all();
    }

    public function saveData($data)
    {
        $time = date("Y-m-d H:i:s"); 
        $data['create_time'] = $time;
        $data['last_query_time'] = $time;
        $error = $this->chkAttributes($data); 
        if ($error) { 
            Logger::dayLog("DcTellabRecord","save failed", $data, $error);
            return false;
        }
        return $this->save();
    }


}
