<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_tem_quota".
 *
 * @property string $id
 * @property string $user_id
 * @property string $quota
 * @property string $days
 * @property integer $version
 */
class TemQuota extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_tem_quota';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'quota', 'days'], 'required'],
            [['user_id', 'version'], 'integer'],
            [['quota', 'days'], 'number'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'quota' => 'Quota',
            'days' => 'Days',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getByUserId($user_id) {
        $user_id = intval($user_id);
        if (!$user_id) {
            return null;
        }
        return self::find()->where(['user_id' => $user_id])->one();
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/7/10 10:50
     */
    public function addTemQuota($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
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
    public function updateTemQuota($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
