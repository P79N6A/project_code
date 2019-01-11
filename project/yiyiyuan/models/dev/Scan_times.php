<?php

namespace app\models\dev;

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
class Scan_times extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_scan_times';
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

    /**
     * 判断弹层的显示次数
     * @param type $mark 0：全部时间浏览次数 1：当天浏览次数
     */
    public function getScanCount($mobile, $type, $mark = 0) {
        if ($mark == 0) {
            $scan_count = Scan_times::find()->where(['mobile' => $mobile, 'type' => $type])->count();
        } else {
            $scan_count = Scan_times::find()->where(['mobile' => $mobile, 'type' => $type])->andFilterWhere(['>=', 'create_time', date('Y-m-d 00:00:00')])->count();
        }
        if ($scan_count == 0) {
            $scan = new Scan_times();
            $scan->mobile = $mobile;
            $scan->type = $type;
            $scan->create_time = date('Y-m-d H:i:s');
            $scan->save();
        }
        return $scan_count;
    }

    /**
     * 获取用户是否显示活动弹层
     * 规则：
     * 1：活动时间范围之外不显示
     * 2：已经参与过活动的用户不显示
     * 3：当天显示过活动的用户当天不显示
     * @param string $beginTime 活动开始时间 ("2017-04-24 00:00:00")
     * @param int $days 活动天数
     * @param int $mobile 用户手机号
     * @param int $isShowType 已经显示过type 18
     * @param int $partakeType 已经参与过type 19
     * @return bool false:不显示 true:显示
     */
    public function isShow($beginTime, $days, $mobile, $isShowType, $partakeType)
    {
        $endTime = date("Y-m-d 00:00:00",strtotime("+".$days." days", strtotime($beginTime)));
        $nowTime = date('Y-m-d H:i:s');
        //不在活动范围内
        if($nowTime > $endTime || $nowTime < $beginTime){
            return false;
        }
        //已经参与过活动
        $isPartake = self::find()->where(['mobile' => $mobile,'type' => $partakeType])->one();
        if($isPartake){
            return false;
        }
        //当天已经显示过活动弹框
        $today_start = date('Y-m-d 00:00:00');
        $today_end = date('Y-m-d 23:59:59');
        $where = [
            'AND',
            ['mobile' => $mobile],
            ['type' => $isShowType],
            ['between', 'create_time', $today_start, $today_end],
        ];
        $isShow = self::find()->where($where)->one();
        if($isShow){
            return false;
        }
        $this->mobile = $mobile;
        $this->type = $isShowType;
        $this->create_time = date('Y-m-d H:i:s');
        $this->save();
        return true;
    }

    /**
     *  添加一条记录
     * @param array $condition
     * @return bool
     */
    public function addScan($condition)
    {
        if(!is_array($condition) || empty($condition)){
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->create_time = date('Y-m-d H:i:s');
        return $this->save();
    }
}
