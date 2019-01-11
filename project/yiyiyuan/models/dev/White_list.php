<?php

namespace app\models\dev;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class White_list extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_white_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['idno', 'mobile', 'name', 'user_type', 'grade', 'user_id'], 'required'],
            [['user_type', 'grade', 'user_id'], 'integer'],
            [['amount'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['idno', 'mobile'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键，递增',
            'user_id' => '用户ID',
            'idno' => '用户姓名',
            'mobile' => '用户身份证号',
            'name' => '用户手机号码',
            'user_type' => '用户类型：1',
            'grade' => '用户等级：1标识铜牌；2标识银牌；3标识金牌；4标识钻石',
            'amount' => '用户可借款额度',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
        ];
    }

    /**
     * @abstract 通过身份证号码验证用户是否为白名单用户
     * @param [idno]
     * @return [true,false]
     * */
    public function isWhiteList($idno = '') {
        if (empty($idno)) {
            return false;
        }
        $data = $this->findOne(['idno' => $idno]);

        if ($data) {
            return true;
        }

        return false;
    }

    /**
     * 获取最大的id
     */
    public function getMaxId() {
        $id = static::find()->select(['max(id) as id'])->scalar();
        return is_numeric($id) ? $id : 0;
    }

    /**
     * 删除老数据
     */
    public function deleteOld($id) {
        $id = intval($id);
        static::deleteAll("id<={$id}");
    }

    /**
     * 删除单条数据
     */
    public function deleteUser($mobile) {
        static::deleteAll("mobile = '$mobile'");
    }

    /**
     * 创建一条纪录
     */
    public function createData($idno, $name, $mobile, $amount) {
        // 数据
        $create_time = date('Y-m-d H:i:s');
        $data = [
            'name' => $name,
            'mobile' => $mobile,
            'idno' => $idno,
            'user_type' => 1,
            'grade' => 1,
            'amount' => $amount,
            'last_modify_time' => $create_time,
            'create_time' => $create_time,
        ];
        return $data;
    }

    /**
     * 添加一条纪录（如果存在记录则更新记录）
     */
    public static function addBatch($idno, $name, $mobile, $amount, $user_id) {
        if (empty($user_id)) {
            return FALSE;
        }
        // 数据
        $create_time = date('Y-m-d H:i:s');
        // 是否存在
        $o = static::find()->where(['user_id' => $user_id])->one(); 
        if (empty($o)) {
            $o = new self;
            $data = [
                'user_id' => $user_id,
                'name' => $name,
                'mobile' => $mobile,
                'idno' => $idno,
                'user_type' => 1,
                'grade' => 1,
                'amount' => $amount,
                'last_modify_time' => $create_time,
                'create_time' => $create_time,
            ];
        } else {
            $data = [
                'last_modify_time' => $create_time,
            ];
        }
        // 保存数据
        $o->attributes = $data;
        return $o->save();
    }

}
