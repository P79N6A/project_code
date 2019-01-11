<?php

namespace app\models;

use Yii;

/**
 * 支付通道绑卡表
 *
 */
class BindBank extends \app\models\BaseModel
{
    const STATUS_BINDINIT = 0; // 初始化
    const STATUS_BINDOK = 1;  // 绑定成功
    const STATUS_BINDFAIL = 2; // 绑定失败
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_bindbank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'identityid', 'idcardno', 'user_name', 'cardno', 'card_type', 'bank_mobile', 'bank_name', 'create_time', 'modify_time'], 'required'],
            [['aid', 'pay_type', 'card_type', 'status'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['identityid', 'cardno', 'bank_name', 'validate', 'cvv2'], 'string', 'max' => 50],
            [['idcardno', 'user_name', 'bank_mobile', 'bank_code'], 'string', 'max' => 20],
            [['userip'], 'string', 'max' => 30],
            [['smscode'], 'string', 'max' => 10],
            [['bind_no'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bind_no' => '支付公司绑卡id',
            'pay_type' => '通道',
            'aid' => '应用ID',
            'identityid' => '商户生成的用户唯一标识',
            'idcardno' => '身份证号',
            'user_name' => '姓名',
            'cardno' => '银行卡号',
            'card_type' => '银行卡类型',
            'bank_mobile' => '银行卡绑定手机号',
            'bank_name' => '银行名称',
            'bank_code' => '银行编码',
            'validate' => '有效期',
            'cvv2' => 'cvv2编码',
            'userip' => '用户IP',
            'create_time' => '生成时间',
            'modify_time' => '修改时间',
            'smscode' => '短信验证码',
            'status' => '绑卡状态',
        ];
    }

    /**
     * 判断某帐号是否成功绑定过此卡
     */
    public function getBindBankInfo($aid, $identityid, $cardno, $pay_type) {
        $oBind = static::find()->where([
            'pay_type' => $pay_type,
            'aid' => $aid,
            'identityid' => $identityid,
            'cardno' => $cardno,
        ])->limit(1) -> one();

        return $oBind;
    }

    /**
     * 保存银行卡信息
     */
    public function saveOrder($postData){
        //1 数据验证
        if( !is_array($postData) || empty($postData) ){
            return $this->returnError(false,"数据不能为空");
        }
        if( empty($postData['aid']) ){
            return $this->returnError(false,"应用id不能为空");
        }
        if( empty($postData['identityid']) ){
            return $this->returnError(false,"用户唯一标识不能为空");
        }
        if( empty($postData['idcardno']) ){
            return $this->returnError(false,"身份证号不能为空");
        }
        if( empty($postData['user_name']) ){
            return $this->returnError(false,"姓名不能为空");
        }
        if( empty($postData['cardno']) ){
            return $this->returnError(false,"银行卡号不能为空");
        }
        if( empty($postData['card_type']) ){
            return $this->returnError(false,"银行卡类型不能为空");
        }
        if( empty($postData['bank_mobile']) ){
            return $this->returnError(false,"手机号码不能为空");
        }
        if( empty($postData['pay_type']) ){
            return $this->returnError(false,"出款通道不能为空");
        }
        
        // 初始化参数
        $postData['bind_no'] = isset($postData['bind_no'] ) ? $postData['bind_no'] : '';
        $postData['create_time'] = $postData['modify_time'] = date('Y-m-d H:i:s');
        $postData['validate'] = isset($postData['validate']) ? $postData['validate'] : '';
        $postData['cvv2'] = isset($postData['cvv2']) ? $postData['cvv2'] : '';
        $postData['userip'] = isset($postData['userip']) ? $postData['userip'] : '';
        $postData['smscode'] = isset($postData['smscode']) ? $postData['smscode'] : '';
        $postData['bank_code'] = isset($postData['bank_code']) ? $postData['bank_code'] : '';
        $postData['userip'] = isset($postData['userip']) ? $postData['userip'] : '';
		
        $postData['status'] = isset($postData['status']) ? intval($postData['status']) : 0;
		
        // 参数检证是否有错
        if ($errors = $this->chkAttributes($postData)) {
            return $this->returnError(false, implode('|', $errors));
        }
         
        $result = $this->save();
        if (!$result) {
            return $this->returnError(false, implode('|', $this->errors));
        }
        return true;
    }
    /**
     * 设置为绑定
     * @param str $bind_no 支付方绑定ID号
     */
    public function setBind($bind_no){
        $this->bind_no = (string)$bind_no;
        $this->status = static::STATUS_BINDOK;
        $this->modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    public function getByBid($id){
        $id = intval($id);
        if( !$id ){
            return null;
        }
        return static::find() -> where(['id'=>$id]) -> limit(1) -> one();
    }
}
