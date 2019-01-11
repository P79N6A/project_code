<?php
/**
 * 一亿元用户表
 * @author 孙瑞
 */
namespace app\models\yiyiyuan;

class YiUser extends YyyBase{
    public static function tableName(){
        return 'yi_user';
    }

    public function getMobilesByUserIds($userIds){
		if(!$userIds){
			return [];
		}
		$mobiles = self::find()->where(['in','user_id',$userIds])->select(['user_id','mobile'])->all();
		if(!$mobiles){
			return [];
		}
		return $mobiles;
	}
}
