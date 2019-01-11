<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%bank_valid}}".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $cardno
 * @property string $idcard
 * @property string $username
 * @property string $phone
 * @property string $create_time
 */
class BankValid extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_valid}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'cardno', 'idcard', 'username', 'phone', 'create_time'], 'required'],
            [['aid'], 'integer'],
            [['create_time'], 'safe'],
            [['cardno'], 'string', 'max' => 50],
            [['idcard', 'username', 'phone'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'aid' => '应用id',
            'cardno' => '银行卡号',
            'idcard' => '身份证号',
            'username' => '姓名',
            'phone' => '银行留存电话',
            'create_time' => '创建时间',
        ];
    }
/**
	 * 保存数据
	 */
	public function saveData($postData){
		// 检测数据
		if(!$postData){
			return $this->returnError(false, '不能为空');
		}
		$data = [
			'aid'	 		=> $postData['aid'],
			'cardno'	 => $postData['cardno'],
			'idcard'		 => $postData['idcard'],
			'username'=> $postData['username'],
			'phone'     => $postData['phone'],
            'create_time' => date('Y-m-d H:i:s'),
        ];
		
		$error = $this->chkAttributes($data);
		if($error){
			return $this->returnError(false, $error);
		}

		return $this->save();
	}
	/**
	 * 根据银行卡号查询数据
	 * @param $cardno 银行卡号
	 * @return object | null
	 */
	public function getByCard($cardno){
		if(!$cardno){
			return false;
		}
		return static::find() -> where(['cardno' => $cardno]) -> one();
	}
	/**
	 * 查询四要素是否正确
	 * @param [] $data 四要素
	 * @return bool
	 */
	public function chk($data){
		return 	    $this->cardno	 == $data['cardno'] &&
						$this->idcard		 == $data['idcard'] &&
						$this->username== $data['username'] &&
						$this->phone     == $data['phone'];
	}
	/**
	 * 查询四要素是否正确
	 * @param [] $data 四要素
	 * @return bool
	 */
	public function support($cardno){
		//1 查询存在卡bin表中
		$card = CardBin::getCardBin($cardno);
		if(!$card){
			return $this->returnError(false, "不支持该银行卡");
		}
		
		//2 转换成0->1:储蓄卡; 1->2:信用卡
		if($card['card_type'] == 1){// 信用卡
			$card_type = 2; 
		}else if($card['card_type'] == 0){// 储蓄卡
			$card_type = 1;
		}else{
			return $this->returnError(false, "不支持该银行卡");
		}
		
		//3 获取银行编码
		$o = new Bank4Support;
		$isSupport = $o -> support($card_type,  $card['bank_abbr'], $card['bank_name']);
		return $isSupport;
	}
	
}
