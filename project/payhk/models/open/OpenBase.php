<?php
/**
 * 所有一亿元的数据库表均需要继承此类
 */
namespace app\models\open;

class OpenBase extends \app\models\BaseModel {
    public static function getDb() {
		return \Yii::$app->xhh_open;
	}
	/**
     * Undocumented function
     * 根据订单号查询
     * @param [type] $clientId
     * @return void
     */
    public function getRemitByClientId($clientId){
        $where = [
            'client_id' =>$clientId
        ];
        $data = static::find()->where($where)->one();
        return $data;
    }
}
