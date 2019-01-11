<?php

namespace app\models\day;

use Yii;

/**
 * This is the model class for table "yi_user_bank_guide".
 *
 * @property integer $id
 * @property string $user_id
 * @property integer $type
 * @property string $bank_abbr
 * @property string $bank_name
 * @property string $province
 * @property string $city
 * @property string $area
 * @property string $sub_bank
 * @property string $card
 * @property string $bank_mobile
 * @property integer $default_bank
 * @property integer $status
 * @property integer $verify
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $is_new
 */
class User_bank_guide extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_user_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'type', 'default_bank', 'status', 'verify', 'is_new'], 'integer'],
            [['bank_mobile'], 'required'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['bank_abbr', 'bank_name', 'province', 'city', 'area'], 'string', 'max' => 20],
            [['sub_bank', 'card'], 'string', 'max' => 64],
            [['bank_mobile'], 'string', 'max' => 12]
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
            'bank_abbr' => 'Bank Abbr',
            'bank_name' => 'Bank Name',
            'province' => 'Province',
            'city' => 'City',
            'area' => 'Area',
            'sub_bank' => 'Sub Bank',
            'card' => 'Card',
            'bank_mobile' => 'Bank Mobile',
            'default_bank' => 'Default Bank',
            'status' => 'Status',
            'verify' => 'Verify',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'is_new' => 'Is New',
        ];
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/8/3 14:19
     */
    public function addRecord($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['is_new'] = 1;
        $data['status'] = 1;
        $data['default_bank'] = 0;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
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
     * @date 2018/8/3 14:19
     */
    public function updateRecord($condition) {
        if (!is_array($condition) || empty($condition)) {
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

    /**
     * 获取记录,根据user_id
     * @param $user_id
     * @param int $type 0储蓄卡 1借记卡
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/2 20:52
     */
    public function getByUserId($user_id, $type = 0) {
        if (empty($user_id)) {
            return null;
        }
        $where = [
            'user_id' => $user_id,
            'type' => $type,
            'status' => 1
        ];
        return self::find()->where($where)->one();
    }

    /**
     * 获取记录，根据user_id
     * @param $user_id
     * @param int $type 0储蓄卡 1借记卡
     * @param array $order
     * @return array|null|\yii\db\ActiveRecord[]
     * @author 王新龙
     * @date 2018/8/2 21:25
     */
    public function listByUserId($user_id, $type = 0, $order = ['last_modify_time' => SORT_DESC, 'id' => SORT_DESC]) {
        if (empty($user_id)) {
            return null;
        }
        $where = [
            'user_id' => $user_id,
            'type' => $type,
            'status' => 1
        ];
        return self::find()->where($where)->orderBy($order)->all();
    }

    /**
     * 获取记录，根据card
     * @param $card
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/3 15:34
     */
    public function getByCard($card) {
        if (empty($card)) {
            return null;
        }
        return self::find()->where(['card' => $card, 'status' => 1])->one();
    }
}
