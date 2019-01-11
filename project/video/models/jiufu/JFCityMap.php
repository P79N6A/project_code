<?php

namespace app\models\jiufu;

/**
 * This is the model class for table "jf_city_map".
 *
 * @property integer $id
 * @property integer $jf_id
 * @property string $jf_name
 * @property integer $local_id
 * @property string $local_name
 * @property string $create_time
 */
class JFCityMap extends \app\models\BaseModel {
	/**
	 * @inheritdoc
	 */
	public static function tableName() {
		return 'jf_city_map';
	}

	/**
	 * @inheritdoc
	 */
	public function rules() {
		return [
			[['jf_id', 'jf_name', 'local_id', 'local_name', 'create_time'], 'required'],
			[['jf_id', 'local_id'], 'integer'],
			[['create_time'], 'safe'],
			[['jf_name', 'local_name'], 'string', 'max' => 50],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels() {
		return [
			'id' => 'id',
			'jf_id' => '9富id',
			'jf_name' => '9富城市名',
			'local_id' => '本地id',
			'local_name' => '本地城市名',
			'create_time' => '创建时间',
		];
	}
	/**
	 * 获取玖富的城市编码
	 * @param  int $county_id
	 * @param  int $city_id
	 * @param  int $province_id
	 * @return int
	 */
	public function getCityCode($county_id, $city_id, $province_id) {
		$local_ids = [(int)$county_id, (int)$city_id, (int)$province_id];
		$data = static::find()->where(['local_id' => $local_ids])->limit(10)->all();
		if( empty($data) ){
			return 0;
		}
		
		// 从县->市->省查找
		$jf_id = 0;
		$map = \yii\helpers\ArrayHelper::map($data, 'local_id', 'jf_id');
		foreach($local_ids as $local_id){
			if(isset($map[$local_id]) && $map[$local_id]){
				$jf_id = $map[$local_id];
				break;
			}
		}
		return $jf_id;
	}
}
