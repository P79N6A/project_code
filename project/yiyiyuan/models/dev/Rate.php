<?php

namespace app\models\dev;

use app\commonapi\Common;
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
class Rate extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_rate';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    public function getLastRate() {
        $data = Rate::find()->orderBy('create_time DESC')->limit('1')->one();

        return $data;
    }

    public function getRateByAll() {
        $lastRate = $this->getLastRate();

        $rateArr = array();
        if (!empty($lastRate)) {
            $overdue_rate = $lastRate->overdue_rate;
            $fund_cost = $lastRate->fund_cost;
            $i = 7;

            for ($i; $i <= 31; $i++) {
                $rateArr[$i] = round(((floatval($overdue_rate) / 100) + ($i * (floatval($fund_cost) / 100) / 365)) / $i, 4);
            }
        }
        return $rateArr;
    }

    public function getRateToArr() {
        $lastRate = $this->getLastRate();

        $rateArr = array();
        if (!empty($lastRate)) {
            $overdue_rate = $lastRate->overdue_rate;
            $fund_cost = $lastRate->fund_cost;
            $i = 7;

            for ($i; $i <= 31; $i++) {
                $temp = array();
                $temp['day'] = $i;
                $temp['rate'] = round(((floatval($overdue_rate) / 100) + ($i * (floatval($fund_cost) / 100) / 365)) / $i, 4);
                if ($this->dayIsShow($i)) {
                    array_push($rateArr, $temp);
                }
            }
        }
        return $rateArr;
    }

    public function getRateToNewArr() {
        $lastRate = $this->getLastRate();

        $rateArr = array();
        if (!empty($lastRate)) {
            $overdue_rate = $lastRate->overdue_rate;
            $fund_cost = $lastRate->fund_cost;
            $i = 7;
            $days = [7, 14, 21, 28];
            for ($i; $i <= 31; $i++) {
                if (!in_array($i, $days)) {
                    continue;
                }
                $temp = array();
                $temp['day'] = $i;
                $temp['rate'] = 0.0005;
                if ($this->dayIsShow($i)) {
                    array_push($rateArr, $temp);
                }
            }
        }
        return $rateArr;
    }

    public function dayIsShow($day) {
        $start = strtotime('2016-02-05');
        $end = strtotime('2016-02-15');
        $nowtime = time();
        $repayDay = $nowtime + $day * 24 * 60 * 60;
        if ($repayDay > $start && $repayDay < $end) {
            return false;
        }
        return true;
    }

    public function getRateByDay($day = 7, $time = '') {
        $lastRate = $this->getLastRate();
        $overdue_rate = $lastRate->overdue_rate;
        $fund_cost = $lastRate->fund_cost;

        $day_rate = round(((floatval($overdue_rate) / 100) + ($day * (floatval($fund_cost) / 100) / 365)) / $day, 4);
        return $day_rate;
    }

}
