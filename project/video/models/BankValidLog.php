<?php

namespace app\models;

/**
 * This is the model class for table "{{%bank_valid_log}}".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $cardno
 * @property string $idcard
 * @property string $username
 * @property string $phone
 * @property integer $status
 * @property string $create_time
 */
class BankValidLog extends BaseModel {
    // 状态
    const STATUS_INIT = 0; // 初始
    const STATUS_ING = 1; // 处理中
    const STATUS_OK = 2; // 成功
    const STATUS_FAIL = 11; // 失败

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%bank_valid_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'cardno', 'idcard', 'username', 'phone', 'create_time'], 'required'],
            [['aid', 'status', 'error_code'], 'integer'],
            [['create_time'], 'safe'],
            [['cardno'], 'string', 'max' => 50],
            [['idcard', 'username', 'phone'], 'string', 'max' => 20],
            [['error_msg'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'cardno' => '银行卡号',
            'idcard' => '身份证号',
            'username' => '姓名',
            'phone' => '银行留存电话',
            'status' => '状态:0:初始; 1:处理中; 2:验证成功; 11:验证失败',
            'error_code' => '错误码',
            'error_msg' => '错误原因',
            'create_time' => '创建时间',
        ];
    }
    /**
     * 每日同一身份证三次查询
     * @param string $idcard
     * @return bool
     */
    public function chkQueryNum($idcard) {
        if (!$idcard) {
            return false;
        }
        $today = date('Y-m-d');
        $total = self::find()->where(['idcard' => $idcard])
            ->andWhere(['>=', 'create_time', $today])
            ->count();

        // 每日 限定为5次
        $limit = 5;
        return $total < $limit;
    }
    /**
     * 是否存在在日志当中
     * @param [] $data
     * @return bool
     */
    public function existOne($data) {
        if (!is_array($data)) {
            return false;
        }
        $where = [
            'cardno' => $data['cardno'],
            'idcard' => $data['idcard'],
            'username' => $data['username'],
            'phone' => $data['phone'],
        ];
        return self::find()->where($where)->one();
    }
    /**
     * 四要素验证失败后缓存3天
     * @param [] $data
     * @return bool
     */
    public function existSameFail($data) {
        if (!is_array($data)) {
            return null;
        }
        $daybefore = date('Y-m-d', strtotime('-3 day'));
        $where = [
            'AND',
            [
                'cardno' => $data['cardno'],
                'idcard' => $data['idcard'],
                'username' => $data['username'],
                'phone' => $data['phone'],
                'status' => static::STATUS_FAIL,
            ],
            ['>', 'create_time', $daybefore],
        ];
        return self::find()->where($where)->limit(1)->one();
    }
    /**
     * 保存到数据库中
     */
    public function savaData($postData) {
        if (!is_array($postData)) {
            return false;
        }
        $data = [
            'aid' => $postData['aid'],
            'cardno' => $postData['cardno'],
            'idcard' => $postData['idcard'],
            'username' => $postData['username'],
            'phone' => $postData['phone'],
            'error_code' => 0,
            'error_msg' => '',
            'status' => static::STATUS_INIT,
            'create_time' => date('Y-m-d H:i:s'),
        ];

        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(false, implode("|", $error));
        }
        return $this->save();
    }
}
