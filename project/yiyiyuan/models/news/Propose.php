<?php

namespace app\models\news;

use Yii;
use app\commonapi\ImageHandler;
use yii\helpers\Html;

/**
 * This is the model class for table "yi_propose".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $content
 * @property string $picture
 * @property string $create_time
 */
class Propose extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_propose';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id'], 'integer'],
            [['content'], 'string'],
            [['create_time'], 'safe'],
            [['picture'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'content' => 'Content',
            'picture' => 'Picture',
            'create_time' => 'Create Time',
        ];
    }

    public function getUser(){
        return $this->hasOne(User::className(),['user_id' => 'user_id']);
    }

    /**
     * 添加投诉记录
     * @param [type] $condition [description]
     */
    public function addPropose($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取图片全路径
     * @return [type] [description]
     */
    public function getPictureUrl(){
        return $this->picture ? (new ImageHandler())->img_domain_url.$this->picture : '';
    }

    /**
     * 获取投诉内容
     */
    public function getContentShow(){
        return Html::encode($this->content);
    }



}
