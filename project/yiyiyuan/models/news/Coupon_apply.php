<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_coupon_apply". 
 * 
 * @property string $id
 * @property string $title
 * @property integer $type
 * @property integer $purpose
 * @property string $val
 * @property integer $limit
 * @property string $number
 * @property string $send_num
 * @property string $start_date
 * @property string $end_date
 * @property integer $apply_depart
 * @property string $apply_user
 * @property string $apply_money
 * @property integer $audit_person
 * @property integer $status
 * @property string $create_time
 * @property string $audit_time
 * @property integer $version
 */
class Coupon_apply extends BaseModel {

    /**
     * @inheritdoc 
     */
    public static function tableName() {
        return 'yi_coupon_apply';
    }

    /**
     * @inheritdoc 
     */
    public function rules() {
        return [
            [['title', 'val', 'limit', 'number', 'apply_money', 'audit_person', 'status'], 'required'],
            [['type','send_type', 'purpose', 'limit', 'number', 'send_num', 'apply_depart', 'audit_person', 'status', 'version'], 'integer'],
            [['val', 'apply_money'], 'number'],
            [['start_date', 'end_date', 'create_time', 'audit_time'], 'safe'],
            [['title'], 'string', 'max' => 1024],
            [['apply_user'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc 
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'type' => 'Type',
            'send_type' => 'Send Type',
            'purpose' => 'Purpose',
            'val' => 'Val',
            'limit' => 'Limit',
            'number' => 'Number',
            'send_num' => 'Send Num',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'apply_depart' => 'Apply Depart',
            'apply_user' => 'Apply User',
            'apply_money' => 'Apply Money',
            'audit_person' => 'Audit Person',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'audit_time' => 'Audit Time',
            'version' => 'Version',
        ];
    }

    /**
	 * 乐观所版本号
     * **/
    public function optimisticLock()
    {
        return "version";
    }

	/**
	 * 检查优惠卷列表中是否有此类型优惠卷，如果有返回优惠卷的id，如果没有则生成并返回id
     * @param string $title   优惠卷标题
     * @param int $type       1注册自动发券 2 输入手机号自动发券 3 分享成功自动发券
     * @param int $day        优惠卷有效天数
     * @param int $val        优惠卷金额
     * @return int(bool)
     */
    public function chkCouponList($title, $type, $day, $val) {
        $endtime = date('Y-m-d 00:00:00', strtotime("+$day days"));
        $where = [
            'title' => $title,
            'type' => $type,
            'start_date' => date('Y-m-d 00:00:00'),
            'val' => $val,
            'end_date' => $endtime,
            'apply_depart' => -1,
            'apply_user' => -1,
            'audit_person' => -1,
            'status' => 3
        ];
        $coupon = Coupon_apply::find()->where($where)->one();
        if($coupon){
            return $coupon->id;
        }
        $creat_resurt = $this->createCoupon($title, $type, 0, $val, $number = 10000, $endtime);
        if($creat_resurt){
            return Yii::$app->db->getLastInsertID();
        }
        return false;
    }

    /**
     * 创建优惠卷
     * @param string $title 优惠卷标题
     * @param int $type 1注册自动发券 2 输入手机号自动发券 3 分享成功自动发券
     * @param int $limit 使用限制 0 不限制 1 数字则为限制的金额
     * @param int $val   优惠卷金额
     * @param int $number 生成数量
     * @param int $endtime 有效期截至时间
     * @param int $purpose 用户 1 市场活动 2 客诉 3 用户注册
     * @return bool
     */
    public function createCoupon($title, $type, $limit, $val, $number, $endtime, $purpose = 0) {
        $condition = [
            'title' => $title,
            'type' => $type,
            'limit' => $limit,
            'val' => $val,
            'number' => $number,
            'end_date' => $endtime,
            'purpose' => $purpose
        ];
        return $this->addCoupon($condition);
    }

    /**
     * 新增优惠卷
     */
    public function addCoupon($condition){
        if( !is_array($condition) || empty($condition) ){
            return false;
        }
        $data = $condition;
        $time = date('Y-m-d 00:00:00');
        $data['create_time'] = $time;
        $data['audit_time'] = $time;
        $data['start_date'] = $time;
        $data['send_num'] = 0;
        $data['apply_depart'] = -1;
        $data['apply_user'] = '-1';
        $data['audit_person'] = -1;
        $data['status'] = 3;
        $data['apply_money'] = 0;
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }

    /*添加优惠券
     * */
    public function save_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $data['audit_time'] = date('Y-m-d H:i:s');
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }
    /**
     * 更新优惠卷
     */
    public function updateCoupon($condition)
    {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        try {
            $result = $this->save();
            return $result;
        } catch (\Exception $ex) {
            return FALSE;
        }
    }

    /**
     * 获取可用优惠卷（面向全部用户）
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function listByType()
    {
        $time = date('Y-m-d H:i:s');
        $where = [
            'AND',
            ['<', 'start_date', $time],
            ['>', 'end_date', $time],
            ['send_type' => 2],//全部用户
            ['status' => 3],//审核通过
        ];
        return self::find()->where($where)->all();
    }

    /**
     * 修改优惠卷发送数
     * @return bool
     */
    public function updateSendNum()
    {
        $sendNum = $this->send_num;
        try {
            $this->send_num = $sendNum + 1;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
}
