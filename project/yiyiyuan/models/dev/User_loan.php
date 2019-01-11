<?php

namespace app\models\dev;

use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\dev\Fraudmetrix_return_info;
use app\models\dev\User_history_info;
use app\models\dev\User_loan_single;
use app\models\Flow;
use app\models\news\Cg_remit;
use app\models\yyy\XhhApi;
use Yii;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use app\models\news\Renew_amount;

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
class User_loan extends ActiveRecord {

    public $shareurl;
    public $huankuantime;
    public $huankuan_amount;
    public $admin_name;
    public $audit_name;
    public $single_id;
    public $single_status;
    public $single_create_time;
    public $realname;
    public $mobile;
    public $repay_amount;
    public $with_fee = 0.1;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * 添加不存在的字段
     */
    public function getCollection($coulum) {
        $loan_id = $this->loan_id;
        $loan_collection = Loan_collection::find()->where(['loan_id' => $loan_id])->orderBy('create_time desc')->one();
        if (empty($loan_collection) && !isset($loan_collection)) {
            return 0;
        } else {
            if ($coulum != 'admin_id') {
                return $loan_collection->{$coulum};
            } else {
                $ma = Manager::find()->where(['id' => $loan_collection->admin_id])->one();
                if (empty($ma) && !isset($ma)) {
                    $real = '没有催收人';
                } else {
                    $real = $ma->realname;
                }
                return $real;
            }
        }
    }

    /**
     * 还款金额
     * @param int $type 1:原有获取方式  2：续期状态已还款金额
     * @return type
     */
    public function getRepayAmount($type = 1) {
        $parent_id = $this->parent_loan_id;
        if (empty($parent_id)) {
            $type = 1;
        }
        if ($type == 1) {
            $loan_id = $this->loan_id;
            $amount = Loan_repay::find()->where(['loan_id' => $loan_id, 'status' => 1])->sum('actual_money');
        } else {//续期       
            $loan_id = User_loan::find()->select(['loan_id'])->where(['parent_loan_id' => $parent_id])->asArray()->all();

            $loan_ids = ArrayHelper::getColumn($loan_id, 'loan_id');
            $amount = Loan_repay::find()->where(['loan_id' => $loan_ids, 'status' => 1])->sum('actual_money');
        }
        return $amount;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    public function getAccount() {
        return $this->hasOne(Account::className(), ['user_id' => 'user_id']);
    }
    public function getRepay() {
        return $this->hasOne(Loan_repay::className(), ['loan_id' => 'loan_id'])->orderBy('id desc');
    }

    public function getFlow() {
        return $this->hasOne(Flow::className(), ['loan_id' => 'loan_id', 'loan_status' => 'status']);
    }

    public function getSingle() {
        return $this->hasOne(User_loan_single::className(), ['loan_id' => 'loan_id']);
    }

    public function getloan_collection() {
        return $this->hasOne(Loan_collection::className(), ['loan_id' => 'loan_id']);
    }

    public function getBank() {
        return $this->hasOne(User_bank::className(), ['id' => 'bank_id']);
    }

    public function getRemitlist() {
        return $this->hasOne(User_remit_list::className(), ['loan_id' => 'loan_id']);
    }

    public function getLoanextend() {
        return $this->hasOne(User_loan_extend::className(), ['loan_id' => 'loan_id']);
    }

    public function getCgRemit() {
        return $this->hasOne(Cg_remit::className(), ['loan_id' => 'loan_id']);
    }

    public function loanFee($amount, $days) {
        $rateModel = new Rate();
        $day_rate = 0.0005;
        $interest_fee = round($amount * $day_rate * $days, 2);
        $withdraw_fee = (round($amount * 0.1, 2) > 5) ? round($amount * 0.1, 2) : 5;
        return array(
            'interest_fee' => $interest_fee,
            'withdraw_fee' => $withdraw_fee,
        );
    }

    public function createLoanNo() {
        $suffix = $this->loan_id;
        $size = 6;
        for ($i = 1; $i < $size; $i++) {
            if (strlen($suffix) < $size)
                $suffix = '0' . $suffix;
        }
        $loan_no = date("Ymd") . $suffix;
        $loan = $this->updateUserLoan(array('loan_no' => $loan_no));
        return $loan;
    }

    /**
     * 借款驳回，失效返回投资者额度
     */
    public function reject() {
        $create_time = date('Y-m-d H:i:s');
        //查询该笔借款是否有使用优惠券，如果有使用优惠券，则判断优惠券是否过期，没有过期则返还给用户
        $loan_coupon = Coupon_list::find()->joinWith('couponuse', true, 'LEFT JOIN')->where([Coupon_use::tableName() . '.loan_id' => $this->loan_id])->one();
        if (!empty($loan_coupon)) {
            if ($create_time < $loan_coupon['end_date']) {
                $loan_coupon->status = 1;
            } else {
                $loan_coupon->status = 3;
            }
            if (!$loan_coupon->save()) {
                return false;
            }
        }
        $invest_list = User_invest::find()->select(array('user_id', 'amount', 'version', 'status'))->where(['loan_id' => $this->loan_id])->all();
        if (!empty($invest_list)) {
            foreach ($invest_list as $k => $v) {
                $account = Account::find()->where(['user_id' => $v['user_id']])->one();
                $account->current_invest = $account->current_invest - $v['amount'];
                $account->current_amount = $account->current_amount + $v['amount'];
                $account->total_invest = $account->total_invest - $v['amount'];
                $account->version++;
                if (!$account->save()) {
                    return false;
                }
                $v->status = 2;
                if (!$v->save()) {
                    return false;
                }
            }
        }
        
//        $ownerAccount = Account::find()->where(['user_id' => $this->user_id])->one();
//        $ownerAccount->current_loan = $ownerAccount->current_loan - $this->amount;
//        $ownerAccount->total_loan = $ownerAccount->total_loan - $this->amount;
//        if ($this->credit_amount > 0) {
//            $ownerAccount->current_amount = $ownerAccount->current_amount + $this->credit_amount;
//        }
//        $ownerAccount->version++;
//        if (!$ownerAccount->save()) {
//            return false;
//        }
        return true;
    }

    /**
     * 获取已还款金额
     * @param type $loan_id   借款id
     */
    public function getAlreadyRepayAmount($loan_id) {
        $actual_money = Loan_repay::find()->where(['loan_id' => $loan_id, 'status' => 1])->sum('actual_money');
        $already_money = intval($actual_money * 100) / 100;
        return $already_money;
    }

    /**
     * 获取应还款的金额
     */
    public function getRepaymentAmount($loan_id, $status, $chase_amount, $collection_amount, $like_amount, $amount, $current_amount, $interest_fee, $coupon_amount, $withdraw_fee) {
        $loan_coupon_sql = "select l.id,l.limit,l.end_date,l.val,l.status from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
        $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
        $loan = User_loan::findOne($loan_id);
        if ($loan->prome_status == 1 && ($loan->status == 3 || $loan->status == 7)) {
            if (!empty($coupon_amount)) {
                $coupon_amount = ($interest_fee > $coupon_amount) ? $coupon_amount : $interest_fee;
            }
            if ($loan->is_calculation == 1) {
                $total_amount = $amount + $interest_fee + $collection_amount - $like_amount - $coupon_amount;
            } else {
                $total_amount = $amount + $interest_fee + $withdraw_fee + $collection_amount - $like_amount - $coupon_amount;
            }
        } else if ($loan_id <= 38841) {
            if (!empty($chase_amount)) {
                $total_amount = $chase_amount + $collection_amount - $like_amount;
            } else {
                if ($current_amount < $amount) {
                    $total_amount = $current_amount + $interest_fee + $collection_amount - $like_amount;
                } else {
                    $total_amount = $amount + $interest_fee + $collection_amount - $like_amount;
                }
            }
        } else {
            if (!empty($chase_amount)) {
                $total_amount = $chase_amount + $collection_amount;
            } else {
                if (!empty($loan_coupon) && ($loan_coupon['val'] == 0) && ($loan_coupon['status'] == 2)) {
                    if ($loan->is_calculation == 1) {
                        $total_amount = $amount + $collection_amount;
                    } else {
                        $total_amount = $amount + $collection_amount + $withdraw_fee;
                    }
                } else {
                    if (!empty($coupon_amount)) {
                        if ($loan->is_calculation == 1) {
                            $coupon_amount = ($interest_fee > $coupon_amount) ? $coupon_amount : $interest_fee;
                        } else {
                            $coupon_amount = ($interest_fee + $withdraw_fee > $coupon_amount) ? $coupon_amount : $interest_fee + $withdraw_fee;
                        }
                    }
                    if ($current_amount < $amount && $loan->status != 3 && $loan->status != 7 && $loan->status != 17) {
                        if ($loan->is_calculation == 1) {
                            $total_amount = $current_amount + $interest_fee + $collection_amount - $like_amount - $coupon_amount;
                        } else {
                            $total_amount = $current_amount + $interest_fee + $withdraw_fee + $collection_amount - $like_amount - $coupon_amount;
                        }
                    } else {
                        if ($loan->is_calculation == 1) {
                            $total_amount = $amount + $interest_fee + $collection_amount - $like_amount - $coupon_amount;
                        } else {
                            $total_amount = $amount + $interest_fee + $withdraw_fee + $collection_amount - $like_amount - $coupon_amount;
                        }
                    }
                }
            }
        }
        if ($total_amount * 10000 % 100 != 0) {
            $total_amount =  ceil($total_amount * 100) / 100;
        } else {
             $total_amount = $total_amount;
        }
        if ($loan->business_type == 2) {
            $total_amount = ceil($total_amount / 0.99 * 10000) / 10000;
        }
        if ($status != 8) {


            $already_money = $loan->getRepayAmount(2);
            if ($already_money != 0) {
                $total_amount = bcsub($total_amount, $already_money, 2);
            }
        }
        if ($total_amount * 10000 % 100 != 0) {
            return ceil($total_amount * 100) / 100;
        } else {
            return $total_amount;
        }
    }

    /**
     * 获取应还款的金额
     * @param type $loan
     * @param type $type   0:已逾期，1：未逾期  预留参数
     * @return floor 本应还款的金额
     */
    public function totalAmount($is_calculation, $type = 0) {
        if ($this->current_amount < $this->amount) {
            if ($this->loan_id <= 38841) {
                $totalamount = $this->current_amount + $this->interest_fee;
            } else {
                if ($is_calculation == 1) {
                    $totalamount = $this->current_amount + $this->interest_fee;
                } else {
                    $totalamount = $this->current_amount + $this->interest_fee + $this->withdraw_fee;
                }
            }
        } else {
            if ($this->loan_id <= 38841) {
                $totalamount = $this->amount + $this->interest_fee;
            } else {
                if ($is_calculation == 1) {
                    $totalamount = $this->amount + $this->interest_fee;
                } else {
                    $totalamount = $this->amount + $this->interest_fee + $this->withdraw_fee;
                }
            }
        }
        return $totalamount;
    }


    /**
     * 计算逾期费用
     * @param type $loan
     * @param type $type   0:已逾期，1：未逾期  预留参数
     * @return floor 本应还款的金额
     */
    public function chaseAmount($is_calculation) {
        $totalamount = $this->totalAmount($is_calculation);
        $days = floor((time() - strtotime($this->end_date)) / 24 / 3600) + 1;
        if ($days <= 90) {
            $chase_amount = $totalamount * pow((1 + 0.01), $days);
        } else {
            $num = $totalamount * pow((1 + 0.01), 90);
            $chase_amount = $num * pow((1 + 0.005), $days - 90);
            if ($chase_amount < $this->chase_amount) {
                $chase_amount = $this->chase_amount * (1 + 0.005);
            }
        }
        return $chase_amount;
    }

    /**
     * 更改借款状态
     * @param type $status
     */
    public function changeStatus($status, $type = -1) {
        $this->status = $status;
        $this->version++;
        if ($status == 5) {
            $this->withdraw_time = date('Y-m-d H:i:s');
        }
        if ($status == 9) {
            $new_start_date = date('Y-m-d');
            $new_end_date = date('Y-m-d', (time() + ($this->days + 1) * 24 * 3600));
            $this->start_date = $new_start_date;
            $this->end_date = $new_end_date;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        if (!$this->save()) {
            return false;
        }
        if ($this->business_type == 3) {
            $guarantee_loan = User_guarantee_loan::find()->where(['loan_id' => $this->loan_id])->one();
            $guarantee_loan->status = $status;
            $guarantee_loan->version++;
            if (!$guarantee_loan->save()) {
                return false;
            }
        }
        $flowdata = (object) array('loan_id' => $this->loan_id, 'status' => $status);
        $flow = new Flow();
        $flow->CreateFlow($flowdata, $type);
        if ($status == 3 || $status == 4 || $status == 7 || $status == 15 || $status == 17) {
            if (!$this->reject()) {
                return false;
            }
            if ($status == 17 && $this->prome_status == 1) {
                $this->prome_status = 5;
                $this->save();
            }
        }
        return $this;
    }

    public function frozen_guarantee() {
        $loan_id = $this->loan_id;
        $guarantee = User_guarantee_loan::find()->select('user_guarantee_id')->where(['loan_id' => $loan_id])->one();
        $userinfo_guarantee = User::find()->where(['user_id' => $guarantee->user_guarantee_id])->one();
        if ((!empty($userinfo_guarantee)) && ($userinfo_guarantee['status'] != 7)) {
            if (!$userinfo_guarantee->frozen()) {
                return false;
            }
        }
        return true;
    }

    /**
     * 
     * @param type $userid
     * @param type $status
     * @param type $business_type  -1 表示查询所有类型借款
     * @return type
     */
    public function getUserLoan($userid, $status = '', $business_type = '') {
        if (empty($userid)) {
            return null;
        }
        $nowtime = date('Y-m-d H:i:s');
        $loan = User_loan::find()->where(['user_id' => $userid]);
        if (!empty($status)) {
            $loan = $loan->andWhere(['status' => $status]);
        }
        if (empty($business_type)) {
            $loan = $loan->andWhere(['business_type' => 1]);
        } else {
            $loan = $loan->andWhere(['business_type' => $business_type]);
        }
        $result = $loan->orderBy('open_end_date desc')->all();
        if (!empty($result)) {
            return $result;
        } else {
            return null;
        }
    }

    public function addUserLoan($condition) {
        if (empty($condition)) {
            return false;
        }
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->number = 0;
        $this->settle_type = 0;
        $open_start_date = date('Y-m-d H:i:s');
        $open_end_date = $this->getOpenEndTime();
        $this->open_start_date = $open_start_date;
        $this->open_end_date = $open_end_date;
        $this->create_time = $open_start_date;
        $this->last_modify_time = $open_start_date;
        $result = $this->save();
        if ($result) {

            $loan_id = Yii::$app->db->getLastInsertID();
            $loan_info = User_loan::findOne($loan_id);
            $loan_info->parent_loan_id = $loan_id;
            $loan_info->save();
            return $loan_id;
        } else {
            return false;
        }
    }

    public function updateUserLoan($condition) {
        if (empty($condition)) {
            return false;
        }
        $create_time = date('Y-m-d H:i:s');
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        if (isset($condition['open_end_date'])) {
            $this->open_end_date = $this->getOpenEndTime($this->open_start_date);
        }

        $this->version += 1;
        $this->last_modify_time = $create_time;
        $result = $this->save();
        if ($result) {
            return $this;
        } else {
            return false;
        }
    }

    private function getOpenEndTime($open_start_date = '') {
        if (empty($open_start_date)) {
            $hour = date('H');
        } else {
            $hour = date('H', strtotime($open_start_date));
        }
        if ($hour >= 0 && $hour < 9) {
            $open_end_date = date('Y-m-d 15:00:00');
        } else if ($hour < 18) {
            $open_end_date = date('Y-m-d H:i:s', strtotime('+6 hour'));
        } else {
            $open_end_date = date('Y-m-d H:i:s', strtotime('+15 hour'));
        }
        return $open_end_date;
    }

    /**
     * 获取等待投资的纪录
     * @param [] $uids 用户id
     */
    public function getWillIncomeByUids($uids) {
        if (!is_array($uids) || empty($uids)) {
            return null;
        }
        $condition = [
            'status' => 2, // 刚刚通过时，是要好友投钱的
            'user_id' => $uids
        ];
        // 初步限定在500条， 以后确认好友关系了这个就好解决了
        return static::find()->where($condition)->limit(500)->orderBy("open_end_date DESC")->all();
    }

    /**
     * 获取逾期罚息
     */
    public function getOverdueAmount($status, $chase_amount, $current_amount, $interest_fee, $withdraw_fee, $is_calculation) {
        if ($status == 12 || $status == 13) {
            if ($is_calculation == 1) {
                $overdue_amount = $chase_amount - $current_amount - $interest_fee;
            } else {
                $overdue_amount = $chase_amount - $current_amount - $interest_fee - $withdraw_fee;
            }
        } else {
            if ($chase_amount > 0) {
                if ($is_calculation == 1) {
                    $overdue_amount = $chase_amount - $current_amount - $interest_fee;
                } else {
                    $overdue_amount = $chase_amount - $current_amount - $interest_fee - $withdraw_fee;
                }
            } else {
                $overdue_amount = 0;
            }
        }
        if ($overdue_amount * 10000 % 100 != 0) {
            return ceil($overdue_amount * 100) / 100;
        } else {
            return $overdue_amount;
        }
    }

    /**
     * 获取还款时间
     */
    public function getHuankuanTime($status, $end_date) {
        if ($status == 12 || $status == 13 || $status == 9 || $status == 11) {
            $huankuantime = date('n' . '月' . 'j' . '日', (strtotime($end_date) - 24 * 3600));
        } else {
            $huankuantime = '以短信推送时间为准';
        }

        return $huankuantime;
    }

    /**
     * 计算服务费
     * (产品要求，服务费不进行任何减免的展示)
     * @param unknown $code
     * @return unknown
     */
    public function getServiceAmount($loan_id, $status, $interest_fee, $withdraw_fee, $coupon_amount, $is_calculation) {
        if ($is_calculation == 1) {
            $service_amount = $interest_fee;
        } else {
            $service_amount = $interest_fee + $withdraw_fee;
        }
        return $service_amount;
    }

    /**
     * 获取逾期天数
     */
    public function getOverdueDays($status, $end_date) {
        if ($status == 12 || $status == 13) {
            $overdue_days = ceil((time() - strtotime($end_date)) / 24 / 3600);
        } else {
            $overdue_days = 0;
        }

        return $overdue_days;
    }

    /**
     * 查询借款信息和借款人信息,借款人银行卡信息
     */
    public function getLoanInfoAndUserInfo($loan_id) {
        if (empty($loan_id)) {
            return null;
        }

        $sql_loaninfo = "select u.realname,u.mobile,u.identity,u.school,u.edu,u.school_time,u.birth_year,b.card,l.user_id,l.status,l.loan_no,l.amount,l.current_amount,l.interest_fee,l.withdraw_fee,l.coupon_amount,l.start_date,l.end_date,l.desc,l.open_end_date,l.days,l.version as lversion,a.version as aversion from yi_user_loan as l,yi_account as a,yi_user as u,yi_user_bank as b where l.loan_id=$loan_id and l.user_id=a.user_id and l.user_id=u.user_id and l.user_id=b.user_id";
        $loaninfo = Yii::$app->db->createCommand($sql_loaninfo)->queryOne();

        return $loaninfo;
    }

    /**
     * 查询该笔借款是否有使用优惠券，如果有使用优惠券，则判断优惠券是否过期，没有过期则返还给用户
     */
    public function getLoanCoupon($loan_id) {
        if (empty($loan_id)) {
            return null;
        }

        $coupon_use = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
        if (!empty($coupon_use)) {
            $nowtime = date('Y-m-d H:i:s');
            $loan_coupon_sql = "select l.id,l.end_date from " . Coupon_list::tableName() . " as l," . Coupon_use::tableName() . " as u where l.id=u.discount_id and u.loan_id=" . $loan_id;
            $loan_coupon = Yii::$app->db->createCommand($loan_coupon_sql)->queryOne();
            if (!empty($loan_coupon)) {
                if ($nowtime < $loan_coupon['end_date']) {
                    $sql_coupon = "update " . Coupon_list::tableName() . " set status=1 where id=" . $loan_coupon['id'];
                    $sql_coupon_ret = Yii::$app->db->createCommand($sql_coupon)->execute();
                } else {
                    $sql_coupon = "update " . Coupon_list::tableName() . " set status=3 where id=" . $loan_coupon['id'];
                    $sql_coupon_ret = Yii::$app->db->createCommand($sql_coupon)->execute();
                }

                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 借款驳回
     * @param unknown $code
     * @return unknown
     */
    public function rejectLoan($final_score, $loan_id, $amount, $user_id, $version) {
        $sql_score = "update " . User_loan::tableName() . " set status=3,final_score='$final_score',version=version+1 where loan_id=" . $loan_id;
        $ret_score = Yii::$app->db->createCommand($sql_score)->execute();
        $flowdata = (object) array('loan_id' => $loan_id, 'status' => 3);
        $flow = new Flow();
        $flow->CreateFlow($flowdata, 0);

        //查询借款的信息
        $loaninfo = User_loan::find()->select(array('credit_amount'))->where(['loan_id' => $loan_id])->one();
        //借款人账户当前借款和总借款减
        if ($loaninfo->credit_amount > 0) {
            $loan_acc = "update " . Account::tableName() . " set current_amount=current_amount+" . $loaninfo->credit_amount . ",current_loan=current_loan-" . $amount . ",total_loan=total_loan-" . $amount . ",version=version+1 where user_id=" . $user_id;
        } else {
            $loan_acc = "update " . Account::tableName() . " set current_loan=current_loan-" . $amount . ",total_loan=total_loan-" . $amount . ",version=version+1 where user_id=" . $user_id;
        }
        $ret_loan_acc = Yii::$app->db->createCommand($loan_acc)->execute();

        //查询该笔借款是否有使用优惠券，如果有使用优惠券，则判断优惠券是否过期，没有过期则返还给用户
        $coupon_reback = $this->getLoanCoupon($loan_id);

        $user_invest = new User_invest();
        //借款投资人信息
        $investUser = User_invest::find()->where(['loan_id' => $loan_id])->all();
        if ($ret_loan_acc) {
            //投资人投资额度返回，同时投资人账户当前投资和总投资减
            foreach ($investUser as $key => $val) {
                $invest_reback = $user_invest->setInvestAccount($val->invest_id, $val->version, $val->amount, $val->user_id);
            }
        }

        return true;
    }

    /**
     * 修改借款的分数
     */
    public function setLoanScore($loan_id, $final_score) {
        $sql_score = "update " . User_loan::tableName() . " set final_score='$final_score',version=version+1 where loan_id=" . $loan_id;
        $ret_score = Yii::$app->db->createCommand($sql_score)->execute();

        return true;
    }

    /**
     * dev调用同盾接口，判断该笔借款的分数,生成借款之前调用
     * @param $source 1:$_COOKIE['PHPSESSID']获取tokenid，2：app获取tokenid
     */
    public function getFraudmetrixInfoDev($edu, $loan_user_id, $loan_no, $version, $realname, $mobile, $identity, $school, $school_time, $amount, $create_time, $birth_year, $source = 1) {
        switch ($edu) {
            case 1:
                $ext_diploma = '博士';
                break;
            case 2:
                $ext_diploma = '硕士';
                break;
            case 3:
                $ext_diploma = '本科';
                break;
            default:
                $ext_diploma = '专科';
        }
        if ($source == 1) {
            $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
            if (empty($token_id)) {
                $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $loan_user_id])->one();
                $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
            }
        } else {
            $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $loan_user_id])->one();
            $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
        }
        $params = array(
            'account_name' => $realname,
            'mobile' => $mobile,
            'id_number' => $identity,
            'seq_id' => $loan_no,
            'ip_address' => \Yii::$app->request->getUserIP(),
            'type' => 1,
            'token_id' => $token_id,
            'ext_school' => $school,
            'ext_diploma' => $ext_diploma,
            'ext_start_year' => $school_time,
            'card_number' => '',
            'pay_amount' => $amount,
            'event_occur_time' => $create_time,
            'ext_birth_year' => $birth_year
        );
        $api = new Apihttp();
        $result_loan = $api->riskLoanValid($params);
        //$result_loan = Http::riskDecision_loan($realname, $mobile, $identity, $school, $ext_diploma, $school_time, '', $amount, $create_time, $loan_no, $birth_year, $token_id);
        $fraudmetrix = new Fraudmetrix_return_info();
        $fraudmetrix->CreateFraudmetrix($result_loan, $loan_user_id, $loan_no);
        return $result_loan;
    }

    /**
     * 调用同盾接口，判断该笔借款的分数
     */
    public function getFraudmetrixInfo($edu, $loan_user_id, $loan_id, $user_id, $version, $realname, $mobile, $identity, $school, $school_time, $card, $amount, $create_time, $loan_no, $birth_year) {
        switch ($edu) {
            case 1:
                $ext_diploma = '博士';
                break;
            case 2:
                $ext_diploma = '硕士';
                break;
            case 3:
                $ext_diploma = '本科';
                break;
            default:
                $ext_diploma = '专科';
        }
//        $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : rand(100000000, 999999999);
        $token_id = isset($_COOKIE['PHPSESSID']) ? $_COOKIE['PHPSESSID'] : '';
        if (empty($token_id)) {
            $userpass = User_password::find()->select('device_tokens')->where(['user_id' => $loan_user_id])->one();
            $token_id = !empty($userpass->device_tokens) ? $userpass->device_tokens : rand(100000000, 999999999);
        }
        $params = array(
            'account_name' => $realname,
            'mobile' => $mobile,
            'id_number' => $identity,
            'seq_id' => $loan_no,
            'ip_address' => \Yii::$app->request->getUserIP(),
            'type' => 1,
            'token_id' => $token_id,
            'ext_school' => $school,
            'ext_diploma' => $ext_diploma,
            'ext_start_year' => $school_time,
            'card_number' => $card,
            'pay_amount' => $amount,
            'event_occur_time' => $create_time,
            'ext_birth_year' => $birth_year
        );
        $api = new Apihttp();
        $result_loan = $api->riskLoanValid($params);
        //$result_loan = Http::riskDecision_loan($realname, $mobile, $identity, $school, $ext_diploma, $school_time, $card, $amount, $create_time, $loan_no, $birth_year, $token_id);
        $fraudmetrix = new Fraudmetrix_return_info();
        $fraudmetrix->CreateFraudmetrix($result_loan, $loan_user_id, $loan_id);
        if (isset($result_loan->rsp_code) && $result_loan->rsp_code == '0000') {
            $final_score = trim($result_loan->finalScore);
            $final_result = trim($result_loan->result);
            if (isset($final_score)) {
                if ($final_result == 'Reject') {
                    //借款驳回
                    $result_reject = $this->rejectLoan($final_score, $loan_id, $amount, $user_id, $version);
                } else {
                    if ($final_score >= 60) {
                        //借款驳回
                        $result_reject = $this->rejectLoan($final_score, $loan_id, $amount, $user_id, $version);
                    } else {
                        $ret_score = $this->setLoanScore($loan_id, $final_score);
                    }
                }
            }
        }

        return true;
    }

    /**
     * 修改借款状态为5
     * @param unknown $code
     * @return unknown
     */
    public function setLoanStatus($type, $loan_id, $amount, $create_time, $version) {
        if ($type == 1) {
            $sql_loan = "update " . User_loan::tableName() . " set status=5,current_amount=(current_amount+" . $amount . "),last_modify_time='$create_time',withdraw_time='$create_time',version=(version+1) where loan_id=" . $loan_id . " and version=" . $version;

            //记录 5
            $flowdata = (object) array('loan_id' => $loan_id, 'status' => 5);
            $flow = new Flow();
            $flow->CreateFlow($flowdata, 0);
        } else if ($type == 2) {
            $sql_loan = "update " . User_loan::tableName() . " set current_amount=(current_amount+" . $amount . "),last_modify_time='$create_time',version=(version+1) where loan_id=" . $loan_id . " and version=" . $version;
        }

        $ret_loan = Yii::$app->db->createCommand($sql_loan)->execute();
        if ($ret_loan) {
            return true;
        } else {
            return false;
        }
    }

    public function addRejectLoan($user, $loan_no, $amount, $days, $desc, $status, $final_score, $coupon_id, $coupon_amount, $source = 2, $final_result = 'Reject', $business_type = 1) {

        $day_rate = 0.0005;
        $interest_fee = round($amount * $day_rate * $days, 2);
        $withdraw_fee = (round($amount * 0.1, 2) > 5) ? round($amount * 0.1, 2) : 5;

        //借款驳回
        $condition = array(
            'user_id' => $user->user_id,
            'loan_no' => $loan_no,
            'real_amount' => $amount,
            'amount' => $amount,
            'credit_amount' => 0,
            'recharge_amount' => 0,
            'current_amount' => 0,
            'days' => $days,
            'status' => $status,
            'prome_status' => 1,
            'interest_fee' => $interest_fee,
            'desc' => $desc,
            'withdraw_fee' => $withdraw_fee,
            'final_score' => $final_score,
            'bank_id' => -1,
            'source' => !empty($source) ? $source : 2,
            'is_calculation' => 1,
            'business_type' => $business_type,
        );
        if (!empty($coupon_id)) {
            $condition['coupon_amount'] = $interest_fee > $coupon_amount ? $coupon_amount : $interest_fee;
        }
        $loans = $this->addUserLoan($condition);
        Logger::errorLog(print_r(array($loans), TRUE), 'create_loan');
        $loan = User_loan::findOne($loans);
        if (!empty($coupon_id)) {
            $couponUseModel = new Coupon_use();
            $couponUseModel->addCouponUse($user, $coupon_id, $loan);
        }
        $fr = Fraudmetrix_return_info::find()->where(['loan_id' => $loan_no])->one();

        if (!empty($fr)) {
            $fr->loan_id = $loans;
            $result = $fr->save();
        }

        if ($loans) {
            if ($final_result == 'Reject') {
                $reason = '请30天后再次尝试借款';
            } else if ($final_score >= 60) {
                $reason = '请一周后再次尝试发起借款';
            } else {
                $reason = '暂不符合借款要求';
            }
            $flowsModel = new User_loan_flows();
            $flowsModel->loan_id = $loan->loan_id;
            $flowsModel->admin_id = '-1';
            $flowsModel->loan_status = $status;
            $flowsModel->reason = $reason;
            $flowsModel->create_time = date('Y-m-d H:i:s');
            $flowsModel->save();
            return true;
        } else {
            return false;
        }
    }

    /**
     * 
     * @param type $loan_id
     * @param type $amount 借款金额
     * @param type $loan_coupon   Coupon_list
     * @param type $interest_fee  利息
     * @param type $withdraw_fee 服务费
     */
    public function getLoanCouponAmount($loan_id, $amount, $interest_fee, $withdraw_fee, $loan_coupon) {
        if (empty($loan_id) || empty($loan_coupon)) {
            return NULL;
        }
        $loan = User_loan::findOne($loan_id);
        if (($loan_coupon['val'] == 0) && ($loan_coupon['limit'] <= $amount)) {
            $coupon_amount = 0;
        } else {
            if ($loan_coupon['limit'] == 0) {
                if ($loan->is_calculation == 1) {
                    if ($loan_coupon['val'] >= $interest_fee) {
                        $coupon_amount = $interest_fee;
                    } else {
                        $coupon_amount = $loan_coupon['val'];
                    }
                } else {
                    if ($loan_coupon['val'] >= $interest_fee + $withdraw_fee) {
                        $coupon_amount = $interest_fee + $withdraw_fee;
                    } else {
                        $coupon_amount = $loan_coupon['val'];
                    }
                }
            } else {
                if ($loan_coupon['limit'] <= $amount) {
                    if ($loan->is_calculation == 1) {
                        if ($loan_coupon['val'] >= $interest_fee) {
                            $coupon_amount = $interest_fee;
                        } else {
                            $coupon_amount = $loan_coupon['val'];
                        }
                    } else {
                        if ($loan_coupon['val'] >= $interest_fee + $withdraw_fee) {
                            $coupon_amount = $interest_fee + $withdraw_fee;
                        } else {
                            $coupon_amount = $loan_coupon['val'];
                        }
                    }
                } else {
                    $coupon_amount = 0;
                }
            }
        }
        return $coupon_amount;
    }

    /**
     * 获取借款续期费用
     * @param $loan_id
     * @return float|int
     */
    public function getRenewalMoney($loan_id) {
        $loan = self::findOne($loan_id);
        if (empty($loan)) {
            return 0;
        }
        $renewModel = new Renew_amount();
        $renew = $renewModel->isCanrenew($loan);
        if ($renew) {
            $money = $renewModel->getRenewFee($loan);
            if ($money) {
                return round($money, 2);
            }
        }
        $money = $loan->withdraw_fee + $loan->interest_fee + 30;
        return round($money, 2);
    }

    public function createRenewLoan() {
        if ($this->number >= 2 || $this->status != 9) {//最多续期两次,只有待还款才能续期
            return FALSE;
        }
        $end_date = date('Y-m-d 00:00:00', strtotime('+28 days', strtotime($this->end_date)));
        $number = $this->number + 1;
        $parent_loan_id = $this->parent_loan_id;
        $parent_loan = User_loan::findOne($parent_loan_id);
        $res = $this->saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id);
        if ($res) {
            $condition = [
                'settle_type' => 2,
            ];
            $up = $this->updateUserLoan($condition);
            if ($up) {
                return $this->changeStatus(8);
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    private function saveRenewLoan($parent_loan, $end_date, $number, $parent_loan_id) {
        $o = new self;
        foreach ($parent_loan as $key => $val) {
            if ($key == 'loan_id') {
                continue;
            }
            if ($key == 'parent_loan_id') {
                $o->parent_loan_id = $parent_loan_id;
                continue;
            }
            if ($key == 'number') {
                $o->number = $number;
                continue;
            }
            if ($key == 'settle_type') {
                $o->settle_type = 3;
                continue;
            }
            if ($key == 'end_date') {
                $o->end_date = $end_date;
                continue;
            }
            if ($key == 'start_date') {
                if ($number == 2) {
                    $info = User_loan::find()->where(['parent_loan_id' => $parent_loan_id, 'number' => 1])->one();
                    $o->start_date = $info->end_date;
                } else {
                    $o->start_date = $parent_loan->end_date;
                }
                continue;
            }
            if ($key == 'version') {
                $o->{$key} = 1;
                continue;
            }
            if ($key == 'create_time' || $key == 'last_modify_time') {
                $o->{$key} = date('Y-m-d H:i:s');
                continue;
            }
            if ($key == 'status') {
                $o->{$key} = 9;
                continue;
            }
            $o->{$key} = $val;
        }
        return $o->save();
    }

    /**
     * 最近一笔驳回借款
     * @param $user_id
     * @return array|bool|null|ActiveRecord
     */
    public function rejectLoanInfo($user_id) {
        if (empty($user_id))
            return false;
        $loan_info = User_loan::find()->where(['user_id' => $user_id])->andWhere(['in', 'status', array(3, 7)])->orderBy(['last_modify_time' => SORT_DESC])->one();
        if (!empty($loan_info)) {
            return $loan_info;
        }
        return array();
    }

    /**
     * 借款驳回判断方法
     * 规则：限制用户：初贷（未成功借款）用户
      限制条件：被人工审核驳回（有驳回原因）后未进行修改借款资料的用户
      借款资料：单位信息（名称，电话，地址），联系人信息（姓名，关系，手机号），邮箱，通讯录（是否增加）
      限制时间：本次借款人工信审驳回后未修改资料两天（可调阈值）内发起借款即被系统延迟驳回
     * @param $user_id  用户user_id
     * @return bool
     */
    public function LoanJudgment($user_id) {
        if (empty($user_id))
            return false;
        //初贷（未成功借款）用户
        $first_loan_user = $this->firstLoanUser($user_id);
        if (!empty($first_loan_user))
            return true;
        $loan_info = $this->rejectLoanInfo($user_id);
        //不存在返回true
        if (empty($loan_info))
            return true;
        //限制时间为2天内
        $one_day_time = 60 * 60 * 24 * 2;
        $time = time();
        //取出最近修改时间
        $last_modify_time = strtotime($loan_info['last_modify_time']);
        $limit_time = $time - $last_modify_time;
        //驳回超过2天返回true
        if ($limit_time > $one_day_time)
            return true;
        //取出联系人最后修改的时间
        $favorite_contacts = new Favorite_contacts();
        $favorite_info = $favorite_contacts->getFavoriteByUserId($user_id);
        //判断联系人最后修改时间
        if (!empty($favorite_info)) {
            $favorite_last_time = strtotime($favorite_info['last_modify_time']);
            if ($last_modify_time < $favorite_last_time)
                return true;
        }
        //判断历时记录时间
        $User_history_info = new User_history_info();
        $history_info = $User_history_info->newestHistory($user_id);
        if (!empty($history_info)) {
            $history_last_time = strtotime($history_info->create_time);
            if ($last_modify_time < $history_last_time)
                return true;
        }
        return false;
    }

    /**
     * 用户借款成功
     * @return array|bool|null|ActiveRecord
     */
    private function firstLoanUser($user_id) {
        if (empty($user_id))
            return false;
        $loan_info = User_loan::find()->where(['user_id' => $user_id])->andWhere(['in', 'status', array(8, 9, 11, 12, 13)])->one();
        if (!empty($loan_info)) {
            return $loan_info;
        }
        return array();
    }
    /**
     * 获取应还款的金额
     * @param $loanInfo
     * @return float|int|string
     */
    public function getRepaymentAmountnew($loanInfo) {
        $total_amount = $this->getAllMoney($loanInfo->loan_id);
        if ($loanInfo->status != 8) {
            $already_money = $this->getRepayAmountnew(2);
            if ($already_money != 0) {
                $total_amount = bcsub($total_amount, $already_money, 2);
            }
        }
        if ($total_amount * 10000 % 100 != 0) {
            return ceil($total_amount * 100) / 100;
        } else {
            return $total_amount;
        }
    }

    /**
     * 获取借款原始应还款金额
     * @param int loan_id 借款ID
     * @param int type 1:计算借款总应还款金额 2：计算借款 本金+利息+手续费 或者 本金+利息
     * @return float
     */
    private function getAllMoney($loan_id, $type = 1) {
        $loan = User_loan::findOne($loan_id);
        $coupon_amount = $this->getCouponAmount($loan_id);
        if ($loan->is_calculation == 1) {
            $moneys = $loan->amount + $loan->interest_fee;
        } else {
            $moneys = $loan->amount + $loan->interest_fee + $loan->withdraw_fee;
        }
        if ($type == 2) {
            return $moneys;
        }
        if (!empty($loan->chase_amount)) {
            $total_amount = $loan->chase_amount;
        } else {
            if ($loan->status == 7 || ($loan->prome_status == 1 && $loan->status == 3)) {
                $total_amount = $moneys - $coupon_amount;
            } else {
                $total_amount = $moneys - $loan->like_amount - $coupon_amount;
                $total_amount = $total_amount >= $loan->amount ? $total_amount : $loan->amount;
            }
        }
        if ($total_amount * 10000 % 100 != 0) {
            return ceil($total_amount * 100) / 100;
        } else {
            return $total_amount;
        }
    }

    /**
     * 获取借款优惠金额
     * @param $loan_id
     * @return int|mixed|string
     */
    public function getCouponAmount($loan_id) {
        $loan = User_loan::findOne($loan_id);
        $use_coupon = Coupon_use::find()->where(['loan_id' => $loan_id])->one();
        if (!empty($use_coupon)) {
            $coupon_list = Coupon_list::find()->where(['id' => $use_coupon->discount_id])->one();
            $coupon_amount = $coupon_list->val == 0 ? $loan->interest_fee : ($loan->interest_fee > $coupon_list->val ? $coupon_list->val : $loan->interest_fee);
        } else {
            $coupon_amount = 0;
        }
        return $coupon_amount;
    }
    /**
     * 还款金额
     * @param int $type 1:原有获取方式  2：续期状态已还款金额
     * @return type
     */
    public function getRepayAmountnew($type = 1) {
        $parent_id = $this->parent_loan_id;
        if (empty($parent_id)) {
            $type = 1;
        }
        if ($type == 1) {
            $loan_id = $this->loan_id;
            $amount = Loan_repay::find()->where(['loan_id' => $loan_id, 'status' => 1])->sum('actual_money');
        } else {//续期
            $loan_id = User_loan::find()->select(['loan_id'])->where(['parent_loan_id' => $parent_id])->asArray()->all();
            $loan_ids = ArrayHelper::getColumn($loan_id, 'loan_id');
            $amount = Loan_repay::find()->where(['loan_id' => $loan_ids, 'status' => 1])->sum('actual_money');
        }
        return $amount;
    }

    /**
     * 计算借款服务费
     * @param $amount 借款金额
     * @param $with_fee 服务费比例，如果不传使用user_loan对象的属性$with_fee,如果有特殊的就用特殊的
     * @return mixed
     */
    public function getServiceAmountnew($amount, $with_fee = 0) {
        if ($with_fee == 0) {
            return $amount * $this->with_fee;
        }
        return $amount * $with_fee;
    }

    /**
     * 
     * @param type $user
     * @param type $from
     * @param type $amount
     * @param type $days
     * @param type $desc
     * @param type $loan_no
     * @return int  0:没有触犯规则 1：添加驳回借款  2：拉黑用户了，直接跳转
     */
    public function getRule($user, $from, $amount, $days, $desc, $loan_no) {
        $api = new XhhApi();
        $limit = $api->runDecisions($user, $from, 'loan', $amount, $days, $desc);
        Logger::dayLog('loan_limit', $user->mobile, $limit);
        if (!empty($limit) && isset($limit['res_code'])) {
            return 0;
        }
        $limit_new = [];
        if (!empty($limit)) {
            $mark = 0;
            foreach ($limit as $key => $val) {
                if (in_array($key, ['loan_time_start', 'loan_time_end', 'age_value', 'more_loan_value', 'one_more_loan_value', 'seven_more_loan_value', 'one_number_account_value', 'is_black'])) {
                    if (!empty($val)) {
                        $limit_new[$key] = $val;
                        $mark = 1;
                    }
                }
            }
            if ($mark == 0) {
                return 0;
            }
        }
        $loanAll = static::find()->where(['user_id' => $user->user_id, 'business_type' => [1, 4]])->count();
        if (empty($limit_new) || (count($limit_new) == 1 && isset($limit_new['one_more_loan_value']) && $loanAll == 0)) {
            return 0;
        }
        $condition = $limit_new;
        $condition['loan_no'] = $loan_no;
        $event = Loan_event::addRecord($user->user_id, $condition);
        if (isset($limit['is_black']) && $limit['is_black'] == 1) {
            return 2;
        }
        return 1;
    }

}
