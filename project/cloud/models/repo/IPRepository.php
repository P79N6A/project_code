<?php

namespace app\models\repo;

/**
 * This is the model class for table "ip_repository".
 *
 * @property integer $id
 * @property string $ip
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $district
 * @property string $street
 * @property integer $longitude
 * @property integer $latitude
 * @property string $create_time
 */
class IPRepository extends RepoBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_repository';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip'], 'required'],
            [['create_time'], 'safe'],
            [['ip'], 'string', 'max' => 15],
            [['source'], 'string', 'max' => 10],
            [['country', 'province', 'city', 'district', 'street','longitude','latitude'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'country' => 'Country',
            'province' => 'Province',
            'city' => 'City',
            'district' => 'District',
            'street' => 'Street',
            'longitude' => 'Longitude',
            'latitude' => 'Latitude',
            'source' => 'Source',
            'create_time' => 'Create Time',
        ];
    }

    public function validateIP($ip){
        $ipReg = "/^(((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))\.){3}((1?\d{1,2})|(2[0-4]\d)|(25[0-5]))$/";
        $result = preg_match($ipReg, $ip);
        return $result;
    }

    public function getInfoByIP($ip){
        $data = static::find()
            ->select(['ip','country','province','city','district','street','longitude','latitude','source'])
            ->where(['ip' => $ip])
            ->asArray()
            ->one();
        return $data;
    }

    public function createData($data)
    {
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        } else {
            return $result;
        }
    }
}