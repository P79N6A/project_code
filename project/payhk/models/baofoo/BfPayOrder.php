<?php
/**
 * 宝付商编之间转账支付
 *
 */

namespace app\models\baofoo;
use app\common\Func;
use \app\common\Logger;

class BfPayOrder extends \app\models\BaseModel {

    // 支付状态 0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;9:退款 11:失败;12:无响应(预留) 13： 查询达上限
    const STATUS_INIT = 0;
    const STATUS_REQING_REMIT = 1; // 支付中
    const STATUS_DOING = 3; // 受理中
    const STATUS_REQING_QUERY = 4; // 查询请求中
    const STATUS_SUCCESS = 6; // 成功
    const STATUS_REFUND = 9; // 退款
    const STATUS_FAILURE = 11; // 支付失败
    const STATUS_HTTP_NOT_200 = 12; // 无响应
    const STATUS_QUERY_MAX = 13; // 查询达上限
    const MAX_QUERY_NUM = 7; // 最大查询次数

    /**
     * @inheritdoc
     */

    public static function tableName() {
        return 'policy_payorder';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [
                ['aid', 'req_id', 'client_id', 'settle_amount', 'remit_status', 'create_time', 'modify_time'], 'required'], [
                ['aid', 'remit_status', 'query_num', 'version'], 'integer'], [
                ['settle_amount'], 'number'], [
                ['create_time', 'modify_time', 'remit_time', 'query_time'], 'safe'], [
                ['req_id', 'client_id'], 'string', 'max' => 40], [
                ['rsp_status', 'bf_orderid'], 'string', 'max' => 50], [
                ['rsp_status_text'], 'string', 'max' => 255]
            ];
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
            'client_id' => '[系统生成]流水号(内部对宝付)',
            'bf_orderid' => '宝付流水号(宝付内部)',
            'settle_amount' => '[必填]结算金额',
            'remit_status' => '打款状态:0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)13：查询超限',
            'rsp_status' => '宝付:响应状态:空为新加, RSP_TIMEOUT表示无响应',
            'rsp_status_text' => '宝付:响应结果',
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
        $time = date('Y-m-d H:i:s'); 
        $client_id = $this->getClientId($postData['aid']);
        $data = [
            'aid' => $postData['aid'],
            'req_id' => $postData['req_id'],
            'client_id' => $client_id,
            'settle_amount' => $postData['settle_amount'],
            'remit_status' => self::STATUS_INIT,
            'rsp_status'        => '',
            'rsp_status_text'   => '',
            'bf_orderid' => '',
            'create_time' => $time,
            'modify_time' => $time,
            'remit_time' => '0000-00-00 00:00:00',
            'query_time' => $time,
        ];
        //3  是否存在出款记录（同一应用、同一人、同一类型、同一天）
        $remitReq = $this->getRemitByReqid($postData['aid'], $postData['req_id']);
        if ($remitReq > 0) {
            return $this->returnError(null, "订单重复提交");
        }

        //4  字段检测
        $model = new self();
        $errors = $model->chkAttributes($data);
        if ($errors) {
            return $this->returnError(null, implode('|', $errors));
        }
        //5  保存数据
        $result = $model->save();
        if (!$result) {
            return $this->returnError(null, implode('|', $model->errors));
        }

        $returndata = ['req_id' => $postData['req_id'], 'remit_status' => self::STATUS_INIT, 'client_id' => $client_id, 'settle_amount' => $postData['settle_amount']];
        return $returndata;
    }
    /**
     * 根据商户唯一订单号查询
     * @param  str $client_id
     * @return bool
     */
    public function getByClientId($client_id) {
        if (!$client_id) {
            return null;
        }
        return static::find()->where(['client_id' => $client_id])->limit(1)->one();
    }
    /**
     * 根据请求订单号查询
     * @param  str $reqId
     * @return bool
     */
    public function getByReqId($reqId) {
        if (!$reqId) {
            return null;
        }
        return static::find()->where(['req_id' => $reqId])->limit(1)->one();
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
    public function getClientId() {
        $time = date('YmdHis', time());
        $str = rand(1000, 9999);
        $clientId = "B" . $time . $str;
        $where = ['client_id' => $clientId];
        $ret = static::find()->where($where)->count();
        if ($ret > 0) {
            $clientId = $this->getClientId();
        }
        return $clientId;
    }

    /**
     * 受理中
     * lockDoingRemit
     * @return bool
     */
    public function saveToDoing($bfOrderid){
        if(!$bfOrderid){
            return false;
        }
        //1. 是否已经处理过了
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->query_time = date('Y-m-d H:i:s');
        $this->query_num = 0;
        $this->modify_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_DOING;
        $this->bf_orderid = $bfOrderid;
        return $this->save();
    }

    /**
     * 保存remit_status状态为成功
     * @return bool
     */
    public function saveToSuccess($bfOrderid){
        // 终态时更新出款时间
        if(!$bfOrderid){
            return false;
        }
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->remit_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_SUCCESS;
        $this->modify_time = date('Y-m-d H:i:s');
        $this->bf_orderid = $bfOrderid;
        $result = $this->save();
        return $result;
    }

    /**
     * 保存remit_status状态为失败
     * @param $rsp_status
     * @param $rsp_status_text
     * @return bool
     */
    public function saveToFail($rsp_status, $rsp_status_text) {
        if(empty($rsp_status) || empty($rsp_status_text)){
            return false;
        }
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->remit_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_FAILURE;
        $this->rsp_status = $rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 保存remit_status状态为退款
     * @param $rsp_status
     * @param $rsp_status_text
     * @return bool
     */
    public function saveToRefund($bfOrderid) {
        if(!$bfOrderid){
            return false;
        }
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        $this->remit_time = date('Y-m-d H:i:s');
        $this->remit_status = static::STATUS_REFUND;
        $this->modify_time = date('Y-m-d H:i:s');
        $this->bf_orderid = $bfOrderid;
        $result = $this->save();
        return $result;
        }
    /**
     * 更新下次查询时间
     * @return bool
     */
    public function saveNextQuery() {
        // 当是出款中时, 更新下次的查询时间
        // 累加查询次数
        $this->query_num++;
        if ($this->query_num < WsmRemit::MAX_QUERY_NUM) {
            // 未超通知限制的时候, 计算下次查询时间间隔
            $this->query_time = $this->acQueryTime($this->query_num, $this->query_time);
            $this->remit_status = WsmRemit::STATUS_DOING;
        } else {
            // 超出查询次数限制. 将查询中的变更为超限状态
            $this->remit_status =WsmRemit::STATUS_QUERY_MAX; // 转人工处理
        }
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 回写响应结果
     * $this 操作数据
     * @param $remit_status 响应状态
     * @param $rsp_status 宝付接口响应状态
     * @param $rsp_status_text 宝付接口响应结果
     * @param $sub_remit_time 提交宝付出款时间
     * @param $bf_orderid 宝付订单号
     * @param $source 1: 出款调用; 2:查询调用;3:异步通知调用
     * @return bool
     */
    public function saveRspStatus($remit_status, $rsp_status, $rsp_status_text, $sub_remit_time,$bf_orderid, $source) {
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
        if ($source == 1 && $sub_remit_time) {
            $this->sub_remit_time = $sub_remit_time;
        }
        $this->remit_status = $remit_status;
        if($source == 1 && $bf_orderid){
            $this->bf_orderid = (string)$bf_orderid;
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
    public function getInitData($limit) {
        $where = ['AND', ['remit_status' => static::STATUS_INIT],
            ['>', 'create_time', date('Y-m-d H:i:00', strtotime('-7 day'))],
            ['<', 'create_time', date('Y-m-d H:i:00')]
        ];
        $data = static::find()->where($where)->orderBy('create_time ASC')->offset(0)->limit($limit)->all();
//        print_r($data);
        return $data;
    }


    /**
     * 获取正在处理中的数据
     */
    public function getDoingData($limit) {
        $where = ['AND', ['remit_status' => static::STATUS_DOING],
            ['>', 'query_time', date('Y-m-d H:i:00', strtotime('-7 day'))],
            ['<', 'query_time', date('Y-m-d H:i:00')]];
        // 按查询时间排序
        $data = static::find()->where($where)->orderBy('query_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 锁定正在转账支付的状态
     */
    public function lockRemit($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_REQING_REMIT], ['id' => $ids]);
        return $ups;
    }
    /**
     * 单条锁定正在转账的状态
     */
    public function lockOneRemit(){
        try{
            $this->remit_status = static::STATUS_REQING_REMIT;
            $result = $this->save();
        }catch(\Exception $e){
            Logger::dayLog('bfpay/lock', 'lockOneRemit',$this->id,'fail');
            $result = false;
        }
        return $result;
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
     * 单条锁定正在查询接口的状态
     */
    public function lockOneQuery(){
        try{
            $this->remit_status = static::STATUS_REQING_QUERY;
            $result = $this->save();
        }catch(\Exception $e){
            Logger::dayLog('bfpay/lock', 'lockOneQuery', $this->id,'fail');
            $result = false;
        }
        return $result;
    }


}
