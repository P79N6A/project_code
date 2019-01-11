<?php

namespace app\models\cloud;

use Yii;

/**
 * This is the model class for table "dc_basic".
 *
 * @property string $id
 * @property string $aid
 * @property string $identity_id
 * @property string $event
 * @property string $idcard
 * @property string $birth
 * @property integer $gender
 * @property string $province
 * @property string $phone
 * @property string $name
 * @property string $ip
 * @property string $device
 * @property string $source
 * @property string $create_time
 */
class DcBasic extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_basic';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'gender'], 'integer'],
            [['identity_id', 'event', 'idcard', 'birth', 'province', 'phone', 'name', 'ip', 'device', 'source', 'create_time'], 'required'],
            [['create_time'], 'safe'],
            [['identity_id'], 'string', 'max' => 50],
            [['event', 'birth', 'phone', 'source'], 'string', 'max' => 20],
            [['idcard', 'province', 'name', 'ip'], 'string', 'max' => 30],
            [['device'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => '业务应用ID',
            'identity_id' => '业务唯一标识',
            'event' => '事件类型:reg; loan; login;',
            'idcard' => '身份证',
            'birth' => '出生年月',
            'gender' => '性别: 0:女; 1:男',
            'province' => '身份证省份',
            'phone' => '手机号',
            'name' => '姓名',
            'ip' => 'IP地址',
            'device' => '设备号',
            'source' => '来源:weixin, android, ios, web',
            'create_time' => '创建时间',
        ];
    }

    // 获取设备申请借款账户数
    public function getAccountByDevice($where) {
        return $this->find()->where($where)->groupBy('identity_id')->orderby('ID DESC')->count();
    }

    // 获取设备申请借款账户数
    public function getPhoneByDevice($where) {
        return $this->find()->where($where)->groupBy('phone')->orderby('ID DESC')->count();
    }

    /**
     * [getMultiLoan 获取申请借款次数]
     * @param  [type] $identity_id [description]
     * @param  [type] $start_time  [description]
     * @return [type]              [description]
     */
    public function  getCreditLoanByPhoneWithtime($phone, $start_time = null){
        $where = [
            'AND', 
            ['phone' => (string)$phone],
        ];
        if (!empty($start_time)) {
           $where[] = ['>','create_time', $start_time];
        }
        $count = static::find() -> where($where) -> count();
        return (int)$count;
    }
    
}
