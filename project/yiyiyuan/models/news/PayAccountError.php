<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_pay_account_error".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $type
 * @property string $res_code
 * @property string $res_msg
 * @property integer $status
 * @property string $create_time
 * @property integer $version
 */
class PayAccountError extends BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_pay_account_error';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'type', 'status', 'version'], 'integer'],
            [['create_time', 'res_json'], 'safe'],
            [['res_code', 'res_msg'], 'string', 'max' => 1024]
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
            'res_code' => 'Res Code',
            'res_msg' => 'Res Msg',
            'status' => 'Status',
            'res_json' => 'Res Json',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * @return string
     */
    public function optimisticLock() {
        return "version";
    }

    public function save_error($condition) {
        if (!$condition || !is_array($condition)) {
            return false;
        }
        $condition['create_time'] = date('Y-m-d H:i:s');
        $condition['version'] = 0;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 查询操作成功记录，根据user_id && type
     * @param $user_id
     * @param $type
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/9/19 11:18
     */
    public function getByUserIdAndType($user_id, $type) {
        if (empty($user_id) || empty($type)) {
            return null;
        }
        return self::find()->where(['user_id' => $user_id, 'type' => $type, 'res_code' => '00000000'])->one();
    }

    /**
     * 获取错误记录
     * @param $user_id
     * @param $type
     * @param $res_code
     * @author 王新龙
     * @date 2018/9/26 19:19
     */
    public function getError($user_id, $type, $res_code) {
        if (empty($user_id) || empty($type) || empty($res_code)) {
            return null;
        }
        return self::find()->where(['user_id' => $user_id, 'type' => $type, 'res_code' => $res_code])->orderBy('id DESC')->one();
    }

    /**
     * 获取最后一条
     * @param $user_id
     * @param $type
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/10/10 16:08
     */
    public function getLastError($user_id, $type) {
        if (empty($user_id) || empty($type)) {
            return null;
        }
        $where = [
            'AND',
            ['user_id' => $user_id],
            ['type' => $type],
            ['!=', 'res_code', '00000000'],
        ];
        return self::find()->where($where)->orderBy('id DESC')->one();
    }

    /**
     * 修改状态为成功
     * @return bool
     * @author 王新龙
     * @date 2018/9/26 19:40
     */
    public function updateStatusSuccess() {
        try {
            $this->status = 1;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}
