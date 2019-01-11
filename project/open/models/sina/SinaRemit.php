<?php

namespace app\models\sina;

use app\common\Logger;

/**
 * 新浪出款
 *
 */
class SinaRemit extends \app\models\BaseModel
{

    // 支付状态 0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)
    const STATUS_INIT         = 0;
    const STATUS_REQING_REMIT = 1; // 出款请求中
    const STATUS_DOING        = 3; // 受理中
    const STATUS_REQING_QUERY = 4; // 查询请求中
    const STATUS_SUCCESS      = 6; // 成功
    const STATUS_FAILURE      = 11; // 支付失败
    const STATUS_HTTP_NOT_200 = 12; // 无响应
    const STATUS_QUERY_MAX    = 13; // 查询达上限

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'sina_remit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['req_id', 'aid', 'user_id', 'identity_id', 'cardno', 'callbackurl', 'create_time', 'modify_time', 'query_time'], 'required'],
            [['aid', 'remit_status', 'query_num', 'client_status', 'version'], 'integer'],
            [['settle_amount'], 'number'],
            [['create_time', 'modify_time', 'remit_time', 'query_time'], 'safe'],
            [['req_id', 'client_id', 'cardno', 'rsp_status', 'withdraw_status', 'ip'], 'string', 'max' => 50],
            [['user_id'], 'string', 'max' => 20],
            [['identity_id'], 'string', 'max' => 30],
            [['rsp_status_text', 'callbackurl'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'              => '结算记录ID',
            'req_id'          => '订单号',
            'client_id'       => '新浪流水号',
            'aid'             => '应用id',
            'user_id'         => '客户端id',
            'identity_id'     => '新浪唯一标识',
            'cardno'          => '银行卡号',
            'settle_amount'   => '[必填]结算金额',
            'remit_status'    => '打款状态:0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)',
            'rsp_status'      => '响应状态',
            'rsp_status_text' => '响应结果',
            'withdraw_status' => '新浪出款提现状态:INIT    初始化;SUCCESS:成功(异步通知);FAILED:失败(异步通知);PROCESSING:处理中;',
            'callbackurl'     => '异步通知回调url',
            'create_time'     => '创建时间',
            'modify_time'     => '更新时间',
            'remit_time'      => '出款时间',
            'query_time'      => '下次查询时间',
            'query_num'       => '查询次数',
            'client_status'   => '客户端响应',
            'ip'              => 'ip',
            'version'         => '乐观锁',
        ];
    }
    public function getStatus()
    {
        return [
            static::STATUS_INIT         => '初始',
            static::STATUS_REQING_REMIT => '出款请求中',
            static::STATUS_DOING        => '受理中',
            static::STATUS_REQING_QUERY => '查询请求中',
            static::STATUS_SUCCESS      => '成功',
            static::STATUS_FAILURE      => '支付失败',
            static::STATUS_HTTP_NOT_200 => '无响应',
            static::STATUS_QUERY_MAX    => '查询次数超限'];
    }

    public function getByReqId($req_id)
    {
        return static::find()->where(['req_id' => $req_id])->limit(1)->one();
    }
    private function sameRequest($user_id, $cardno, $settle_amount)
    {
        $where = [
            'AND',
            [
                'user_id'       => $user_id,
                'cardno'        => $cardno,
                'settle_amount' => $settle_amount,
            ],
            ['>', 'create_time', date("Y-m-d H:i:s", strtotime('-1 day'))],
        ];
        $data = static::find()->where($where)->limit(1)->one();
        return $data;
    }
    /**
     * 添加一条纪录到数据库
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function saveData($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $oSame = $this->sameRequest($data['user_id'], $data['cardno'], $data['settle_amount']);
        if ($oSame) {
            return false;
        }

        //保存数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'req_id'          => $data['req_id'],
            'client_id'       => '',
            'aid'             => $data['aid'],
            'user_id'         => $data['user_id'],
            'identity_id'     => $data['identity_id'],
            'cardno'          => $data['cardno'],
            'settle_amount'   => $data['settle_amount'],
            'remit_status'    => 0,
            'rsp_status'      => '',
            'rsp_status_text' => '',
            'withdraw_status' => '',
            'callbackurl'     => $data['callbackurl'],
            'create_time'     => $time,
            'modify_time'     => $time,
            'remit_time'      => '0000-00-00 00:00:00',
            'query_time'      => $time,
            'query_num'       => 0,
            'client_status'   => 0,
            'ip'              => $data['ip'],
            'version'         => 0,
        ];
        $errors = $this->chkAttributes($data);
        if ($errors) {
            Logger::dayLog('sinauser', '保存失败', $data, $errors);
            return false;
        }

        return $this->save();
    }
    /**
     * 回写响应结果
     * $this 操作数据
     * @param $rsp_status 接口响应状态
     * @param $rsp_status_text 接口响应结果
     * @param $withdraw_status 提现状态
     * @param $client_id 新浪内部id
     * @return bool
     */
    public function saveRspStatus($rsp_status, $rsp_status_text, $withdraw_status, $client_id)
    {
        $this->rsp_status      = $rsp_status;
        $this->rsp_status_text = $rsp_status_text;
        $this->withdraw_status = $withdraw_status;
        if ($client_id) {
            $this->client_id = $client_id;
        }

        if ($rsp_status == 'APPLY_SUCCESS') {
            // 是否需要更新出款状态
            $map = $this->getSinaStatus();
            if ($withdraw_status && isset($map[$withdraw_status])) {
                $this->remit_status = $map[$withdraw_status];
            } else {
                $this->remit_status = static::STATUS_DOING;
            }
        } elseif (in_array($rsp_status, ['CARD_TYPE_NOT_SUPPORT', 'MEMBER_ID_NOT_EXIST'])) {
            // 明确失败才失败
            $this->remit_status = static::STATUS_FAILURE;
        } else {
            $this->remit_status = static::STATUS_DOING;
        }

        // 终态时更新出款时间
        if (in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            $this->remit_time = date('Y-m-d H:i:s');
        }

        $this->modify_time = date('Y-m-d H:i:s');
        $result            = $this->save();
        return $result;
    }
    /**
     * 新浪状态映射关系
     */
    public function getSinaStatus()
    {
        //INIT 初始化;SUCCESS:成功(异步通知);FAILED:失败(异步通知);PROCESSING:处理中;
        //0:初始化;1:出款请求中;3:受理中;4:查询请求中;6:成功;11:失败;12:无响应(预留)
        $map = [
            'INIT'       => static::STATUS_INIT,
            'PROCESSING' => static::STATUS_DOING,
            'SUCCESS'    => static::STATUS_SUCCESS,
            'FAILED'     => static::STATUS_FAILURE,
        ];
        return $map;
    }
    public function optimisticLock()
    {
        return "version";
    }
    /**
     * 返回客户端响应结果
     * @return  []
     */
    public function clientData()
    {
        return [
            'req_id'          => $this->req_id,
            'client_id'       => $this->client_id,
            'settle_amount'   => $this->settle_amount,
            'remit_status'    => $this->remit_status,
            'rsp_status'      => $this->rsp_status,
            'rsp_status_text' => $this->rsp_status_text,
            'channel_id'      => 1
        ];
    }
    /**
     * POST 异步通知客户端
     * @return bool
     */
    public function clientPost($callbackurl, $data, $aid)
    {
        //1 加密
        $res_data = \app\models\App::model()->encryptData($aid, $data);
        $postData = ['res_data' => $res_data, 'res_code' => 0];

        //2 post提交
        $oCurl = new \app\common\Curl;
        $res   = $oCurl->post($callbackurl, $postData);
        Logger::dayLog('sinaback/clientPost', 'post', "客户响应|{$res}|", $callbackurl, $data);

        //3 解析结果
        $res = strtoupper($res);
        return $res == 'SUCCESS';
    }

    /**
     * POST 异步通知客户端:并仅通知最终结果, 即(成功|失败)
     * @return bool
     */
    public function clientNotify()
    {
        // 已经通知过了
        /*if ($this->client_status == 1) {
        return true;
        }*/
        if (!in_array($this->remit_status, [static::STATUS_SUCCESS, static::STATUS_FAILURE])) {
            return false;
        }
        // 更新通知状态
        $data   = $this->clientData();
        $result = $this->clientPost($this->callbackurl, $data, $this->aid);
        if ($result) {
            $this->client_status = 1;
            $this->modify_time   = time();
            $result              = $this->save();
        } else {
            //@todo 加入通知队列中
            //
        }
        return $result;
    }
    /**
     * 获取需要等待出款的数据
     */
    public function getInitData($limit)
    {
        $where = ['AND',
            ['remit_status' => static::STATUS_INIT],
            ['>', 'create_time', date('Y-m-d H:i:00', strtotime('-2 hour'))],
            ['<', 'create_time', date('Y-m-d H:i:00', strtotime('-1 hour'))],
        ]; //@todo 暂定跑5小时前
        $data = static::find()->where($where)->orderBy('create_time ASC')->offset(0)->limit($limit)->all();
        return $data;
    }
    /**
     * 锁定正在出款接口的状态
     */
    public function lockRemit($ids)
    {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['remit_status' => static::STATUS_REQING_REMIT], ['id' => $ids]);
        return $ups;
    }
}
