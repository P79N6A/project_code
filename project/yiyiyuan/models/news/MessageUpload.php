<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_message_upload".
 *
 * @property integer $id
 * @property integer $mid
 * @property string $url
 * @property string $create_time
 * @property integer $version
 */
class MessageUpload extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_message_upload';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
                [['mid', 'version'], 'integer'],
                [['create_time'], 'safe'],
                [['url'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'          => 'ID',
            'mid'         => 'Mid',
            'url'         => 'Url',
            'create_time' => 'Create Time',
            'version'     => 'Version',
        ];
    }

    public function addData($mid, $url) {
        $condition['mid']        = $mid;
        $condition['url']        = $url;
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error                    = $this->chkAttributes($condition);
        if ($error) {
            var_dump($error);
            die;
            return $error;
        }
        return $this->save();
    }

}
