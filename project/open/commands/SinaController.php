<?php
/**
 * 出款计划任务
 * windows d:\xampp\php\php.exe D:\www\open\yii sina
 */
// 公司内部充值
// */5 7-22 * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii sina innerpay 1>/dev/null 2>&1

namespace app\commands;
use app\common\Logger;
use app\models\sina\SinaAutoRecharge;
use app\models\sina\SinaRemit;
use app\models\sina\SinaUser;
use app\modules\api\common\sinapay\CSinaRemit;
use app\modules\api\common\sinapay\Sinapay;
use Yii;

/**
 * 新浪
 */
class SinaController extends BaseController {
    /**
     * 公司内部充值到中间帐号
     * 由于可能存在单次限额, 故可在数据库中添加多条纪录
     * 每日执行一次
     * 基本户->中间户
     */
    public function innerpay($max_money = 7500000) {
        // 创建充值订单
        $data = $this->createPays($max_money);
        if (!is_array($data) || empty($data)) {
            echo '0笔充值';exit;
        }

        // 处理充值
        $total = count($data);
        $success = 0;
        foreach ($data as $obj) {
            try {
                $res = $obj->innerpay($obj->req_id, $obj->amount, '121.199.129.16');
                $success++;
            } catch (\Exception $e) {
                Logger::dayLog("sinacommand", 'innerpay', '充值失败');
            }
        }
        echo "共充值笔数: {$total}; 成功数:{$success}\n";
    }
    /**
     * 基本户->还款
     * @return [type] [description]
     */
    public function innerpayrepay($returnMoney = 10) {
        $stuff = sprintf("%03d", $i);
        $req_id = 'repay' . date('YmdHis') . $stuff;

        $model = new SinaAutoRecharge();
        $model->generate($req_id, $returnMoney);

        $ip = '121.199.129.16';
        $oSinapayApi = new Sinapay();
        $response = $oSinapayApi->inner_pay_money_repay($req_id, $returnMoney, $ip);
        Logger::dayLog("sinacommand", 'innerpayrepay', '基本户->还款', $response);
        echo var_export($response, true);
    }
    /**
     * 基本户->还款
     * @return [type] [description]
     */
    public function innerpayreturn($returnMoney = 5) {
        $stuff = sprintf("%03d", $i);
        $req_id = 'return' . date('YmdHis') . $stuff;

        $model = new SinaAutoRecharge();
        $model->generate($req_id, $returnMoney);

        $ip = '121.199.129.16';
        $oSinapayApi = new Sinapay();
        $response = $oSinapayApi->create_single_hosting_pay_trade($req_id, $returnMoney, $ip);
        Logger::dayLog("sinacommand", 'innerpayreturn', '返回基本户', $response);
        echo var_export($response, true);
    }

    /**
     * 查看余额
     * @return 查询余额
     */
    public function showMoney() {
        $basic = number_format($this->getBasicMoney());
        $mid = number_format($this->getMiddleMoney());
        $content = date('Y-m-d H:i:s') . " :基本户:{$basic} /中间户(代付):{$mid}\n";

        $file = \Yii::$app->basePath . '/log/sinacommand/restMoney.log';
        file_put_contents($file, $content, FILE_APPEND);
        echo $content;
    }
    /**
     * 批量生成充值纪录
     * @return []
     */
    private function createPays($max_money) {
        $money = $this->getBasicMoney();
        $max_money = (int) $max_money;
        $money = (int) $money;
        $money = $money - 50000; // 手续费
        if ($money < 0) {
            return null;
        }
        if ($money > $max_money) {
            $money = $max_money;
        }

        $amount = 500000; // 最小50万
        $total = floor($money / $amount);
        $data = [];
        for ($i = 0; $i < $total; $i++) {
            $stuff = sprintf("%03d", $i);
            $req_id = 'auto' . date('YmdHis') . $stuff;
            $model = new SinaAutoRecharge();
            $model->generate($req_id, $amount);
            $data[] = $model;
        }
        return $data;
    }
    /**
     * 基本户余额
     * @return [type] [description]
     */
    private function getBasicMoney() {
        $sinapay = new Sinapay();
        $amount = $sinapay->query_balance('200034807310');
        return $amount;
    }
    /**
     * 中间户余额
     * @return float
     */
    private function getMiddleMoney() {
        $sinapay = new Sinapay();
        $res = $sinapay->query_middle_account('1001');
        if (!$res) {
            return 0;
        }
        $arr = explode('^', $res);
        $restMoney = $arr[2];
        return $restMoney;
    }
    // end 充值

    /**
     * 自动更新密码状态
     */
    public function notifypassword() {
        $user = new SinaUser;
        $rows = $user->pwdNoSet();
        if (empty($rows)) {
            return 0;
        }
        foreach ($rows as $oUser) {
            $res = $oUser->notifypassword();
            echo $oUser->user_id, ":", $res . "\n";
        }
    }
    /**
     * 重试出款
     * @param int $id
     */
    public function retryOne($id) {
        //1 查询
        $id = intval($id);
        if (!$id) {
            echo "{$id}:must be >0";exit;
        }
        $oSinaRemit = SinaRemit::find()->where(['id' => $id, 'remit_status' => 0])->one();
        if (!$oSinaRemit) {
            echo "{$id}:not exists 此id不存在, 或者状态不合法";exit;
        }

        //2. 调用接口是否出过款
        $identity_id = $oSinaRemit['identity_id'];
        $out_trade_no = $oSinaRemit['req_id'];

        $oSinapayApi = new Sinapay();
        $res = $oSinapayApi->query_pay_result($out_trade_no);
        if (is_array($res) && isset($res['response_code']) && $res['response_code'] == 'ILLEGAL_OUTER_TRADE_NO') {
            $result = (new CSinaRemit)->doRemit($oSinaRemit);
            var_export($result);
        }
        echo "id : " . $oSinaRemit->id . " : status :" . $oSinaRemit->remit_status;
    }
    /**
     * 每10分钟计划任务
     * 查询1-3天前小时状态为0的出款
     */
    public function notifyPayresult() {
        $start_time = date('Y-m-d H:i:00', strtotime('-72 hours'));
        $end_time = date('Y-m-d H:i:00', strtotime('-24 hours'));
        $where = [
            'AND',
            ['remit_status' => [SinaRemit::STATUS_INIT, SinaRemit::STATUS_REQING_REMIT]],
            ['>=', 'create_time', $start_time],
            ['<', 'create_time', $end_time],
        ];
        $oSinapayList = SinaRemit::find()->where($where)->limit(100)->orderBy('create_time ASC')->all();

        $sinapay = new Sinapay();
        foreach ($oSinapayList as $oSinapay) {
            $identity_id = $oSinapay['identity_id'];
            $out_trade_no = $oSinapay['req_id'];

            //$res = $sinapay->query_hosting_withdraw($identity_id, $out_trade_no);
            $res = $sinapay->query_pay_result($out_trade_no);
            if (is_array($res) && isset($res['response_code']) && $res['response_code'] == 'ILLEGAL_OUTER_TRADE_NO') {
                $oSinapay->rsp_status = $res['response_code'];
                $oSinapay->rsp_status_text = $res['response_message'];
                $oSinapay->remit_status = SinaRemit::STATUS_FAILURE;
                $result = $oSinapay->clientNotify();
            }
            Logger::dayLog("sinapay/notifyPayresult", $res, $oSinapay->attributes);
        }
    }
}
