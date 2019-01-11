<?php

namespace app\models\rongbao;

use app\models\BindBank;

/**
 * This is the model class for table "pay_rongbao_bindbank".
 *
 * @property integer $id
 * @property integer $aid
 * @property integer $channel_id
 * @property string $bind_id
 * @property string $identityid
 * @property string $cardno
 * @property string $bankname
 * @property string $bankcode
 * @property string $idcardtype
 * @property string $idcard
 * @property string $name
 * @property string $phone
 * @property string $userip
 * @property string $create_time
 * @property string $modify_time
 * @property string $error_code
 * @property string $error_msg
 * @property integer $status
 */
class RongbaoBindbank extends \app\models\BasePay {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'pay_rongbao_bindbank';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['aid', 'channel_id', 'identityid', 'cardno', 'bankname', 'idcard', 'name', 'phone', 'userip', 'create_time', 'modify_time', 'cli_orderid', 'orderid', 'card_type'], 'required'],
            [['aid', 'channel_id', 'status', 'card_type','bind_id'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['bind_no', 'error_msg'], 'string', 'max' => 100],
            [['identityid', 'bankcode', 'idcard', 'name', 'phone', 'userip', 'error_code'], 'string', 'max' => 20],
            [['cardno', 'bankname'], 'string', 'max' => 50],
            [['idcardtype'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id'          => 'ID',
            'aid'         => 'Aid',
            'channel_id'  => 'Channel ID',
            'bind_id'     => 'Bind ID',
            'bind_no'     => 'Bind NO',
            'identityid'  => 'Identityid',
            'cardno'      => 'Cardno',
            'bankname'    => 'Bankname',
            'bankcode'    => 'Bankcode',
            'idcardtype'  => 'Idcardtype',
            'idcard'      => 'Idcard',
            'name'        => 'Name',
            'phone'       => 'Phone',
            'userip'      => 'Userip',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
            'error_code'  => 'Error Code',
            'error_msg'   => 'Error Msg',
            'status'      => 'Status',
            'cli_orderid' => 'cli_orderid',
            'orderid'     => 'orderid',
            'card_type'   => 'card_type',
        ];
    }

    /**
     * 保存数据
     */
    public function saveBindBank($postData) {
        //1 数据验证
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(null, "数据不能为空");
        }
        //2  保存数据
        $time = date("Y-m-d H:i:s");
        $data = [
            'aid'         => $postData['aid'],
            'channel_id'  => $postData['channel_id'],
            'identityid'  => $postData['identityid'],
            'cardno'      => $postData['cardno'],
            'card_type'   => $postData['card_type'],
            'bankname'    => $postData['bankname'],
            'idcard'      => $postData['idcard'],
            'name'        => $postData['name'],
            'phone'       => $postData['phone'],
            'userip'      => $postData['userip'],
            'card_type'   => $postData['card_type'],
            'cli_orderid' => $postData['cli_orderid'],
            'orderid'     => $postData['orderid'],
            'bind_id'     => 0,
            'create_time' => $time,
            'modify_time' => $time,
            'bankcode'    => '',
            'error_code'  => '',
            'error_msg'   => '',
        ];

        //4  字段检测
        if ($errors = $this->chkAttributes($data)) {
            return $this->returnError(null, implode('|', $errors));
        }

        //5  保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, implode('|', $this->errors));
        }
        return true;
    }
    
    /**
     * 判断某帐号是否成功绑定过此卡
     */
    public function getBindBankInfo($aid, $identityid, $cardno, $channel_id) {
        $oBind = static::find()->where([
            'channel_id' => $channel_id,
           // 'aid' => $aid,
            'identityid' => $identityid,
            'cardno' => $cardno,
            'status' => 1
        ])->limit(1) -> one();

        return $oBind;
    }

    /*
     * 绑卡成功修改绑定ID
     */

    public function updateBindno($bindno) {
        //1 数据验证
        if (empty($bindno)) {
            return $this->returnError(null, "数据不能为空");
        }
        //2  保存数据
        $data['modify_time'] = date("Y-m-d H:i:s");
        $data['bind_no']     = $bindno;
        //4  字段检测
        if ($errors              = $this->chkAttributes($data)) {
            return $this->returnError(null, implode('|', $errors));
        }

        //5  保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, implode('|', $this->errors));
        }
        return true;
    }

    public function findCardByConditions($conditions, $one = true) {
        $where = [];
        if (!empty($conditions)) {
            $where = $conditions;
        }
        if ($one) {
            return $data = self::find()->where($where)->one();
        } else {
            return $data = self::find()->where($where)->all();
        }
    }
    
    public function bindBankSuccess($oRongOrder){
        //修改融宝绑卡表状态
        $this -> status = BindBank::STATUS_BINDOK;
        if(!$this -> save()){
            return false;
        }
        //支付成功后 将bind_id更新到子订单表中
        $oRongOrder ->bind_id = $this -> id;
        $saveOrderBindId = $oRongOrder -> save();
        if(!$saveOrderBindId){
            return false;
        }
        //在bindbank表中生成新的绑卡成功记录
        $postData = $this-> attributes;
        $postData['status'] = BindBank::STATUS_BINDOK;  //成功
        $saveBindBank = (new BindBank)->saveOrder($postData);
        if (!$saveBindBank) {
            return false;
        }
        return true;
    }

}
