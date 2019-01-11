<?php

namespace app\models\xs;

use Yii;

/**
 * 扩展表gps信息
 *
 * @property string $id
 * @property string $basic_id
 * @property string $latitude
 * @property string $longtitude
 * @property string $accuracy
 * @property string $speed
 * @property string $location
 * @property string $create_time
 */
class XsGps extends \app\models\xs\XsBaseNewModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%gps}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basic_id'], 'integer'],
            [['basic_id','latitude', 'longtitude','create_time'], 'required'],
            [['create_time'], 'safe'],
            [['latitude', 'longtitude', 'accuracy', 'speed'], 'string', 'max' => 64],
            [['location'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'basic_id' => '请求表id',
            'latitude' => 'gps:纬度',
            'longtitude' => 'gps:经度',
            'accuracy' => 'gps:精度',
            'speed' => 'gps:速度',
            'location' => '地址',
            'create_time' => '创建时间',
        ];
    }

    public function saveData($data){
		$time = date("Y-m-d H:i:s");
		$postData = [
            'basic_id'   =>  $data['basic_id'],
            'latitude'   =>  $data['latitude'],
            'longtitude' =>  $data['longtitude'],
            'accuracy'   =>  $this->getValue($data,'accuracy'),
            'speed'      =>  $this->getValue($data,'speed'),
            'location'   =>  $this->getValue($data,'location'),
			'create_time' => $time,
		];

		$error = $this->chkAttributes($postData);
		if ($error) {
			return false;
		}

		return $this->save();
    }
}
