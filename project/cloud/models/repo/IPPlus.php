<?php

namespace app\models\repo;

/**
 * This is the model class for table "ip_plus_360".
 *
 * @property integer $id
 * @property integer $ip_prefix
 * @property integer $minip
 * @property integer $maxip
 * @property string $continent
 * @property string $areacode
 * @property string $country
 * @property string $multiarea
 * @property string $user
 */
class IPPlus extends CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ip_plus_360';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ip_prefix', 'minip', 'maxip'], 'integer'],
            [['ip_prefix', 'minip', 'maxip', 'continent'], 'required'],
            [['continent'], 'string', 'max' => 16],
            [['areacode'], 'string', 'max' => 4],
            [['country'], 'string', 'max' => 50],
            [['user'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip_prefix' => 'Ip Prefix',
            'minip' => 'Minip',
            'maxip' => 'Maxip',
            'continent' => 'Continent',
            'areacode' => 'Areacode',
            'country' => 'Country',
            'multiarea' => 'Multiarea',
            'user' => 'User',
        ];
    }

    public function getInfoByIPSeg($ip){
        //转换为整数
        $ipLong = sprintf('%u',ip2long($ip));
        //取出前四位
        $ipPrefix = substr($ipLong, 0, 4);
        $data = static::find()
            ->where(['ip_prefix' => $ipPrefix])
            ->andWhere(['<=', 'minip', $ipLong])
            ->andWhere(['>=', 'maxip', $ipLong])
            ->limit(1)->one();
        $res = $this->mapAwIP($data, $ip);
        return $res;
    }

    private function mapAwIP($data, $ip){
        $res = [];
        if(empty($data)){
            return $res;
        }
        $multiarea = json_decode($data['multiarea'], true);
        if(empty($multiarea)){
            return $res;
        }
        $area = $multiarea[0];
        $res = [
            "ip" => $ip,
            "country" => $data['country'],
            "province" => $area['p'],
            "city" => $area['c'],
            "district" => $area['d'],
            "street" => "",
            "longitude" => $area['j'],
            "latitude" => $area['w'],
            "source" => "aiwen"
        ];
        return $res;
    }
}