<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "xhh_identity".
 *
 * @property integer $id
 * @property string $name
 * @property string $idcard
 * @property string $data
 * @property string $image
 * @property integer $type
 * @property string $create_time
 */
class Identity extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'xhh_identity';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['type'], 'integer'],
            [['create_time'], 'safe'],
            [['name', 'idcard'], 'string', 'max' => 20],
            [['image'], 'string', 'max' => 100],
            [['data'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'idcard' => 'Idcard',
            'data' => 'Data',
            'image' => 'Image',
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }

    public function addRecord($content) {
        $o = new self;
        $data = [
            'name' => isset($content['name']) ? $content['name'] : '',
            'idcard' => isset($content['idcard']) ? $content['idcard'] : '',
            'data' => isset($content['data']) ? json_encode($content['data']) : '',
            'image' => isset($content['image']) ? $content['image'] : '',
            'type' => isset($content['type']) ? $content['type'] : 1,
            'create_time' => date('Y-m-d H:i:s'),
        ];
        // 保存数据
        $o->attributes = $data;
        return $o->save();
    }

}
