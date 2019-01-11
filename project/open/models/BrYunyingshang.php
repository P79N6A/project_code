<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "br_yunyingshang".
 *
 * @property string $id
 * @property string $mobile
 * @property string $resourceUrl
 * @property string $status
 * @property string $down_time
 * @property string $last_modify_time
 * @property string $create_time
 */
class BrYunyingshang extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'br_yunyingshang';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'resourceUrl'], 'required'],
            [['down_time', 'last_modify_time', 'create_time'], 'safe'],
            [['mobile', 'status'], 'string', 'max' => 20],
            [['resourceUrl'], 'string', 'max' => 512]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'resourceUrl' => 'Resource Url',
            'status' => 'Status',
            'down_time' => 'Down Time',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function getYunyingshang($mobile)
    {
        if (empty($mobile)) return array();
        $shang_info = self::find()->where(['mobile'=>$mobile])->one();
        if (empty($shang_info)) {
            return array();
        }
        return $shang_info;
    }
    public function addList($condition)
    {
        if (!empty($condition['mobile'])){
            $shang_info = (new self())->getYunyingshang($condition['mobile']);
            if (!empty($shang_info)){
                return $shang_info->updateYunyingshang($condition);
            }
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->create_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    public function updateYunyingshang($condition)
    {
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $result = $this->save();
        return $result;
    }
}
