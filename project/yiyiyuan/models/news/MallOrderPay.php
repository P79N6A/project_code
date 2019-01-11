<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_mall_order_pay".
 *
 * @property string $id
 * @property string $m_id
 * @property string $req_id
 * @property string $user_id
 * @property integer $source
 * @property integer $status
 * @property string $money
 * @property string $actual_money
 * @property string $paybill
 * @property integer $platform
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class MallOrderPay extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_mall_order_pay';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['m_id', 'req_id', 'user_id', 'money', 'platform'], 'required'],
            [['m_id', 'user_id', 'source', 'status', 'version', 'platform'], 'integer'],
            [['money', 'actual_money'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['req_id', 'paybill'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'm_id' => 'M ID',
            'req_id' => 'Req ID',
            'user_id' => 'User ID',
            'source' => 'Source',
            'status' => 'Status',
            'money' => 'Money',
            'actual_money' => 'Actual Money',
            'paybill' => 'Paybill',
            'platform' => 'Platform',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }


    /**
     * 添加记录
     * @param $condition
     * @return bool
     */
    public function save_repay($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $data['createtime'] = date('Y-m-d H:i:s');
        $data['status'] = 0;
        $data['version'] = 1;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function updateData($data){
        if(empty($data) || !is_array($data)){
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $condition = $data;
        $condition['last_modify_time'] = $now;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return FALSE;
        }
        return $this->save();
    }
}
