<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;
use yii\helpers\ArrayHelper;
use app\models\news\User;
/**
 * This is the model class for table "yi_coupon_list".
 *
 * @property string $id
 * @property string $apply_id
 * @property string $title
 * @property integer $type
 * @property string $sn
 * @property integer $val
 * @property integer $limit
 * @property string $start_date
 * @property string $end_date
 * @property string $mobile
 * @property integer $status
 * @property string $use_time
 * @property string $create_time
 */
class Coupon_list extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_coupon_list';
    }

    public function getCouponuse()
    {
        return $this->hasOne(Coupon_use::className(), ['discount_id' => 'id']);
    }
    public function getUser() {
        return $this->hasOne(User::className(), ['mobile' => 'mobile']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['apply_id', 'type', 'val', 'limit', 'status'], 'integer'],
            [['title', 'val'], 'required'],
            [['start_date', 'end_date', 'use_time', 'create_time'], 'safe'],
            [['title'], 'string', 'max' => 1024],
            [['sn'], 'string', 'max' => 32],
            [['mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'apply_id' => 'Apply ID',
            'title' => 'Title',
            'type' => 'Type',
            'sn' => 'Sn',
            'val' => 'Val',
            'limit' => 'Limit',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'mobile' => 'Mobile',
            'status' => 'Status',
            'use_time' => 'Use Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 对用户发放优惠卷
     * @param int $user_id
     * @param string $title 优惠卷标题
     * @param int $type 1注册自动发券 2 输入手机号自动发券 3 分享成功自动发券
     * @param int $day 优惠卷有效天数
     * @param int $val 优惠卷金额
     * @return bool
     */
    public function sendCoupon($user_id, $title, $type, $day, $val)
    {
        $applyModel = new Coupon_apply();
        $apply_id = $applyModel->chkCouponList($title, $type, $day, $val);
        if (!$apply_id) {
            return false;
        }
        return $this->sendToUser($user_id, $apply_id, $title, $val, $type);
    }

    private function sendToUser($user_id, $apply_id, $title, $val, $type)
    {
        $userinfo = User::findOne($user_id);
        //通过手机号查询是否有相同优惠券
        if ($type != 4) {
            $coupon = $this->getSameValids($userinfo->mobile, $title, $val);
            if ($coupon) {
                return false;//已经有此优惠价
            }
        }
        $resurt = $this->createCoupon($apply_id, $userinfo->mobile);
        if (!$resurt) {
            return false;
        }

        $caModel = Coupon_apply::findOne($apply_id);
        $condition = [
            'send_num' => $caModel->send_num + 1,
            'status' => $caModel->number > $caModel->send_num ? 3 : 5
        ];

        return $caModel->updateCoupon($condition);
    }

    /**
     * 通过mobile,title,val三个字段判断此用户是否已存在相同的优惠券
     */
    public function getSameValids($mobile, $title, $val)
    {
        $where = array(
            'AND',
            ['status' => 1],
            ['mobile' => $mobile],
            ['title' => $title],
            ['val' => $val],
            ['>', 'end_date', date('Y-m-d H:i:s')],
        );
        $res = Coupon_list::find()->where($where)->one();
        return $res;
    }

    /**
     * 创建优惠券
     */
    public function createCoupon($apply_id, $mobile)
    {
        $standard = Coupon_apply::find()->where(['id' => $apply_id])->asArray()->one();
        $condition = $standard;
        $condition['apply_id'] = $standard['id'];
        $condition['val'] = intval($standard['val']);
        $condition['mobile'] = $mobile;
        $result = $this->addCoupon($condition);

        return $result;
    }

    /**
     *新增优惠卷
     */
    public function addCoupon($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $sn = date('ymdHis', time()) . '1';
        $data = $condition;
        $data['sn'] = $sn;
        $data['status'] = 1;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取某一用户可用的优惠券
     * @param $mobile
     * @param int $term 期数，如果分期，优惠卷列表返回空
     * @return array|null
     */
    public function getValids($mobile, $term = 1)
    {
        if ($term > 1) {
            return [];
        }
        $couponlist = $this->getCouponByMobile($mobile, 1, 'end_date ASC');
        if (!$couponlist) {
            return null;
        }
        $data = \yii\helpers\ArrayHelper::toArray($couponlist);
        usort($data, [$this, 'sortCouponList']);
        return $data;
    }


    /**
     * 获取某一用户可用的优惠券
     * @param $mobile
     * @param int $term 期数，如果分期，优惠卷列表返回空
     * @return array|null
     */
    public function getValidList($mobile, $term = 1, $coupon_type, $is_installment = FALSE)
    {
        if ($is_installment) {
            return [];
        }
        $couponlist = $this->getCouponListByMobile($mobile, 1, $coupon_type, 'end_date ASC');
        if (!$couponlist) {
            return null;
        }
        $data = \yii\helpers\ArrayHelper::toArray($couponlist);
        usort($data, [$this, 'sortCouponList']);
        return $data;
    }

    /**
     * 根据手机号查询优惠券
     * @param type $mobile
     * @param int $status 1：未使用；2：已使用；3：过期；4：所有
     * @param int $type 1：借款优惠券；2：还款优惠券；
     * @return type
     */
    public function getCouponListByMobile($mobile, $status = 1, $coupon_type, $order = '')
    {
        if (empty($mobile)) {
            return null;
        }
        $now_time = date('Y-m-d H:i:s');
        if ($status == 4) {
            $coupon = Coupon_list::find()->where(['mobile' => $mobile, 'type' => $coupon_type]);
        } else {
            $coupon = Coupon_list::find();
            if ($status == 1) {
                $coupon = $coupon->where(['status' => $status, 'mobile' => $mobile, 'type' => $coupon_type])->andWhere("end_date >= '$now_time'");
            } else {
                if ($status == 3) {
                    $coupon = $coupon->where(['status' => $status, 'type' => $coupon_type])->orWhere("end_date <= '$now_time'");
                } else {
                    $coupon = $coupon->orWhere(['status' => $status, 'type' => $coupon_type]);
                }
                $coupon = $coupon->andWhere(['mobile' => $mobile]);
            }
        }
        if (!empty($order)) {
            $coupon = $coupon->orderBy($order);
        }
        $couponlist = $coupon->all();
        return $couponlist;
    }


    /**
     * 根据手机号查询优惠券
     * @param type $mobile
     * @param int $status 1：未使用；2：已使用；3：过期；4：所有
     * @return type
     */
    public function getCouponByMobile($mobile, $status = 1, $order = '')
    {
        if (empty($mobile)) {
            return null;
        }
        $now_time = date('Y-m-d H:i:s');
        if ($status == 4) {
            $coupon = Coupon_list::find()->where(['mobile' => $mobile]);
        } else {
            $coupon = Coupon_list::find();
            if ($status == 1) {
                $coupon = $coupon->where(['status' => $status, 'mobile' => $mobile])->andWhere("end_date > '$now_time'");
            } else {
                if ($status == 3) {
                    $coupon = $coupon->where(['status' => $status])->orWhere("end_date < '$now_time'");
                } else {
                    $coupon = $coupon->orWhere(['status' => $status]);
                }
                $coupon = $coupon->andWhere(['mobile' => $mobile]);
            }
        }
        if (!empty($order)) {
            $coupon = $coupon->orderBy($order);
        }
        $couponlist = $coupon->all();
        return $couponlist;
    }

    public function getCouponById($couponId)
    {
        $couponId = intval($couponId);
        if (!$couponId) {
            return [];
        }
        return self::find()->where(['id' => $couponId])->one();
    }

    /**
     *
     *
     * 优惠券：判断当前优惠券账户是否有可用的借款优惠券，
     * 优先匹配全免券，如果有两张以上全免券则优先匹配当前距有效期最接近的全免券，
     * 其次匹配金额券，按照最大面值的优惠依次匹配，如果面值相同则优先匹配当前距有效期最接近的优惠券；
     * 如果没有则优惠券项整个置灰显现
     *
     */
    public function sortCouponList($o1, $o2)
    {
        if ($o1['val'] != $o2['val']) {
            if ($o1['val'] == 0) {
                return -1;
            } elseif ($o2['val'] == 0) {
                return 1;
            } else {
                // 从大到小排序
                return $o1['val'] > $o2['val'] ? -1 : 1;
            }
        } else {
            // 从小到大排序
            return $o1['end_date'] < $o2['end_date'] ? -1 : 1;
        }
    }

    public function updateCouponList($condition = array())
    {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $result = $this->save();
        if ($result) {
            return $this;
        } else {
            return false;
        }
    }

    /**
     * 修改优惠券列表
     * @param type $condition
     * @return boolean
     * @author Zhangchao <zhangchao@xianhuahua.com>
     */
    public function update_couponlist($condition = array())
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 通过借款ID取出对应的优惠券
     * @param $loan_id
     * @return array();
     */
    public function getLoanCoupon($loan_id)
    {
        if (empty($loan_id)) return false;
        $coupon_list = self::tableName();
        $coupon_use = Coupon_use::tableName();
        $result = self::find()->innerJoin($coupon_use, "{$coupon_use}.discount_id = {$coupon_list}.id")->where(["loan_id" => $loan_id])->all();
        if (!empty($result)) {
            return $result[0];
        }
        return array();
        /*
        $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $value['loan_id'];
        $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
        */
    }

    public function _saveCouplist($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['use_time'] = $condition['use_time'];
        $data['status'] = 2;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 修改使用失败
     * @return bool
     */
    public function updateUseFail()
    {
        $data['status'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 拉取优惠卷
     * @param $mobile
     * @return bool
     */
    public function pullCoupon($mobile)
    {
        if (empty($mobile)) {
            return false;
        }
        $couponApplyList = (new Coupon_apply())->listByType();
        if (empty($couponApplyList)) {
            return true;
        }
        $cidArr = ArrayHelper::getColumn($couponApplyList, 'id');
        $func = function ($str) {
            return intval($str);
        };
        $cidArr = array_map($func, $cidArr);
        $list = self::find()->where(['mobile' => $mobile, 'apply_id' => $cidArr])->all();
        $alreadyList = ArrayHelper::getColumn($list, 'apply_id');
        $cidDiff = array_diff($cidArr, $alreadyList);
        if (empty($cidDiff)) {
            return true;
        }
        $oUser = User::find()->where(['mobile' => $mobile])->one();
        $user_create_time = $oUser->create_time;
        foreach ($cidDiff as $cid) {
            $couponApplyObj = Coupon_apply::findOne($cid);
            if( $user_create_time < $couponApplyObj->create_time){
                $condition = [
                    'apply_id' => $couponApplyObj->id,
                    'title' => $couponApplyObj->title,
                    'type' => $couponApplyObj->type,
                    'val' => (int)($couponApplyObj->val),
                    'limit' => $couponApplyObj->limit,
                    'start_date' => $couponApplyObj->start_date,
                    'end_date' => $couponApplyObj->end_date,
                    'mobile' => $mobile
                ];
                $result = (new Coupon_list())->addCoupon($condition);
                if (!$result) {
                    continue;
                }
                $couponApplyObj->updateSendNum();
            }
        }
        return true;
    }

    /**
     * 获取优惠卷记录，根据id、mobile
     * @param $id
     * @param $mobile
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getByIdAndMobile($id, $mobile)
    {
        if (empty($id) || empty($mobile)) {
            return null;
        }
        return self::find()->where(['id' => $id, 'mobile' => $mobile])->one();
    }

    //监测是否可以显示优惠卷
    public function chkCouponShow($loanId,$user_loan,$goodbill_arr=[])
    {
        if (empty($loanId)) {
            return false;
        }
        if(in_array($user_loan['business_type'], [5,6,11]) && !empty($goodbill_arr)){//如果是分期还款就不进行优惠券操作
            return false;
        }
        $repayCouponUse = (new RepayCouponUse())->getByLoanId($loanId);
        if (!empty($repayCouponUse)) {
            return false;
        }
        $userLoanList = (new User_loan())->listRenewal($loanId);
        if (!empty($userLoanList)) {
            $loanIdArr = ArrayHelper::getColumn($userLoanList, 'loan_id');
            $repayCouponUseList = (new RepayCouponUse())->getByLoanId($loanIdArr);
            if (!empty($repayCouponUseList)) {
                return false;
            }
        }
        return true;
    }

    //检测还款优惠卷并返回可用优惠券
    public function geHhgCouponDate($coupon_id,$mobile){
        if (empty($coupon_id)) {
            return false;
        }
        $couponListObj = (new Coupon_list())->getByIdAndMobile($coupon_id,$mobile);
        if (empty($couponListObj)) {
            return false;
        }
        $repayCouponUseObj = (new RepayCouponUse())->getByDiscountId($coupon_id);
        if (!empty($repayCouponUseObj)) {
            return false;
        }
        $coupondata=[
            'id' => $couponListObj->id,
            'coupon_amount'=>$couponListObj->val,
        ];
        return $coupondata;
    }

    public function getArrayMax($arr, $field) {
        foreach ($arr as $k => $v) {
            $temp[] = $v[$field];
        }
        $MaxCouponVal = max($temp);
        foreach ($arr as $key => $val) {
            if ($val[$field] == $MaxCouponVal) {
                ;
                return $key;
            }
        }
        return 0;
    }

    //核实还款优惠卷
    public function chkCoupon($mobile, $couponId, $loanId)
    {
        if (empty($mobile) || empty($couponId) || empty($loanId)) {
            return ['rsp_code' => '99994'];
        }
        $couponListObj = (new Coupon_list())->getByIdAndMobile($couponId, $mobile);
        if (empty($couponListObj)) {
            return ['rsp_code' => '10215'];
        }
        if ($couponListObj->type != 5) {
            return ['rsp_code' => '10215'];
        }
        if ($couponListObj->status != 1) {
            return ['rsp_code' => '10215'];
        }
        $date = date('Y-m-d H:i:s');
        if ($couponListObj->start_date > $date || $couponListObj->end_date < $date) {
            return ['rsp_code' => '10216'];
        }
        $repayCouponUseObj = (new RepayCouponUse())->getByDiscountId($couponId);
        if (!empty($repayCouponUseObj)) {
            return ['rsp_code' => '10217'];
        }
        $repayCouponUse = (new RepayCouponUse())->getByLoanId($loanId);
        if (!empty($repayCouponUse)) {
            return ['rsp_code' => '10218'];
        }
        //续期
        $userLoanList = (new User_loan())->listRenewal($loanId);
        if (!empty($userLoanList)) {
            $loanIdArr = ArrayHelper::getColumn($userLoanList, 'loan_id');
            $repayCouponUseList = (new RepayCouponUse())->getByLoanId($loanIdArr);
            if (!empty($repayCouponUseList)) {
                return ['rsp_code' => '10218'];
            }
        }
        return ['rsp_code' => '0000', 'data' => $couponListObj];
    }
}