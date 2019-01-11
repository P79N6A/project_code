<?php

namespace app\models\news;

use app\models\BaseModel;
use app\commonapi\Logger;
use app\commonapi\Keywords;
use Yii;

/**
 * This is the model class for table "yi_no_repeat".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $type
 * @property string $last_modify_time
 * @property integer $version
 */
class No_repeat extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_no_repeat';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'type', 'version'], 'integer'],
            [['last_modify_time'], 'safe'],
            [['user_id', 'type'], 'unique', 'targetAttribute' => ['user_id', 'type'], 'message' => 'The combination of User ID and Type has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * @param $condition
     * @return bool
     */
    public function saveNoRepeat() {
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            return $result;
        } catch (\Exception $ex) {
            Logger::errorLog(print_r(array('total' => $ex), true), 'save_repeat');
            return FALSE;
        }
    }

    /**
     * @param $condition
     * @return bool
     */
    public function addNoRepeat($user_id, $type) {
        if (empty($user_id) || empty($type)) {
            return false;
        }
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['user_id'] = $user_id;
        $data['type'] = $type;
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            return $result;
        } catch (\Exception $ex) {
            Logger::errorLog(print_r(array('total' => $ex), true), 'add_repeat');
            return FALSE;
        }
    }

    /**
     * 连点限制
     * @param type $user_id
     * @param type $type 1：发起借款（决策调用） 2：确认借款
     * @return int
     */
    public function norepeat($user_id, $type) {
        if (empty($user_id) || empty($type)) {
            return false;
        }
        $date = date('Y-m-d H:i:s');
        $norepeat = self::find()->where(['user_id' => $user_id, 'type' => $type])->one();
        if (empty($norepeat)) {
            $result = $this->addNoRepeat($user_id, $type);
            if ($result) {
                return true;
            } else {
                return false;
            }
        } else {
            $last_modify_time = $norepeat->last_modify_time;
            $second = Keywords::repeatTime();
            $last_modify_time = strtotime($last_modify_time) + $second[$type];
            $nowtime = strtotime($date);
            if ($nowtime > $last_modify_time) {
                $result = $norepeat->saveNoRepeat();
                if ($result) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

}
