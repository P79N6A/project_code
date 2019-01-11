<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_banner".
 *
 * @property string $id
 * @property integer $admin_id
 * @property integer $type
 * @property string $url
 * @property string $click_url
 * @property integer $status
 * @property string $create_time
 * @property string $last_modify_time
 * @property string $version
 */
class Banner extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_banner';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['admin_id', 'type', 'url', 'create_time', 'last_modify_time', 'version'], 'required'],
            [['admin_id', 'type', 'status', 'version'], 'integer'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['url', 'click_url'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => 'Admin ID',
            'type' => 'Type',
            'url' => 'Url',
            'click_url' => 'Click Url',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    /**
     *
    $condition = array(
    'status'=>$status,
    'type'=>$type,
    );
     * @param type $condition
     * @return type
     */
    public function getBanner($condition) {
        if (!empty($condition)) {
            $bankNotice = $this->find()->where($condition)->orderBy('last_modify_time desc')->all();
        } else {
            $bankNotice = $this->find()->orderBy('last_modify_time desc')->all();
        }
        return $bankNotice;
    }

    public static function addBanner($condition) {
        if (!isset($condition['admin_id']) || !isset($condition['type']) || !isset($condition['url']) || !isset($condition['status'])) {
            return false;
        }
        $bannerModel = new Banner();
        $now_time = date('Y-m-d H:i:s');
        foreach ($condition as $key => $val) {
            $bannerModel->{$key} = $val;
        }
        $bannerModel->create_time = $now_time;
        $bannerModel->last_modify_time = $now_time;
        $bannerModel->version = 1;
        $result = $bannerModel->save();
        return $result;
    }

    public function updateBanner($condition) {
        if (empty($condition)) {
            return false;
        }
        $now_time = date('Y-m-d H:i:s');
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->last_modify_time = $now_time;
        $this->version = $this->version + 1;
        $result = $this->save();
        return $result;
    }

}
