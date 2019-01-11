<?php

namespace app\models\dev;

use app\models\dev\User;
use app\models\dev\User_loan;
use app\commonapi\Common;
use app\commonapi\Logger;
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
class User_invest extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_user_invest';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    public function getLoan() {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getUser() {
        return $this->hasOne(User::className(), ['user_id' => 'loan_user_id']);
    }

    /**
     * 获取该笔借款对应的投资人的信息和投资额度
     */
    public function getInvestUserList($loan_id) {
        $sql = "SELECT i.amount AS invest_amount,u.realname,w.head AS invest_head_url FROM " . User_invest::tableName() . " AS i LEFT JOIN " . User::tableName() . " AS u ON u.user_id=i.user_id LEFT JOIN " . Userwx::tableName() . " AS w ON u.openid=w.openid WHERE i.loan_id=" . $loan_id;
        $invest_list = Yii::$app->db->createCommand($sql)->queryAll();
        return $invest_list;
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
     * 获取用户当前投资记录和借款记录
     */
    public function getInvestLoan($user_id, $status = array(5, 6, 9, 10, 11, 12, 13)) {
        if (empty($user_id)) {
            return false;
        }
        $result = $this->find()->select([User_invest::tableName() . '.*'])->joinWith('loan', true, 'LEFT JOIN')->Where([User_invest::tableName() . '.user_id' => $user_id]);
        if (!empty($status)) {
            $result = $result->andWhere(['yi_user_loan.status' => $status]);
        }
        $result = $result->orderBy('yi_user_invest.create_time desc')->all();
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    /**
     * 获取我的投资情况：逾期与收益的纪录
     * 逾期:12,13  
     * 收益:6,9,10,11
     */
    public function getMyInvest($user_id) {
        if (empty($user_id)) {
            return null;
        }
        $investTable = static::tableName();
        $loanTable = User_loan::tableName();
        $result = static::find()->select([$loanTable . '.*'])
                ->leftJoin($loanTable, "{$investTable}.loan_user_id = {$loanTable}.user_id")
                ->where([
                    'AND',
                    ["{$investTable}.user_id" => $user_id,
                        "{$investTable}.status" => 1,],
                    ['IN', "{$loanTable}.status", [6, 9, 10, 11, 12, 13]]
                ])->andWhere("{$investTable}.loan_id={$loanTable}.loan_id")
                ->orderBy('end_date DESC')
                ->all();
        return $result;
    }

    /**
     * 获取好友正在申请借款的列表
     * 表示我可以投资
     */
    public function getWillIncome($user_id) {
        //1 获取我的好友id
        if (empty($user_id)) {
            return null;
        }
        $userModel = new User();
        $friends = $userModel->getFriendsByUserId($user_id);
        if (empty($friends)) {
            return null;
        }

        //2 状态等于1的是可投资的列表
        $result = (new User_loan)->getWillIncomeByUids($friends);
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    /**
     * 获取可投资的列表
     */
    public function getInvestList($user_id) {
        //1 逾期和收益的
        $invest = $this->getMyInvest($user_id);

        //2  可投资的列表
        $willincomes = []; //$this->getWillIncome($user_id);
        //print_r($invest); 
        //print_r($willincomes); exit;
        //3 合并两者的数据 // showStatus :  1收益中    2可投资      11逾期
        $allData = [];

        //4 逾期和收益
        if ($invest) {
            // showStatus : 1收益中   11逾期
            foreach ($invest as $r) {
                $temp = $r->attributes;
                if (in_array($temp['status'], [5, 6, 9, 10, 11])) {
                    $temp['showStatus'] = 1;
                } elseif (in_array($temp['status'], [12, 13])) {
                    $temp['showStatus'] = 11;
                }
                $allData[] = $temp;
            }
        }

        //5  获取可投资的
        if ($willincomes) {
            // showStatus :  2可投资
            foreach ($willincomes as $r) {
                $temp = $r->attributes;
                $temp['showStatus'] = 2;
                $allData[] = $temp;
            }
        }
        if (empty($allData)) {
            return null;
        }

        // 手动进行排序
        usort($allData, [$this, 'sortInvestList']);

        // 获取用户名昵称和头像
        $user_ids = Common::onlyIds($allData, 'user_id');
        if (is_array($user_ids) && !empty($user_ids)) {
            $users = (new User())->getInfoByUids($user_ids);
            Common::appends($allData, $users, 'user_id', false);
        }

        return $allData;
    }

    /**
     * 排序算法
     */
    public function sortInvestList($o1, $o2) {
        if ($o1['showStatus'] > $o2['showStatus']) {
            return -1;
        } elseif ($o1['showStatus'] < $o2['showStatus']) {
            return 1;
        } else {
            // 可投资
            if ($o1['showStatus'] == 2) {
                return $o1['open_end_date'] < $o2['open_end_date'] ? -1 : 1;
            } else {
                return $o1['end_date'] < $o2['end_date'] ? -1 : 1;
            }
        }
    }

    /**
     * 获取用户对当前借款的最大可投额度
     */
    public function getLargeAmount($user_id, $loan_id, $loan_amount, $amount, $investamount) {
        $loaninfobyuserandloan = User_invest::find()->select(array('amount', 'invest_id', 'version'))->where(['user_id' => $user_id])->andWhere(['loan_id' => $loan_id])->andWhere(['status' => 1])->one();
        if (!empty($loaninfobyuserandloan)) {

            //已投资，计算筹款金额的三分之一
            $loanthree = ceil($loan_amount / 3);
            //计算投资者原始信用点的二分之一
            $investsecond = floor($amount);
            //比较以上2种金额的大小
            $between_amount = ($loanthree - $investsecond) >= 0 ? $investsecond : $loanthree;
            //计算还可以投资的金额
            $l_amount = ($between_amount - $loaninfobyuserandloan->amount) > 0 ? $between_amount - $loaninfobyuserandloan->amount : 0;
            //比较between_amount和investamount这2个参数的大小
            $left_amount = ($investamount - $l_amount) >= 0 ? $l_amount : $investamount;
            //可投资的金额
            //$left_amount = ($bet_amount - $loaninfobyuserandloan->amount) >= 0 ? floor($bet_amount - $loaninfobyuserandloan->amount) : floor($bet_amount);
        } else {
            //已投资，计算筹款金额的三分之一
            $loanthree = ceil($loan_amount / 3);
            //计算投资者原始信用点的二分之一
            $investsecond = floor($amount);
            //比较以上2种金额的大小
            $between_amount = ($loanthree - $investsecond) >= 0 ? $investsecond : $loanthree;
            //比较between_amount和investamount这2个参数的大小
            $bet_amount = ($investamount - $between_amount) >= 0 ? $between_amount : $investamount;
            //可投资的金额
            $left_amount = floor($bet_amount);
        }

        return $left_amount;
    }

    /**
     * 查询投资用户信息
     */
    public function getInvestUserInfo($user_id) {
        if (empty($user_id)) {
            return null;
        }

        $sql_investinfo = "select u.user_id,u.realname,a.version,a.amount from yi_user as u,yi_account as a where u.user_id=$user_id and u.user_id = a.user_id";
        $userinfo = Yii::$app->db->createCommand($sql_investinfo)->queryOne();

        return $userinfo;
    }

    /**
     * 投资人投资额度返回，同时投资人账户当前投资和总投资减
     */
    public function setInvestAccount($invest_id, $version, $amount, $user_id) {
        $invest_sql = "update " . User_invest::tableName() . " set status=2 ,version=version+1 where invest_id=" . $invest_id . " and version=" . $version;
        $ret_invest_sql = Yii::$app->db->createCommand($invest_sql)->execute();

        if (!$ret_invest_sql) {
            //记录错误日志
            Logger::errorLog($invest_sql, 'withreject');
        }
        $invest_acc_sql = "update " . Account::tableName() . " set current_invest=current_invest-" . $amount . ",total_invest=total_invest-" . $amount . ",current_amount=current_amount+" . $amount . ",version=version+1 where user_id=" . $user_id;
        $ret_invest_acc_sql = Yii::$app->db->createCommand($invest_acc_sql)->execute();
        if (!$ret_invest_acc_sql) {
            //记录错误日志
            Logger::errorLog($invest_acc_sql, 'withreject');
        }

        return true;
    }

    /**
     * 添加投资记录
     */
    public function addInvestInformation($user_id, $loan_user_id, $loan_id, $amount, $yield, $start_date, $end_date, $status, $create_time, $version) {
        //添加一条投资记录
        $model = new User_invest();
        $model->user_id = $user_id;
        $model->loan_user_id = $loan_user_id;
        $model->loan_id = $loan_id;
        $model->amount = $amount;
        $model->yield = $yield;
        $model->start_date = $start_date;
        $model->end_date = $end_date;
        $model->status = $status;
        $model->create_time = $create_time;
        $model->version = $version;

        if ($model->save()) {
            $invest_id = $invest_id = Yii::$app->db->getLastInsertID();
            return $invest_id;
        } else {
            return false;
        }
    }

    /**
     * 修改投资记录
     */
    public function setInvestInformation($invest_id, $amount, $version) {
        $sql_invest = "update " . User_invest::tableName() . " set amount=(amount+" . $amount . "),version=(version+1) where invest_id=" . $invest_id . " and version=" . $version;
        $ret_invest = Yii::$app->db->createCommand($sql_invest)->execute();

        return $ret_invest;
    }

    /**
     * 生成投资合同
     */
    public function createContract($invest_id, $loan_id) {
        $url = Yii::$app->request->hostInfo . "/dev/pdf/investtopdf?invest_id=" . $invest_id . "&loan_id=" . $loan_id;
        $filepath = Yii::$app->basePath . '/log/pdf/invest/' . date('Y') . '/' . date('m') . '/' . date('d');
        Logger::createdir($filepath);
        $contract = 'invest_' . $invest_id;
        $filename = $filepath . '/' . $contract . '.pdf';
        $this->htmltoPdf($url, $filename);
        //修改合同编号和存放路径
        $contract_url = '/log/pdf/invest/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $contract . '.pdf';
        $ret_invest = $this->setContractNumber($invest_id, $contract, $contract_url);

        return true;
    }

    /**
     * 修改合同编号和存放路径
     */
    private function setContractNumber($invest_id, $contract, $contract_url) {
        $sql_xhb = "update yi_user_invest set contract='$contract',contract_url='$contract_url' where invest_id=" . $invest_id;
        $ret_invest = Yii::$app->db->createCommand($sql_xhb)->execute();

        return true;
    }

    /**
     * 将HTML页面转化为PDF格式的文档
     * @param string $url
     * @param string $filename
     * @return boolean
     */
    private function htmltoPdf($url = null, $filename = null) {
        //实例化
        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // 设置文档信息
        $pdf->SetCreator('Helloweba');
        $pdf->SetAuthor('yueguangguang');
        $pdf->SetTitle('合同实例');
        $pdf->SetSubject('TCPDF Tutorial');
        $pdf->SetKeywords('TCPDF, PDF, PHP');

        // 设置页眉和页脚信息

        $pdf->setFooterData(array(0, 64, 0), array(0, 64, 128));

        // 设置页眉和页脚字体
        $pdf->setHeaderFont(Array('stsongstdlight', '', '10'));
        $pdf->setFooterFont(Array('helvetica', '', '8'));

        // 设置默认等宽字体
        $pdf->SetDefaultMonospacedFont('courier');

        // 设置间距
        $pdf->SetMargins(15, 27, 15);
        $pdf->SetHeaderMargin(5);
        $pdf->SetFooterMargin(10);

        // 设置分页
        $pdf->SetAutoPageBreak(TRUE, 25);

        // set image scale factor
        $pdf->setImageScale(1.25);

        // set default font subsetting mode
        $pdf->setFontSubsetting(true);

        //设置字体
        $pdf->SetFont('stsongstdlight', '', 12);

        $pdf->AddPage();

        $strContent = file_get_contents($url);

        $pdf->writeHTML($strContent, true, false, true, false, '');
        //输出PDF
        $pdf->Output($filename, 'F');

        return true;
    }

}
