<?php

namespace app\models\baofoo;

use app\models\Payorder;
use app\common\Func;
use \app\common\Logger;
use app\models\BindBank;

/**
 * 宝付绑卡从表
 *
 */
class BfBindbank extends \app\models\BaseModel {

    // 支付状态
    const STATUS_INIT = 0;
    const STATUS_REQOK = 1; // 请求成功
    const STATUS_BINDOK = 2; // 绑定成功
    const STATUS_DOING = 4; //处理中
    const STATUS_BINDFAIL = 11; // 绑定失败
    const STATUS_REQNO = 12; // 请求失败

    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'pay_bf_bindbank';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'channel_id', 'identityid','cli_identityid', 'requestid', 'cardno', 'bankname', 'idcard', 'name', 'phone', 'userip', 'create_time', 'modify_time',], 'required'],
            [['aid', 'channel_id', 'status'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['cli_identityid', 'requestid', 'cardno','return_bindingid','bankname'], 'string', 'max' => 50],
            [['bankcode', 'idcard', 'name', 'phone', 'userip', 'identityid',  'codesender','error_code'], 'string', 'max' => 20],
            [['idcardtype', 'smscode'], 'string', 'max' => 10],
            [['error_msg'], 'string', 'max' => 100],
            [['requestid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'channel_id' => '支付通道id',
            'identityid' => '商户生成的用户唯一',
            'requestid' => '商户生成的唯一绑卡请求号，最长',
            'cardno' => '银行卡号',
            'bankname' => '银行名称',
            'bankcode' => '银行编号',
            'idcardtype' => '证件类型固定值01',
            'idcard' => '身份证号',
            'name' => '姓名',
            'phone' => '银行留存电话',
            'userip' => '（可选）用户请求ip地址',
            'return_bindingid'=>'宝付返回bind_id',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'error_code' => '(内部)宝付返回错误码',
            'error_msg' => '(内部)宝付返回原因',
            'codesender' => '短信发送方 YEEPAY：宝付发送 | BANK：银行发送 | MERCHANT：商户发送',
            'smscode' => '短信验证码',
            'status' => '0:默认; 1:请求成功; 2:绑定成功 ;11:请求失败; 12:绑定失败',
        ];
    }

    public function getBaofooBank() {
        return $this->hasOne(BaofooBank::className(), ['bankname' => 'bankname']);
    }
    /**
     * 更新状态
     */
    public function updateStatus($requestid, $status,$return_bindingid = '') {
        if (!$requestid) {
            return FALSE;
        }
        if (!is_numeric($status)) {
            return FALSE;
        }
        $one = static::find()->where(['requestid' => $requestid])->limit(1)->one();
        if (!$one) {
            return FALSE;
        }
        if($return_bindingid){
            $one->return_bindingid = $return_bindingid;
        }
        $one->status = $status;
        return $one->save();
    }

    public function saveSuccess() {
        $this->status = self::STATUS_BINDOK;
        $result = $this->save();
        if (!$result) {
            return false;
        }

        // @todo
        //导入到 主绑定表  pay_bindbank
    }
    /**
     * 判断某帐号是否成功绑定过此卡
     */
    public function getSameUserCard($aid,$channel_id, $identityid, $cardno) {
        $where = [
            // 'aid'=>$aid,
            'channel_id' => $channel_id,
            // 'identityid' => $identityid,
            'cardno' => $cardno,
            'status' => self::STATUS_BINDOK,
        ];
        $one = static::find()->where($where)->limit(1)->orderBy('id DESC')->one();
        return $one;
    }
    /**
     * 判断某帐号是否成功绑定过此卡
     */
    public function getByRequest($requestid) {
        if (!$requestid) {
            return null;
        }
        $where = [
            'requestid' => $requestid,
        ];
        return static::find()->where($where)->limit(1)->one();
    }

    /**
     * 从主订单进行绑卡操作
     * @param  [type] $oPayorder [description]
     * @return [type]            [description]
     */
    public function saveCard($data) {
        //1 生成结果
        $userip = Func::get_client_ip();
        if (!$userip) {
            $userip = '127.0.0.1';
        }

        $time = date('Y-m-d H:i:s');
        $request_id = "p" . $data['channel_id'] . '_' . time() . '_' . rand(10000, 99999);

        $cli_identityid = $this->getPayIdentityid(
                    $data['identityid'], 
                    $data['cardno'], 
                    $data['channel_id']
                );

        $postData = [
            'aid' => $data['aid'],
            'channel_id' =>  $data['channel_id'], 
            'identityid' =>  $data['identityid'], //用户标识√string最长50位，商户生成的用户唯一标识
            'cli_identityid' =>  $cli_identityid, //用户标识√string最长50位，商户生成的用户唯一标识
            'requestid' => $request_id, //'xhh_13581524051_8', //绑卡请求号√string商户生成的唯一绑卡请求号，最长50位
            'cardno' => $data['cardno'], //银行卡号√string
            'bankname' => $data['bankname'],
            'idcard' => $data['idcard'],//证件号√string
            'name' => $data['name'],  //持卡人姓名√string
            'phone' => $data['phone'], //银行预留手机号√string
            'userip' => $userip,
            'return_bindingid'=>'',
            'create_time' => $time,
            'modify_time' => $time,
            'error_code' => '',
            'error_msg' => '',
            'smscode' => '',
            'status' => 0,
        ];

        //2 是否已经绑定
        // 检测是否已经成功绑定过该卡@todo 此处宝付是不建议校验的
        $isBind = $this->getSameUserCard(
            $postData['aid'],
            $postData['channel_id'],
            $postData['identityid'],
            $postData['cardno']
        );
        if ($isBind) {
            return true;
        }

        //3. 字段检查是否正确
        if ($errors = $this->chkAttributes($postData)) {
			Logger::dayLog(
                'yeepay/bindcard',
                '提交数据', $postData,
                '失败原因', $errors
            );
            return $this->returnError(false, "数据保存失败");
        }
        return $this->save();
    }
    /**
     * 保存宝付请求状态结果
     * @return bool
     */
    public function saveReqStatus($bfResult) {
        //1. 保存宝付请求绑定结果信息
        $this->modify_time = date('Y-m-d H:i:s');
        $isError = is_array($bfResult) && isset($bfResult['error_code']);
        if ($isError) {
            // 失败时处理逻辑
            $this->error_code = $bfResult['error_code'];
            $this->error_msg = $bfResult['error_msg'];
            $this->status = self::STATUS_REQNO; //请求失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = self::STATUS_REQOK; //请求成功
            $this->codesender = $bfResult['codesender'];
            $this->smscode = $bfResult['smscode'] ? $bfResult['smscode'] : '';
            $result = $this->save();
        }

        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog( 'baofoo/auth','bindbank/saveReqStatus', $bfResult, $this->errors );
            return false;
        }
        return true;
    }
    /**
     * 处理最终绑卡结果
     * @param  string $bfResult
     * @return bool
     */
    public function saveRspStatus($bfResult) {
        //1 处理最终结果
        $this->modify_time = date('Y-m-d H:i:s');
        if (is_array($bfResult) && $bfResult['resp_code']!='0000') {
            // 失败时处理逻辑
            $this->error_code = $bfResult['resp_code'];
            $this->error_msg = $bfResult['resp_msg'];
            $this->status = BfBindbank::STATUS_BINDFAIL; //绑定失败
            $result = $this->save();
        } else {
            // 成功时处理逻辑
            $this->status = BfBindbank::STATUS_BINDOK; //确认绑定成功
            $this->return_bindingid = $bfResult['bind_id'];
            $result = $this->save();
            //生成一条主绑卡成功记录
            $BindBank = new BindBank();
            $BindBank ->succBindBank($this);
        }
        //2. 纪录数据库错误日志
        if (!$result) {
            Logger::dayLog('baofoo/auth','bindbank/saveRspStatus', $bfResult,  $this->errors  );
            return false;
        }
        return true;
    }
    /**
     * 转换
     */
    public function getPayIdentityid($identityid, $cardno, $channel_id) {
        if (!$identityid || !$cardno) {
            return '';
        }
        $card_top = substr($cardno, 0, 6);
        $card_last = substr($cardno, -4);
        $identityid = $identityid . '-' . $card_top . $card_last;

        $cli_identityid = Func::toYeepayCode($identityid, $channel_id); 
        return $cli_identityid;
    }

    /**
     * 找到指定时间段内状态处理中的订单
     * @return []
     */
    public function getAbnorList($start_time,$end_time){
        $start_time = date('Y-m-d H:i:00', strtotime($start_time));
		$end_time = date('Y-m-d H:i:00', strtotime($end_time));
		$where = ['AND',
			['status' => [Payorder::STATUS_DOING]],
			['>=', 'create_time', $start_time],
			['<', 'create_time', $end_time],
		];
		$dataList = self::find()->where($where)->all();
		if (!$dataList) {
			return [];
		}
		return $dataList;
    }
}
