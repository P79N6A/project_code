<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_statistics_type".
 *
 * @property string $id
 * @property integer $come_from
 * @property string $title
 * @property integer $status
 * @property string $create_time
 */
class Statistics_type extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_statistics_type';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['come_from', 'status'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['title'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'come_from' => 'Come From',
            'title' => 'Title',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 关联channel_source表
     * @param type $channel_code 渠道码
     * @param type $title 标题
     */
    public function addChannelType($channel_code, $title) {
        $come_model = $this->addList($title, $channel_code);
        $in_model = $this->addList($title . '访问');
        $reg_model = $this->addList($title . '注册');
        if ($in_model && $reg_model) {
            return ['s_type' => $in_model, 's_id' => $reg_model];
        }
        return NULL;
    }

    /**
     * 关联channel_source表
     * @param type $channel_code 渠道码
     * @param type $title 标题
     */
    public function addDownChannelType($channel_code, $title) {
        $come_model = $this->addList($title, $channel_code);
        $reg_model = $this->addList($title . '下载');
        if ($reg_model) {
            return ['s_id' => $reg_model];
        }
        return NULL;
    }

    public function addList($title, $channel_code = '') {
        $model = new self();
        $model->come_from = $channel_code;
        $model->title = $title;
        $model->status = 1;
        $model->create_time = date('Y-m-d H:i:s');
        $error = $model->chkAttributes($model);
        if ($error) {
            print_r($error);
            return FALSE;
        }
        $result = $model->save();
        return $model->id;
    }

}
