<?php

namespace app\modules\balance\models\zrys;

use Yii;
use yii\helpers\ArrayHelper;


class ZrysPostpone extends \app\modules\balance\models\zrys\ZrysBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_ious';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'order_id','status', 'chase_amount', 'start_time', 'end_time', 'invalid_time','create_time','last_modify_time','version'], 'required'],
            [['user_id', 'order_id'], 'integer'],
            [['create_time', 'last_modify_time','invalid_time'], 'safe'],
            [['create_time', 'limit_start_time', 'limit_end_time'], 'string', 'max' => 50],

        ];
    }

    public function check($id){

        $data = static::find()->where(['id'=>$id])->one();

        var_dump($data);die;
    }

}