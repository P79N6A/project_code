<?php

namespace app\models;

use app\models\ygy\YgyUser;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "st_mobile_white_list".
 *
 * @property string $id
 * @property string $mobile
 * @property integer $type
 * @property string $create_time
 */
class MobileWhiteList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_mobile_white_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile'], 'required'],
            [['type'], 'integer'],
            [['create_time'], 'safe'],
            [['mobile'], 'string', 'max' => 20]
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
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }
    public function getIsWhilte($mobile)
    {
        if (empty($mobile)){
            return 0;
        }
        //var_dump($mobile);
        $while_info = $this->getUserForMobile(['mobile'=>$mobile, 'type'=>1]);
        if (empty($while_info)){
            return 0;
        }
        return 1;

    }
    private function getUserForMobile($where) {
        return $this->find()->where($where)->limit(1)->one();
    }
}
