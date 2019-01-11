<?php

namespace app\models\dev;

use Yii;

class Areas extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_areas';
    }

    /**
     * 
     * @param type $code 区域编码
     */
    public static function getAreaByCode($code) {
        if (strlen($code) < 2 || strlen($code) > 6) {
            return false;
        }
        $area = Areas::find()->where(['code' => $code])->one();
        return $area;
    }

    /**
     * 以pID查询所有数据
     * @param type $pID
     * @return boolean
     */
    public static function getAreaByPid($pID) {
        if (is_null($pID)) {
            return false;
        }
        $area = Areas::find()->where(['pID' => $pID])->all();
        return $area;
    }

    /**
     * 通过code获取区域的省市区编码
     * @param type $code
     * @return boolean
     */
    public static function getProCityArea($code) {
        if (strlen($code) < 2 || strlen($code) > 6) {
            return false;
        }
        $area = Areas::find()->where(['code' => $code])->one();
        switch (strlen($code)) {
            case 2:
                $array['province'] = !empty($area) ? $area->id : NULL;
                break;
            case 4:
                $array['city'] = !empty($area) ? $area->id : NULL;
                $proId = substr($code, 0, 2);
                $province = Areas::find()->where(['code' => $proId])->one();
                $array['province'] = !empty($province) ? $province->id : NULL;
                break;
            case 6:
                $array['area'] = !empty($area) ? $area->id : NULL;
                $cityId = substr($code, 0, 4);
                $city = Areas::find()->where(['code' => $cityId])->one();
                $array['city'] = !empty($city) ? $city->id : NULL;
                $proId = substr($code, 0, 2);
                $province = Areas::find()->where(['code' => $proId])->one();
                $array['province'] = !empty($province) ? $province->id : NULL;
                break;
        }
        return $array;
    }

    /**
     * 通过code获取区域的省市区名称
     * @param type $code
     * @return String
     */
    public static function getProCityAreaName($code) {
        if (strlen($code) < 2 || strlen($code) > 6) {
            return false;
        }
        $area = Areas::find()->where(['code' => $code])->one();
        $area_name = '';
        switch (strlen($code)) {
            case 2:
                $area_name .=!empty($area) ? $area->name : NULL;
                break;
            case 4:
                $city_name = !empty($area) ? $area->name : NULL;
                $proId = substr($code, 0, 2);
                $province = Areas::find()->where(['code' => $proId])->one();
                $province_name = !empty($province) ? $province->name : NULL;
                $area_name .=$province_name . $city_name;
                break;
            case 6:
                $areas_n = !empty($area) ? $area->name : NULL;
                $cityId = substr($code, 0, 4);
                $city = Areas::find()->where(['code' => $cityId])->one();
                $city_name = !empty($city) ? $city->name : NULL;
                $proId = substr($code, 0, 2);
                $province = Areas::find()->where(['code' => $proId])->one();
                $province_name = !empty($province) ? $province->name : NULL;
                $area_name .=$province_name . $city_name . $areas_n;
                break;
        }
        return $area_name;
    }

    public static function getAllAreas() {
        $area_key = 'areas_list';
        $area_list = Yii::$app->redis->get($area_key);
        if (!empty($area_list)) {
            return $area_list;
        } else {
            $provice = Areas::getAreaByPid(0);
            foreach ($provice as $key => $val) {
                $list[$key]['code'] = $val['code'];
                $list[$key]['name'] = $val['name'];
                $city = Areas::getAreaByPid($val->id);
                foreach ($city as $k => $v) {
                    $list[$key]['area'][$k]['code'] = $v['code'];
                    $list[$key]['area'][$k]['name'] = $v['name'];
                    $area = Areas::getAreaByPid($v->id);
                    foreach ($area as $m => $n) {
                        $list[$key]['area'][$k]['area'][$m]['code'] = $n['code'];
                        $list[$key]['area'][$k]['area'][$m]['name'] = $n['name'];
                    }
                }
            }
            $list = json_encode($list);
            Yii::$app->redis->setex($area_key, 2592000, $list);
            return $list;
        }
    }

//     /**
//      * @inheritdoc
//      */
//     public function rules()
//     {
//         return [
//         ];
//     }
//     /**
//      * @inheritdoc
//      */
//     public function attributeLabels()
//     {
//         return [
//             'id' => 'ID',
//         ];
//     }
}
