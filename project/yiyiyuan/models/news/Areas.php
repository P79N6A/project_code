<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_areas".
 *
 * @property integer $id
 * @property integer $code
 * @property integer $pID
 * @property string $name
 * @property string $Intro
 * @property string $addDate
 */
class Areas extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_areas';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['code', 'pID'], 'integer'],
            [['pID', 'name'], 'required'],
            [['addDate'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['Intro'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'code' => 'Code',
            'pID' => 'P ID',
            'name' => 'Name',
            'Intro' => 'Intro',
            'addDate' => 'Add Date',
        ];
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

    public function getName($id) {
        $o = self::findOne($id);
        return $o->name;
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

    /**
     * 获取开户银行地区或开户支行
     * @param int $type     1开户地区 非1开户支行
     * @return mixed
     */
    public function getAreaOrSubBank($type = 1) {
        if ($type == 1) {
            $array = $this->areaArray();
        } else {
            $array = $this->subBankArray();
        }
        return $array[rand(1, count($array))];
    }

    //构造开户地区数据
    private function areaArray() {
        return [
            1 => ['province' => '1', 'city' => '2', 'area' => '10'],
            2 => ['province' => '1', 'city' => '2', 'area' => '3'],
        ];
    }

    //构造开户支行数据
    private function subBankArray() {
        return [
            1 => '中关村支行',
            2 => '苏州街支行',
        ];
    }

}
