<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "{{%black}}".
 *
 * @property string $id
 * @property string $idcard
 * @property string $phone
 * @property integer $is_bairong
 * @property integer $is_fraudmetrix
 * @property integer $is_yyy
 * @property integer $is_other
 * @property string $create_time
 */
class XsBlack extends \app\models\xs\XsBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%black}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['idcard', 'phone', 'create_time'], 'required'],
            [['is_bairong', 'is_fraudmetrix', 'is_yyy', 'is_other'], 'integer'],
            [['create_time'], 'safe'],
            [['idcard'], 'string', 'max' => 30],
            [['phone'], 'string', 'max' => 20],
            [['idcard', 'phone'], 'unique', 'targetAttribute' => ['idcard', 'phone'], 'message' => 'The combination of 身份证 and 手机号 has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idcard' => '身份证',
            'phone' => '手机号',
            'is_bairong' => '百融黑名单',
            'is_fraudmetrix' => '同盾黑名单',
            'is_yyy' => '一亿元黑名单',
            'is_other' => '其它黑名单',
            'create_time' => '创建时间',
        ];
    }

    public function saveData($data){
		$time = date("Y-m-d H:i:s");
		$postData = [
            'idcard'        => $data['idcard'],
            'phone'         => $data['phone'],
            'is_bairong'    => $data['is_bairong'],
            'is_fraudmetrix'=> $data['is_fraudmetrix'],
            'is_yyy'        => $data['is_yyy'],
            'is_other'      => $data['is_other'],
            'create_time'   => $data['create_time'],
            'create_time'   => $time,
		];

		$error = $this->chkAttributes($postData);
		if ($error) {
			return false;
		}

		return $this->save();
    }
    /**
     * 判断是否是黑名单
     * 
     */
    public function getBlack($phone,$idcard){
        //1. 目前仅从手机号判断
        $where = [
            "phone" => $phone,
        ];            
        $res = static::find() -> where($where) -> limit(1) -> one();
        if(empty($res)){
            return Null;
        }
        //2. 获取黑名单字段
        $data = [
            "is_bairong"        =>  $res->is_bairong, 
            "is_fraudmetrix"    =>  $res->is_fraudmetrix, 
            "is_yyy"            =>  $res->is_yyy, 
            "is_other"          =>  $res->is_other, 
        ];

        //3. 有一个是黑名单算是黑名单用户
        foreach($data as $v){
            if($v==1){
                $data['is_black'] = 1;
                break;
            }
        }
        return $res;
    }
}
