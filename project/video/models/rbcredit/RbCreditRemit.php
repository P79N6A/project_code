<?php

namespace app\models\rbcredit;
use yii\helpers\ArrayHelper;

class RbCreditRemit extends \app\models\BaseModel {

    // 支付状态 0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)
    const STATUS_INIT = 0;
    const STATUS_REQING_REMIT = 1; // 出款请求中
    const STATUS_DOING = 3; // 受理中
    const STATUS_REQING_QUERY = 4; // 查询请求中
    const STATUS_SUCCESS = 6; // 成功
    const STATUS_FAILURE = 11; // 支付失败
    const STATUS_HTTP_NOT_200 = 12; // 无响应
    const STATUS_QUERY_MAX = 13; // 查询达上限
    const MAX_QUERY_NUM = 7; // 最大查询次数

    /**
     * @inheritdoc
     */

    public static function tableName() {
        return 'rb_credit_remit';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [
                ['aid', 'req_id', 'client_id','remit_status', 'remit_type', 'identityid', 'user_mobile', 'guest_account_name', 'guest_account_bank','guest_account_bank_code', 'guest_account_province', 'guest_account_city', 'guest_account', 'create_time', 'modify_time', 'callbackurl'], 'required'], [
                ['aid', 'remit_type', 'remit_status', 'account_type', 'query_num', 'version'], 'integer'], [
                ['settle_amount', 'settle_fee', 'real_amount'], 'number'], [
                ['create_time', 'modify_time', 'remit_time', 'query_time'], 'safe'], [
                ['req_id'], 'string', 'max' => 40], [
                ['client_id', 'guest_account'], 'string', 'max' => 30], [
                ['rsp_status','rb_orderid'], 'string', 'max' => 50], [
                ['rsp_status_text', 'settlement_desc'], 'string', 'max' => 255], [
                ['identityid','guest_account_bank_code'], 'string', 'max' => 20], [
                ['user_mobile', 'guest_account_name', 'guest_account_bank'], 'string', 'max' => 60], [
                ['guest_account_province', 'guest_account_city', 'guest_account_bank_branch'], 'string', 'max' => 150]];
    }

    /**
     * 乐观锁
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '结算记录ID',
            'aid' => '应用id',
            'req_id' => '请求ID(业务)',
            'client_id' => '[系统生成]流水号(内部对融宝)',
            'rb_orderid'=>'融宝返回订单号' ,
            'settle_amount' => '[必填]结算金额',
            'settle_fee' => '结算手续费',
            'real_amount' => '实际划款金额（除去手续费）',
            'remit_type' => '[必填]打款业务类型：1表示借款；2担保提现；3收益提现',
            'remit_status' => '打款状态:0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)', 
            'rsp_status' => '融宝:响应状态',
            'rsp_status_text' => '融宝:响应结果', 
            'identityid' => '[选填]用户身份证',
            'user_mobile' => '[选填]用户手机',
            'guest_account_name' => '[必填]帐号名称(持卡人姓名)', 
            'account_type' => '账户类型:0对私；1对公 目前全是私',
            'guest_account_bank' => '[收款]开户行名称', 
            'guest_account_bank_code'=>'开户行code',
            'guest_account' => '[必填]银行账号', 
            'guest_account_province' => '[收款]银行所属省', 
            'guest_account_city' => '[收款]银行所属市',
            'guest_account_bank_branch' => '[收款]银行所属支行', 
            'settlement_desc' => '[选填]结算描述信息',
            'create_time' => '创建时间', 
            'modify_time' => '更新时间',
            'remit_time' => '出款时间', 
            'query_time' => '下次查询时间', 
            'query_num' => '查询次数',
            'version' => '乐观锁'
        ];
    }

    public function getStatus() {
        return [
            static::STATUS_INIT => '初始',
            static::STATUS_REQING_REMIT => '出款请求中',
            static::STATUS_DOING => '受理中',
            static::STATUS_REQING_QUERY => '查询请求中',
            static::STATUS_SUCCESS => '成功',
            static::STATUS_FAILURE => '支付失败',
            static::STATUS_HTTP_NOT_200 => '无响应',
            static::STATUS_QUERY_MAX => '查询次数超限'];
    }

    /**
     * 保存数据
     * */
    public function saveRemitData($postData) {
        //1 数据验证
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(null, "数据不能为空");
        }
        if (empty($postData['req_id'])) {
            return $this->returnError(null, "业务订单不能为空");
        }
        if (empty($postData['aid'])) {
            return $this->returnError(null, "应用id不能为空");
        }
        //2  保存数据
        $time = date('Y-m-d H:i:s'); // 组合
        $client_id = $this->getClientId($postData['aid']);
        $data = [
            'aid'                     => ArrayHelper::getValue($postData,'aid',''),
            'req_id'                  => ArrayHelper::getValue($postData,'req_id',''),
            'client_id'               => $client_id,
            'settle_amount'           => ArrayHelper::getValue($postData,'settle_amount',''),
            'real_amount'             => ArrayHelper::getValue($postData,'settle_amount',''),
            'remit_type'              => ArrayHelper::getValue($postData,'remit_type',''),
            'remit_status'            => self::STATUS_INIT,
            'identityid'              => ArrayHelper::getValue($postData,'identityid',''),
            'user_mobile'             => ArrayHelper::getValue($postData,'user_mobile',''),
            'guest_account_name'      => ArrayHelper::getValue($postData,'guest_account_name',''),
            'guest_account_bank'      => ArrayHelper::getValue($postData,'guest_account_bank',''),
            'guest_account_bank_code' => ArrayHelper::getValue($postData,'guest_account_bank_code',''),
            'guest_account_province'  => ArrayHelper::getValue($postData,'guest_account_province',''),
            'guest_account_city'      => ArrayHelper::getValue($postData,'guest_account_city',''),
            'guest_account_bank_branch' => ArrayHelper::getValue($postData,'guest_account_bank_branch',''),
            'guest_account'           => ArrayHelper::getValue($postData,'guest_account',''),
            'create_time'             => $time,
            'modify_time'             => $time,
            'remit_time'              => '0000-00-00 00:00:00',
            'query_time'              => $time,
            'settlement_desc'         => ArrayHelper::getValue($postData,'settlement_desc',''),
            'callbackurl'             => ArrayHelper::getValue($postData,'callbackurl',''),
        ];
        //3  是否存在出款记录（同一应用、同一人、同一类型、同一天）
        $remitReq = $this->getRemitByReqid($postData['aid'], $postData['req_id']);
        if ($remitReq > 0) {
            return $this->returnError(null, "订单重复提交");
        }

        //4  超限算法: 是否存在出款记录（同一应用、同一人、同一类型、同一天）
        // 这段代码不是必要的这个是精确到秒的.高并发下没什么用途, (应使用id)
        $result = $this->addTopLimit($data['aid'], $data['identityid'], $data['remit_type'], $data['create_time']);
        if ($result) {
            return $this->returnError(null, "交易超限");
        }

        //5  字段检测
        $model = new self();
        $errors = $model->chkAttributes($data);
        if ($errors) {
            return $this->returnError(null, implode('|', $errors));
        }
        //6  保存数据
        $result = $model->save();
        if (!$result) {
            return $this->returnError(null, implode('|', $model->errors));
        }

        $returndata = ['req_id' => $postData['req_id'], 'remit_status' => self::STATUS_INIT, 'client_id' => $client_id, 'settle_amount' => $postData['settle_amount']];
        return $returndata;
    }

    /**
     * 相同订单号是否重复提交
     * */
    public function getRemitByReqid($aid, $reqId) {
        $where = ['aid' => $aid, 'req_id' => $reqId];
        $ret = static::find()->where($where)->count();
        return $ret;
    }

    /**
     * 生成出款流水号
     * */
    public function getClientId($aid) {
        $time = date('YmdHis', time());
        $str = rand(1000, 9999);
        $clientId = "R" . $time . $str;
        $where = ['aid' => $aid, 'client_id' => $clientId];
        $ret = static::find()->where($where)->count();
        if ($ret > 0) {
            $clientId = $this->getClientId($aid);
        }
        return $clientId;
    }

    /**
     * 回写响应结果
     * $this 操作数据
     * @param $remit_status 响应状态
     * @param $rsp_status 融宝接口响应状态
     * @param $rsp_status_text 融宝接口响应结果
     * @param $rb_orderid 融宝订单号
     * @param $source 1: 出款调用; 2:查询调用;3:异步通知调用
     * @return bool
     */
    public function saveRspStatus($remit_status, $rsp_status, $rsp_status_text,$rb_orderid = '', $source) {
        // 当是出款中时, 更新下次的查询时间
        if ($source == 2) {
            // 累加查询次数
            $this->query_num++;
            if ($this->query_num < static::MAX_QUERY_NUM) {
                // 未超通知限制的时候, 计算下次查询时间间隔
                $this->query_time = $this->acQueryTime($this->query_num, $this->query_time);
            } else {
                // 超出查询次数限制. 将查询中的变更为超限状态
                if ($remit_status == static::STATUS_DOING) {
                    $remit_status = static::STATUS_QUERY_MAX; // 转人工处理
                }
            }
        }
        $this->remit_status = $remit_status;
        if($source == 1 && $rb_orderid){
            $this->rb_orderid = (string)$rb_orderid;
        }
        $this->rsp_status = $rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->modify_time = date('Y-m-d H:i:s');
        // 终态时更新出款时间
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            $this->remit_time = date('Y-m-d H:i:s');
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 计算下次查询时间
     * @param int $query_num 当前次数
     * @param str $query_time 当前时间
     * @return str 下次查询时间
     */
    public function acQueryTime($query_num, $query_time) {
        // 累加的分钟
        $addMinutes = [
            1 => 10,
            2 => 30,
            3 => 60,
            4 => 120,
            5 => 240,
            6 => 1560];

        // 不在上述时,不改变
        if (!isset($addMinutes[$query_num])) {
            return $query_time;
        }

        // 累加时间
        $time = ($query_time == '0000-00-00 00:00:00') ? time() : strtotime($query_time);
        $t = $time + $addMinutes[$query_num] * 60;
        return date('Y-m-d H:i:s', $t);
    }

    /**
     * 获取需要等待出款的数据
     */
    public function getInitData($limit, $aid) {
        $where = ['AND', ['remit_status' => static::STATUS_INIT, 'aid' => $aid],
            ['<', 'create_time', date('Y-m-d H:i:00')]];
        $data = static::find()->where($where)->orderBy('create_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 检查当前纪录是否是重复的纪录
     * @param  Remit   $oRemit [description]
     * @return boolean         [description]
     */
    public function isTopLimit() {
        //1 同天身份证和类型重复的纪录
        $oRemit = $this;
        $begin_time = date('Y-m-d', strtotime($oRemit['create_time']));
        $where = [
            'AND',
            ['aid' => $oRemit->aid],
            ['remit_type' => $oRemit->remit_type],
            ['identityid' => $oRemit->identityid],
            ['>=', 'create_time', $begin_time],
            ['<', 'id', $oRemit->id],
            ['!=', 'remit_status', static::STATUS_FAILURE],
        ];
        $num = static::find()->where($where)->count();
        //2 分析是否超限
        return $this->isLimit($oRemit['remit_type'], $num);
    }

    /**
     * 每种类型不同的超限规则
     * @param int $remit_type 类型
     * @param int $num   重复次数
     * @return boolean  是否超限
     */
    private function isLimit($remit_type, $num) {
        if ($remit_type == '1' && $num >= 2) {
            //借款出款
            return true;
        } elseif ($remit_type == '2' && $num >= 4) {
            //担保卡出款
            return true;
        } elseif ($remit_type == '3' && $num > 0) {
            //收益提现
            return true;
        }
        return false;
    }

    /**
     * 获取正在处理中的数据
     */
    public function getDoingData($limit, $aid) {
        $where = ['AND', ['remit_status' => static::STATUS_DOING, 'aid' => $aid],
            ['<', 'query_time', date('Y-m-d H:i:00')]];
        // 按查询时间排序
        $data = static::find()->where($where)->orderBy('query_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 获取正在处理中的批量数据
     */
    public function getDoingBatchData($limit) {
        $where = ['AND', ['remit_status' => static::STATUS_DOING],
            ['<', 'query_time', date('Y-m-d H:i:00')]];
        // 按查询时间排序
        $data = static::find()->where($where)->orderBy('query_time ASC')->groupBy('batch_no')->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 根据batch_no和处理中的状态来查询所有的数据,以便锁定
     */
    public function getDoingByBatchData($batch_nos) {
        $where = [
            'AND',
            ['remit_status' => static::STATUS_DOING],
            ['batch_no' => $batch_nos],
            ['<', 'query_time', date('Y-m-d H:i:00')],
        ];
        // 按查询时间排序
        $data = static::find()->where($where)->all();
        return $data;
    }

    /**
     * 锁定正在出款接口的状态
     */
    public function lockRemit($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_REQING_REMIT], ['id' => $ids]);
        return $ups;
    }

    /**
     * 锁定正在查询接口的状态
     */
    public function lockQuery($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_REQING_QUERY], ['id' => $ids]);
        return $ups;
    }

    /**
     * 添加时是否超限
     * 获取用户是否存在出款信息，本应该是查STATUS_INIT= 0;STATUS_REQING_REMIT = 1;STATUS_DOING = 3;STATUS_REQING_QUERY = 4;STATUS_SUCCESS=6
     *
     * 目前先不限制，只要是用户有过记录就不再处理
     * */
    private function addTopLimit($aid, $identityid, $remit_type, $create_time) {
        // 检测条件为身份证, 出款类型,同一天同一应用
        $begin_time = date('Y-m-d', strtotime($create_time));
        $where = [
            'AND',
            ['aid' => $aid],
            ['remit_type' => $remit_type],
            ['identityid' => $identityid],
            ['>=', 'create_time', $begin_time],
            ['<', 'create_time', $create_time],
            ['!=', 'remit_status', static::STATUS_FAILURE],
        ];

        $num = static::find()->where($where)->count();
        return $this->isLimit($remit_type, $num);
    }

  

    /**
     * 获取今日预计达到最大的出款总额
     * 即除去失败的总额
     * @param  int $aid 出款aid
     * @return float 出款金额
     */
    public function getDayMoney($aid) {
        $aid = intval($aid);

        $start_time = date('Y-m-d');
        $end_time = date('Y-m-d', strtotime('+1 day'));

        $where = [
            'AND',
            ['aid' => $aid],
            ['>=', 'create_time', $start_time],
            ['<', 'create_time', $end_time],
            ['!=', 'remit_status', static::STATUS_FAILURE],
        ];
        $money = static::find()->select(['sum(settle_amount) as settle_amount'])->where($where)->scalar();
        return empty($money) ? 0 : $money;
    }

    /**
     * 与上同,按aid分组
     * @return []
     */
    public function getDayMoneyGroup() {

        $start_time = date('Y-m-d');
        $end_time = date('Y-m-d', strtotime('+1 day'));

        $where = [
            'AND',
            ['>=', 'create_time', $start_time],
            ['<', 'create_time', $end_time],
            ['!=', 'remit_status', static::STATUS_FAILURE],
        ];
        $data = static::find()->select(['sum(settle_amount) as settle_amount', 'aid'])->where($where)->groupBy('aid')->all();
        return $data;
    }

}
