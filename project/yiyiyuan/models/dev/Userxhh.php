<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Userxhh extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_xhh';
    }

	/**
	 * 查询用户在XHH表中是否存在
	 */
	public function getUserinfoByMobile($mobile) {
		if(empty($mobile)){
			return null;
		}
		$userxhh = Userxhh::find()->where(['mobile' => $mobile])->one();
		return $userxhh;
	}
   
    
}
