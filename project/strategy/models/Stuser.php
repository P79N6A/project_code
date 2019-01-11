<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "st_user".
 *
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property string $identity
 * @property integer $is_black
 * @property string $mobile
 * @property integer $bph_fm_sx
 * @property integer $bph_y
 * @property integer $bph_other
 * @property integer $bph_fm_small
 * @property integer $bph_fm_fack
 * @property integer $bph_br
 * @property integer $bid_fm_sx
 * @property integer $bid_fm_court_sx
 * @property integer $bid_fm_court_enforce
 * @property integer $bid_fm_lost
 * @property integer $bid_y
 * @property integer $bid_other
 * @property integer $bid_br
 * @property string $reg_time
 * @property integer $event_number_value
 * @property integer $score
 * @property string $create_time
 * @property string $modify_time
 */
class StUser extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_user';
    }

    /**
     * @inheritdoc
     */
    public function rules() 
    { 
        return [
            [['request_id', 'user_id', 'create_time', 'modify_time'], 'required'],
            [['request_id', 'user_id', 'is_black', 'bph_fm_sx', 'bph_y', 'bph_other', 'bph_fm_small', 'bph_fm_fack', 'bph_br', 'bid_fm_sx', 'bid_fm_court_sx', 'bid_fm_court_enforce', 'bid_fm_lost', 'bid_y', 'bid_other', 'bid_br', 'number_value', 'prd_type'], 'integer'],
            [['reg_time', 'query_time', 'create_time', 'modify_time'], 'safe'],
            [['realname', 'identity', 'mobile'], 'string', 'max' => 20]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => '主键',
            'request_id' => '决策请求ID',
            'user_id' => '业务端用户ID',
            'realname' => '用户真实姓名',
            'identity' => '用户身份证号',
            'is_black' => '黑名单触发情况',
            'mobile' => '用户手机号',
            'bph_fm_sx' => '手机号命中同盾失信证据库',
            'bph_y' => '手机号命中先花黑名单库',
            'bph_other' => '手机号命中三方黑名单库',
            'bph_fm_small' => '手机号命中同盾小号库',
            'bph_fm_fack' => '手机号命中同盾虚假号码库',
            'bph_br' => '手机号命中百融黑名单库',
            'bid_fm_sx' => '身份证号命中同盾失信证据库',
            'bid_fm_court_sx' => '身份证号命中同盾法院失信证据库',
            'bid_fm_court_enforce' => '身份证号命中同盾法院执行证据库',
            'bid_fm_lost' => '身份证号命中同盾失联证据库',
            'bid_y' => '身份证号命中先花黑名单库',
            'bid_other' => '身份证号命中三方黑名单库',
            'bid_br' => '身份证号命中百融黑名单库',
            'reg_time' => '用户注册时间',
            'query_time' => '请求时间',
            'number_value' => '设备注册个数',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
            'prd_type' => '产品类型，1 一亿元；8 7-14天',
        ]; 
    } 

    public function addUserInfo($postData)
    {
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $postData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateUserInfo($postData)
    {
        foreach ($postData as $k => $val) {
            $this->$k = $val;
        }
        $this->modify_time = date('Y-m-d H:i:s');
        return $this->save();
    }
}
