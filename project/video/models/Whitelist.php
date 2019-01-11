<?php

namespace app\models;

use Yii;
class Whitelist extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%whitelist}}';
    }
	/**
	 * 获取某商户的可用ip列表
	 */
	public function getValidIps($aid){
		$data = self::find()->where(["aid"=>$aid])->all();
		if(empty($data)){
			return null;
		}
		foreach($data as $o){
			if( $o -> status == 1){
				$ips[] = $o->ip;
			}
		}
		return $ips;
	}
	/**
	 * 验证ip是否正确
	 */
	public function validIp($aid, $ip){
		$ips = $this->getValidIps($aid);
		if( empty($ips) ){
			return false;
		}
		
		return in_array($ip,$ips);
	}
}
