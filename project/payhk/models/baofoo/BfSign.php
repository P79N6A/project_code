<?php
/**
 * 宝付协议支付签约
 */
namespace app\models\baofoo;
use Yii;
use yii\helpers\ArrayHelper;

class BfSign extends \app\models\BasePay {
    // 签约状态
    const STATUS_INIT = 0; // 初始
    const STATUS_SENDOK = 1; // 发送验证码成功
    const STATUS_SENDFAIL = 3; // 发送验证码失败
    const STATUS_BINDOK = 2; // 签约成功
    const STATUS_BINDFAIL = 11; // 签约失败

    public static function tableName() {
        return 'pay_bf_sign';
    }

    public function rules() {
        return [
            [['aid','channel_id','identityid','cli_identityid','cardno','bankname','idcard_type','idcard','name','phone','create_time','modify_time','status'], 'required'],
            [['aid', 'channel_id', 'status','card_cvv2'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['cli_identityid','pre_sign_msg','sign_msg','cardno','pre_sign_code','bankname'], 'string', 'max' => 50],
            [['idcard','name','phone','identityid','error_code'], 'string', 'max' => 20],
            [['idcard_type','card_date','bankcard_type'], 'string', 'max' => 10],
            [['card_date','bankcard_type'], 'string', 'max' => 5],
            [['error_msg'], 'string', 'max' => 100],
            [['sign_code'], 'string', 'max' => 150],
            [['pre_sign_msg','sign_msg'], 'unique']
        ];
    }

    public function attributeLabels() {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'channel_id' => '支付通道id',
            'identityid' => '商户生成的用户唯一',
            'cli_identityid' => '商户生成的该渠道下的用户唯一',
            'pre_sign_msg' => '商户生成的预签约请求流水',
            'sign_msg' => '商户生成的签约请求流水',
            'cardno' => '银行卡号',
            'card_cvv2' => '信用卡安全码',
            'card_date' => '信用卡有效期',
            'bankname' => '银行名称',
            'bankcard_type' => '银行卡类型',
            'idcard_type' => '证件类型',
            'idcard' => '身份证号',
            'name' => '姓名',
            'phone' => '银行留存电话',
            'pre_sign_code' => '预签约唯一码',
            'sign_code' => '用户的签约唯一码',
            'create_time' => '(内部)创建时间',
            'modify_time' => '(内部)最后修改时间',
            'error_code' => '(内部)宝付返回错误码',
            'error_msg' => '(内部)宝付返回原因',
            'status' => '0:默认; 1:已发送验证码; 2:签约成功; 11:签约失败',
        ];
    }

    public function getOne($data,$column='id'){
        if(!$data){
            return false;
        }
        $result = self::find()->where([$column=>$data])->one();
        return $result;
    }

	public function getSignInfo($payOrderInfo){
        if(!$payOrderInfo){
			return -1;
        }
		$where = [
			'aid' => $payOrderInfo->aid,
			'channel_id' => $payOrderInfo->channel_id,
			'identityid' => $payOrderInfo->identityid,
			'cardno' => $payOrderInfo->cardno,
            'status' => self::STATUS_BINDOK,
		];
		$oBfSign = self::find()->where($where)->one();
        if (!empty($oBfSign)) {
			return $oBfSign->id;
		}
        return 0;
    }

    /**
     * 保存用户支付银行卡信息
     * @param array $payOrderInfo 主订单订单信息
     * @return boolean 保存是否成功
     */
    public function addSignInfo($payOrderInfo){
        // 校验数据
        if (empty($payOrderInfo)) {
            return $this->returnError(false, "数据不能为空");
        }
        $aid = ArrayHelper::getValue($payOrderInfo, 'aid','');
        if(!$aid){
            return $this->returnError(false, "业务aid不能为空");
        }
        $identityId = ArrayHelper::getValue($payOrderInfo, 'identityid','');
        if (!$identityId) {
            return $this->returnError(false, "用户id不能为空");
        }
        $channelId = ArrayHelper::getValue($payOrderInfo, 'channel_id','');
        if (!$channelId) {
            return $this->returnError(false, "通道channel_id不能为空");
        }
        // 整理数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'aid' => $aid,
            'channel_id' => $channelId,
            'identityid' => $identityId,
            'cli_identityid' => $aid.'_'.$channelId.'_'.$identityId,
            'cardno' => ArrayHelper::getValue($payOrderInfo, 'cardno',''),
            'bankname' => ArrayHelper::getValue($payOrderInfo, 'bankname',''),
            'bankcard_type' => '10'.(string)(ArrayHelper::getValue($payOrderInfo, 'card_type','0')),
            'idcard' => ArrayHelper::getValue($payOrderInfo, 'idcard',''),
            'idcard_type' => '01', // 只支持身份类型使用身份证
            'name' => ArrayHelper::getValue($payOrderInfo, 'name',''),
            'phone' => ArrayHelper::getValue($payOrderInfo, 'phone',''),
            'create_time' => $time,
            'modify_time' => $time,
            'status' => self::STATUS_INIT,
        ];
        // 字段检测
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(false, implode('|', $errors));
        }
        // 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(false, implode('|', $this->errors));
        }
        return $result;
    }

    /**
     * 保存请求宝付获取签约信息
     * @param object $oSignInfo 签约表数据对象
     * @param array $saveData 存储的数据
     * @return boolean
     */
    public function saveSignInfo($oSignInfo,$saveData) {
        // 校验数据格式
        if(!$oSignInfo || !$saveData){
            return false;
        }
        $error = $oSignInfo->chkAttributes($saveData);
        if ($error) {
            return $this->returnError(false, '数据格式校验失败');
        }
        // 保存数据信息
        $oSignInfo->modify_time = date('Y-m-d H:i:s');
        $error_code = ArrayHelper::getValue($saveData, 'error_code','');
        if($error_code == '0000'){
            $oSignInfo->status = isset($saveData['sign_code'])?self::STATUS_BINDOK:self::STATUS_SENDOK;
        } else {
            $oSignInfo->status = isset($saveData['sign_code'])?self::STATUS_BINDFAIL:self::STATUS_SENDFAIL;
        }
        foreach($saveData as $key=>$value){
            $oSignInfo->$key = $value;
        }
        $result = $oSignInfo->update();
        // 校验保存结果
        if (!$result) {
            return $this->returnError(False, implode('|', $oSignInfo->errors));
        }
        return True;
    }
}
