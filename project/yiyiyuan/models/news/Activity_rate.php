<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_activity_share".
 *
 * @property string $id
 * @property string $user_id
 * @property string $mobile
 * @property string $create_time
 */
class Activity_rate extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activity_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['type'], 'integer'],
            [['frined_id'], 'integer'],
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
            'user_id' => 'User ID',
            'type' => '活动加速类型',
            'frined_id' => '好友ID',
            'create_time' => 'create_time',
        ];
    }


    public function save_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    public function update_activity_rate($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function savestatus($data) {
        if (empty($data)) {
            return false;
        }
        try {
            $error= $this->chkAttributes($data);
            if ($error) {
                return false;
            }
            $res = $this->save();
            return $res;
        } catch (Exception $e) {
            return false;
        }
    }

}
