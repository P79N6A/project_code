<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_insurance".
 *
 * @property string $id
 * @property string $req_id
 * @property string $loan_id
 * @property integer $type
 * @property integer $days
 * @property string $user_id
 * @property integer $status
 * @property string $money
 * @property integer $is_chk
 * @property string $insurance_order
 * @property string $result_time
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class YiInsurance extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_insurance';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['req_id', 'loan_id', 'user_id', 'is_chk', 'last_modify_time', 'create_time'], 'required'],
            [['loan_id', 'type', 'days', 'user_id', 'status', 'is_chk', 'version'], 'integer'],
            [['money'], 'number'],
            [['result_time', 'last_modify_time', 'create_time'], 'safe'],
            [['req_id', 'insurance_order'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'req_id' => '请求序号',
            'loan_id' => '借款ID',
            'type' => '购买类型 1:借款购买 2：主动购买',
            'days' => '保险天数 主动购买填写',
            'user_id' => '用户ID',
            'status' => '状态 0初始 1成功 2失败',
            'money' => '保险费',
            'is_chk' => '勾选 0初始 1勾选 2不勾选',
            'insurance_order' => '保单号',
            'result_time' => '投保时间',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
            'version' => 'Version',
        ];
    }

    public function getYisureData($where)
    {
        return $this->find()->where($where)->orderby('ID DESC')->one();
    }
}
