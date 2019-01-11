<?php

namespace app\models\news;

use app\models\BaseModel;

/**
 * This is the model class for table "yi_mobile".
 *
 * @property integer $id
 * @property string $mobile
 * @property string $name
 * @property integer $status
 * @property integer $type
 * @property string $create_time
 */
class Mobile extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_mobile';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'required'],
            [['status', 'type', 'user_id', 'channel', 'days', 'sms_type', 'version'], 'integer'],
            [['create_time', 'last_modify_time', 'send_time'], 'safe'],
            [['number'], 'number'],
            [['mobile', 'name'], 'string', 'max' => 20],
            [['send_content'], 'string', 'max' => 255],
            [['title'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'          => 'ID',
            'mobile'      => 'Mobile',
            'name'        => 'Name',
            'status'      => 'Status',
            'type'        => 'Type',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['mobile' => 'mobile']);
    }

    /**
     * 锁定
     */
    public function lockAll($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $rows = static::updateAll(['status' => '1'], ['id' => $ids]);
        return $rows;
    }

    /**
     * 保存为锁定: 锁定当前纪录
     * @return  bool
     */
    public function lock() {
        $result = $this->save();
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status           = '1';
            $result                 = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    //处理失败
    public function fail() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status           = '3';
            $result                 = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    //处理成功
    public function success() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status           = '2';
            $result                 = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    //优惠券处理成功
    public function coouponSuccess() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            if ($this->sms_type == 3) {
                $this->status = '2';
            } else {
                $this->status = '4';
            }
            $result = $this->save();
        } catch (Exception $e) {
            $result = false;
        }
        return $result;
    }

    public function add($data) {
        $data['create_time']      = $data['last_modify_time'] = date("Y-m-d H:i:s");
        $error                    = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

}
