<?php 

namespace app\models\slience;

use Yii;
use app\commonapi\Logger;
/**
 * This is the model class for table "{{%yi_sloan}}".
 *
 * @property string $id
 * @property string $loan_id
 * @property integer $slient_number
 * @property string $user_id
 * @property string $amount
 * @property integer $days
 * @property string $start_date
 * @property string $end_date
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 * @property string $repay_time
 */
class Sloan extends\app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static

    function tableName() {
        return '{{%yi_sloan}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        [
            ['loan_id', 'slient_number', 'user_id', 'amount', 'chase_amount', 'coupon_amount', 'start_date', 'end_date', 'last_modify_time', 'create_time', ], 'required'], [
            ['loan_id', 'slient_number', 'user_id', 'days', 'ostatus', 'status', 'version'], 'integer'], [
            ['amount', 'chase_amount', 'coupon_amount'], 'number'], [
            ['start_date', 'end_date', 'last_modify_time', 'create_time'], 'safe']];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return ['id' => '主键', 'loan_id' => '主借款ID', 'slient_number' => '暗续次数', 'user_id' => '用户ID', 'amount' => '借款金额', 'chase_amount' => '逾期金额', 'coupon_amount' => '优惠券', 'days' => '借款天数', 'start_date' => '起息日', 'end_date' => '到期日', 'ostatus' => '借款状态：1初始；2通过；3驳回；4失效；5已提现；', 'status' => '借款状态：1初始；2通过；3驳回；4失效；5已提现；', 'last_modify_time' => '最后修改时间，对应状态变更时间', 'create_time' => '创建时间', 'version' => '乐观所版本号', 'orepay_time' => '真还款时间', 'repay_time' => '还款时间', ];
    }
    /**
     * 乐观所版本号
     * **/
    public function optimisticLock() {
        return "version";
    }
    /**
     * 指定指定一天数据
     * @param  [type] $theday [description]
     * @return [type]         [description]
     */
    public function importSliences($theday) {
        $oPerfect = new PerfectRate;
        $support_days = $oPerfect->getSupportDays();

        $map = [];
        foreach($support_days as $days) {
            $map[$days] = $this->importSlience($theday, $days);
        }
        return $map;
    }
    /**
     * 获取逾期计算信息
     * @param  string $theday 某一天
     * @param  int $days 逾期天数
     * @return bool
     */
    public function importSlience($theday, $days) {
        //1. 获取逾期天数为$days的数据统计信息
        $days = intval($days);
        if (!$days) {
            return false;
        }
        $oPerfect = new PerfectRate;
        $isSupport = $oPerfect->isSupport($days);
        if (!$isSupport) {
            return false;
        }

        $info = $this->getAccInfo($theday, $days);
        if (empty($info)) {
            return false;
        }
        if (!$info['total']) {
            return false;
        }
        $rate = ($info['total'] - $info['normal']) / $info['total'] * 100;

        //2. 需要导入的数据条数
        $nums = $this->getNeedNums($days, $rate, $info['total']);

        //3. 获取需要导入的条数
        $loans = $this->getSlienceLoan($theday, $days, $nums);

        //4. 导入数据
        $success_nums = $this->createSlienceLoan($loans, $theday, $days);

        Logger::dayLog('slience/importSlience', '指定的日期', $theday, '逾期天数', $days, '逾期统计', $info, '需导入的条数', $nums, '实际导入条数', count($loans), '成功导入条数', $success_nums);
        return $success_nums > 0;
    }
    /**
     * 获取需要补齐的条数
     * @param  int $days  天数
     * @param  float $rate  比率
     * @param  int $total 总数
     * @return int
     */
    private function getNeedNums($days, $rate, $total) {
        $oPerfect = new PerfectRate;
        $nums = $oPerfect->getNeedNums($days, $rate, $total);
        return $nums;
    }
    /**
     * 获取条数
     * @param str $theday 指定某一天
     * @param int $nums 导入条数
     * @return loans
     */
    private function getSlienceLoan($theday, $days, $limit) {
        if (!$limit) {
            return null;
        }
        $thetime = strtotime($theday);
        $start_date = date('Y-m-d', $thetime - $days * 86400);
        $end_date = date('Y-m-d', $thetime - $days * 86400 + 86400);
        $where = ['AND', ['slient_number' => 0],
            ['!=', 'status', 8],
            ['>=', 'end_date', $start_date],
            ['<', 'end_date', $end_date], ];
        $loans = self::find()->where($where)->offset(0)->limit($limit)->orderBy('end_date ASC')->all();
        // 再查询nstatus=8, repay > end_date的数据
        return $loans;
    }

    /**
     * 获取正在进行的续期的数据
     * @param  int loan_id 借款ID
     * @return $loan
     */
    private function getSloan($loan_id) {
        $where = ['AND', ['loan_id' => $loan_id],
            ['>', 'slient_number', 0],
            ['!=', 'status', 8]
        ];
        $sloan = self::find()->where($where)->one();
        return $sloan;
    }

    /**
     * 查询每一时间段内, 逾期为$days的天数
     * @param  string $theday 某一天
     * @param  int $days 逾期天数
     * @return  [theday,total,normal]
     */
    private function getAccInfo($theday, $days) {
        $thetime = strtotime($theday);
        $start_date = date('Y-m-d', $thetime - $days * 86400);
        $end_date = date('Y-m-d', $thetime - $days * 86400 + 86400);
        $max_repay_date = date('Y-m-d', $thetime + 86400);

        // 获取总数
        //static::find()->where($where)->count();
        $tb = static::tableName();
        $sql = "SELECT COUNT(1) AS total FROM {$tb} 
                    WHERE slient_number = 0 AND 
                    end_date>='{$start_date}' 
                    AND end_date<'{$end_date}'";

        echo $sql."\n";
        Logger::dayLog('slience/sql', $sql);
        $total = $this->queryScalar($sql);
        if (!$total) {
            return null;
        }

        // 获取正常还款
        $sql = "SELECT COUNT(1) AS normal FROM  {$tb}  
                    WHERE slient_number = 0 AND 
                    status = 8 AND
                    repay_time>=start_date AND 
                    repay_time<'{$max_repay_date}' AND
                    end_date>='{$start_date}'  AND
                    end_date<'{$end_date}'";

        Logger::dayLog('slience/sql', $sql);
        $normal = $this->queryScalar($sql);

        return $info = ['theday' => $theday, 'total' => $total, 'normal' => $normal, ];
    }
    private function queryScalar($sql) {
        $connection = Yii::$app->db;
        $command = $connection->createCommand($sql);
        $result = $command->queryScalar();
        return $result;
    }
    /**
     * 生成一条成功借款数据
     */
    public function createSuccessLoan($data) {
        $postData = ['loan_id' => $data['loan_id'], 'slient_number' => 0, 'user_id' => $data['user_id'], 'amount' => $data['amount'], 'chase_amount' => $data['chase_amount'], 'coupon_amount' => 0, 'days' => $data['days'], 'start_date' => $data['start_date'], 'end_date' => $data['end_date'], 'ostatus' => $data['status'], 'status' => $data['status'], 'last_modify_time' => $data['last_modify_time'], 'create_time' => $data['create_time'], 'version' => 1, 'orepay_time' => isset($data['repay_time']) ? $data['repay_time'] : '', 'repay_time' => isset($data['repay_time']) ? $data['repay_time'] : ''];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 同步借款的状态
     */
    public function saveLoanstatus($status, $last_modify_time, $repay_time) {
        $this->ostatus = $status;
        $this->last_modify_time = $last_modify_time;
        $this->orepay_time = !empty($repay_time) ? $repay_time : $last_modify_time;

        $result = $this->save();
        if (!$result) {
            return $result;
        }

        $sloan = $this->getSloan($this->loan_id);
        if (empty($sloan)) {
            return true;
        }
        //修改续期记录slient_number >0 ,loan_id,status != 8 
        //ostatus,status,repay_time,orepay_time
        $sloan->ostatus = $status;
        $sloan->status = $status;
        $sloan->repay_time = !empty($repay_time) ? $repay_time : $last_modify_time;
        $sloan->orepay_time = !empty($repay_time) ? $repay_time : $last_modify_time;

        $result_sloan = $sloan->save();

        return $result_sloan;
    }

    /**
     * 同步逾期数据的状态和逾期金额
     */
    public function saveOverduestatus($status, $last_modify_time) {
        $this->ostatus = $status;
        $this->last_modify_time = $last_modify_time;

        $result = $this->save();
        return $result;
    }

    /**
     * 批量创建续期
     * @param  [] $loans yi_user_loan
     * @return int
     */
    private function createSlienceLoan($loans, $theday, $days) {
        if (empty($loans)) {
            return 0;
        }

        $newLoan = [];
        foreach($loans as $loan) {
            // 更新还款时间
            $pre_days = (new PerfectRate)->getPreDays($days);
            $min_date = date('Y-m-d', strtotime($loan->end_date) + $pre_days * 86400);
            $max_date = date('Y-m-d', strtotime($loan->end_date) + $days * 86400);

            $loan->status = 8;
            $loan->repay_time = $this->randRepayDate($min_date, $max_date);
            try{
                $result = $loan->save();
            }catch(\Exception $e){
                $result = false;
            }
            
            if (!$result) {
                Logger::dayLog('slience/sloan', $loan->error);
                continue;
            }

            // 保存续期纪录
            $start_date = date('Y-m-d', strtotime($loan->end_date) + 86400);
            $end_date = date('Y-m-d', strtotime($start_date) + ($loan->days + 1) * 86400);

            // 整百+加优惠卷 1020(real_amount) -> 1000(amount) + 20(coupon_amount)
            $real_amount = $loan->amount + $loan->chase_amount;
            $coupon_amount = $real_amount % 100;
            $amount = intval($real_amount - $coupon_amount);

            $temp = [
                'loan_id' => $loan->loan_id, 
                'slient_number' => $loan->slient_number + 1, 
                'user_id' => $loan->user_id,
                'amount' => $amount,
                'chase_amount' => 0,
                'coupon_amount' => $coupon_amount, 
                'days' => $loan->days,
                'start_date' => $start_date,
                'end_date' => $end_date, 
                'ostatus' => '9', 
                'status' => '9',
                'last_modify_time' => date('Y-m-d H:i:s'), 
                'create_time' => $start_date,
                'version' => '0',
                'repay_time' => null,
                'orepay_time' => null,
            ];
            $data[] = $temp;
        }
        $nums = $this->insertBatch($data);
        return $nums;
    }
    /*
     * 随机还款时间
     */
    private function randRepayDate($start_date, $end_date) {
        $time = rand(strtotime($end_date), strtotime($start_date));
        return date('Y-m-d H:i:s', $time);
    }
}