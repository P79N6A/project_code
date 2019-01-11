<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_do_ious".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $ious_status
 * @property integer $ious_days
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class Do_ious extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_do_ious';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'ious_status', 'ious_days', 'version'], 'integer'],
            [['ious_status', 'create_time', 'last_modify_time'], 'required'],
            [['create_time', 'last_modify_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'ious_status' => 'Ious Status',
            'ious_days' => 'Ious Days',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function getDoiousByUserID($user_ids) {
        if (empty($user_ids) || !is_array($user_ids)) {
            return false;
        }
        return self::find()->where(['user_id' => $user_ids])->all();
    }

    /**
     * 查询记录，根据user_id
     * @param $user_id
     * @return array|bool|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/7/10 10:51
     */
    public function getByUserId($user_id) {
        if (empty($user_id)) {
            return null;
        }
        return self::find()->where(['user_id' => $user_id])->one();
    }

    public function updateIousStatus($iousInfo) {
        try {
            $this->ious_status = $iousInfo['ious_status'];
            $this->ious_days = $iousInfo['ious_days'];
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/7/10 10:50
     */
    public function addRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $condition['last_modify_time'] = $time;
        $condition['create_time'] = $time;
        $condition['version'] = 0;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/7/10 10:50
     */
    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
