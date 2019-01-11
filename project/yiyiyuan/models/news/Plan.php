<?php

namespace app\models\news;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_plan".
 *
 * @property integer $id
 * @property string $name
 * @property integer $fund
 * @property integer $status
 * @property integer $sort_num
 * @property integer $is_accuracy
 * @property string $start_time
 * @property string $end_time
 * @property string $max_estimate
 * @property string $max_real
 * @property string $max_do_estimate
 * @property string $max_do_real
 * @property string $max_success_money
 * @property string $threshold
 * @property integer $admin_id
 * @property string $create_time
 */
class Plan extends BaseModel {

    const INIT_STATUS = 0;
    const LOCK_STATUS = 1;
    const SUCC_STATUS = 2;
    const WAIT_STATUS = 3;
    const CLOSE_STATUS = 4;
    const ACCURATE_STATUS = 5;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_plan';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name', 'fund', 'start_time', 'end_time', 'max_estimate', 'max_real', 'max_do_estimate', 'max_do_real', 'max_success_money', 'threshold', 'admin_id', 'plan_time', 'create_time'], 'required'],
            [['fund', 'status', 'sort_num', 'is_accuracy', 'admin_id'], 'integer'],
            [['start_time', 'end_time', 'create_time', 'plan_time'], 'safe'],
            [['max_estimate', 'max_real', 'max_do_estimate', 'max_do_real', 'max_success_money', 'threshold'], 'number'],
            [['name'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'fund' => 'Fund',
            'status' => 'Status',
            'sort_num' => 'Sort Num',
            'is_accuracy' => 'Is Accuracy',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'max_estimate' => 'Max Estimate',
            'max_real' => 'Max Real',
            'max_do_estimate' => 'Max Do Estimate',
            'max_do_real' => 'Max Do Real',
            'max_success_money' => 'Max Success Money',
            'threshold' => 'Threshold',
            'admin_id' => 'Admin ID',
            'plan_time' => 'Plan Time',
            'create_time' => 'Create Time',
        ];
    }

    public function getIdData($id) {
        if (empty($id)) {
            return false;
        }
        return self::find()->where(['id' => $id])->one();
    }

    /**
     * 获取当前有效排期
     *
     * @return []
     */
    public function getPlans() {
        $nowtime = date('Y-m-d H:i:s');
        $nowdate = date('Y-m-d');
        $where = [
            'AND',
            ['<=', 'start_time', $nowtime],
            ['>=', 'end_time', $nowtime],
            ['in', 'status', [static::INIT_STATUS, static::WAIT_STATUS]],
            ['plan_time' => $nowdate],
        ];
        $plans = static::find()
                ->where($where)
                ->orderBy("sort_num ASC")
                ->all();
        if (!$plans)
            return [];
        return $plans;
    }

    /**
     * 锁定排期
     *
     * @param [array] $planLists
     * @return  bool
     */
    public function lockPlan($planLists) {
        $ids = ArrayHelper::getColumn($planLists, 'id');
        if (empty($ids)) {
            return false;
        }
        $ups = static::updateAll(['status' => static::LOCK_STATUS], ['id' => $ids, 'status' => [static::INIT_STATUS, static::WAIT_STATUS]]);
        return $ups;
    }

    /**
     * 命中规则
     * @param $oplan
     * @return int 未命中->1, 命中规则1、3->2, 命中规则2 ->3
     */
    public function hitRule() {
        //1实际预留金额+阈值  > 设置预留金额
        $real_money = bcadd($this->max_real, $this->threshold, 2);
        if (bccomp($real_money, $this->max_estimate, 2) == 1) {
            return 2;
        }
        //2实际处理金额+阈值  > 设置处理金额
        $do_real = bcadd($this->max_do_real, $this->threshold, 2);
        if (bccomp($do_real, $this->max_do_estimate, 2) == 1) {
            return 3;
        }
        //3实际成功金额+阈值  > 设置处理金额
        $success_money = bcadd($this->max_success_money, $this->threshold, 2);
        if (bccomp($success_money, $this->max_do_estimate, 2) == 1) {
            return 2;
        }
        return 1;
    }

    /**
     * 刷新排期表金额
     *
     * @param [type] $planLists
     * @return void
     */
    public function refreshAll($planLists) {
        if (count($planLists) < 1)
            return [];
        $oUrl = new User_remit_list();
        $oUle = new User_loan_extend();

        foreach ($planLists as $oPlan) {
            //获取willremit金额
            $sumWillRemitMoney = $oUle->getWillRemitMoney($oPlan->fund);
            //获取当日成功金额
            $sumSuccMoney = $oUrl->todayStatusMoney($oPlan->fund, 'SUCCESS');
            //获取当日失败金额
            $sumFailMoney = $oUrl->todayStatusMoney($oPlan->fund, 'FAIL');
            //获取当日所有金额
            $sumAllMoney = $oUrl->todayStatusMoney($oPlan->fund);

            //实际处理金额(含失败)
            $maxReal = $sumAllMoney + $sumWillRemitMoney;
            //实际处理金额(非失败)
            $maxDoReal = $maxReal - $sumFailMoney;
            $oPlan->max_real = $maxReal;
            $oPlan->max_do_real = $maxDoReal;
            $oPlan->max_success_money = $sumSuccMoney;
            $saveRes = $oPlan->save();
        }
        return $planLists;
    }

    /**
     * 根据主键查询
     *
     * @param [type] $ids
     * @return void
     */
    public function getByIds($ids) {
        if (!is_array($ids)) {
            return null;
        }
        return static::findAll(['fund' => $ids]);
    }

    /**
     * 累加指定金额
     * @param $money
     * @return bool
     */
    public function addAndRefresh($money) {
        if (empty($money)) {
            return false;
        }
        $this->max_real += $money;
        $this->max_do_real += $money;
        // $this->max_do_estimate = bcadd($this->max_do_estimate, $money, 2);
        return $this->save();
    }

    /**
     * 保存为关闭status=4
     * @param $oplan
     * @return bool
     */
    public function closePlan() {
        $this->status = static::CLOSE_STATUS;
        return $oplan->save();
    }

    /**
     * Undocumented function
     *
     * @param [type] $data_set
     * @return void
     */
    public function updateData($data_set) {
        if (empty($data_set)) {
            return false;
        }
        foreach ($data_set as $key => $val) {
            $this->$key = $val;
        }
        return $this->save();
    }

    public function saveData($data_set) {
        if (empty($data_set)) {
            return false;
        }
        $data_set = [
            'name' => ArrayHelper::getValue($data_set, 'name', ''), //名称
            'fund' => ArrayHelper::getValue($data_set, 'fund', ''), //资方
            'status' => ArrayHelper::getValue($data_set, 'status', 0), //初始; 1:锁定中; 2:成功完成; 3:待处理(同0); 4:关闭; 5:精确匹配中
            'sort_num' => ArrayHelper::getValue($data_set, 'sort_num', 50), //排序,默认50, 1-100正序
            'is_accuracy' => ArrayHelper::getValue($data_set, 'is_accuracy', 0), //精确匹配: 0:非; 1:是
            'start_time' => ArrayHelper::getValue($data_set, 'start_time', ''), //开始时间
            'end_time' => ArrayHelper::getValue($data_set, 'end_time', ''), //结束时间
            'max_estimate' => ArrayHelper::getValue($data_set, 'max_estimate', 0), //预留最大值
            'max_real' => ArrayHelper::getValue($data_set, 'max_real', 0), //实际留存金额(含失败)
            'max_do_estimate' => ArrayHelper::getValue($data_set, 'max_do_estimate', 0), //预留处理金额
            'max_do_real' => ArrayHelper::getValue($data_set, 'max_do_real', 0), //实际处理金额(非失败)
            'max_success_money' => ArrayHelper::getValue($data_set, 'max_success_money', 0), //实际处理成功金额
            'threshold' => ArrayHelper::getValue($data_set, 'threshold', 0), //阀值
            'admin_id' => ArrayHelper::getValue($data_set, 'admin_id', 1), //管理员ID
            'plan_time' => ArrayHelper::getValue($data_set, 'plan_time', ''), //排期时间
            'create_time' => date("Y-m-d H:i:s"),
        ];
        $error = $this->chkAttributes($data_set);
        if ($error) {
            return $this->returnError(false, json_encode($error));
        }
        return $this->save();
    }

    /**
     * 保存为结束status=2 若精确匹配，status=5
     * 
     * @return bool
     */
    public function saveFinished() {
        $status = static::SUCC_STATUS;
        if ($this->is_accuracy == 1) {
            $status = static::ACCURATE_STATUS;
        }
        $this->status = $status;
        return $this->save();
    }

    /**
     * 查询
     *
     * @param string $start_time
     * @param string $end_time
     * @return void
     */
    public function getSectionData($start_time = '', $end_time = '') {
        $data = self::find();
        if (!empty($start_time)) {
            $start_time = date("Y-m-d 00:00:00", strtotime($start_time));
            $data->andWhere(['>=', 'plan_time', $start_time]);
        }
        if (!empty($end_time)) {
            $end_time = date("Y-m-d 23:59:59", strtotime($end_time));
            $data->andWhere(['<=', 'plan_time', $end_time]);
        }
        $return = $data->orderBy("sort_num asc, id asc")->all();
        return $return;
    }

    /**
     * 查询,取最后一条
     *
     * @param string $start_time
     * @param string $end_time
     * @return void
     */
    public function getSectionLastone($fund) {
        $data = self::find();
        $return = $data->where(['fund' => $fund])->orderBy("id desc")->asArray()->one();
        return $return;
    }

    /**
     * 排期修改为处理中
     *
     * @param [type] $planLists
     * @return int
     */
    public function saveWait() {
        $ups = static::updateAll(['status' => static::WAIT_STATUS], ['status' => static::LOCK_STATUS]);
        return $ups;
    }

    /**
     * 通过资金方和排期时间查找
     * @param $fund
     * @param $plan_time
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getFundTime($fund, $plan_time) {
        if (empty($fund) || empty($plan_time)) {
            return false;
        }
        return self::find()->where(['fund' => $fund, 'plan_time' => $plan_time])->one();
    }

}
