<?php

namespace app\modules\balance\models\yx;

use Yii;


/**
 * This is the model class for table "yx_user".
 *
 * @property integer $id
 */
class YxUser extends \app\modules\balance\models\yx\YxBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yx_user';
    }

   public function getOne($user_id){

       $data = static::find()->where(['user_id'=>$user_id])->one();
       return $data;
   }


}