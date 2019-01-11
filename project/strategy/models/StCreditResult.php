<?php

namespace app\models;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "st_credit_result".
 *
 * @property string $id
 * @property string $credit_id
 * @property string $mobile
 * @property integer $aid
 * @property string $create_time
 * @property string $res_json
 */
class StCreditResult extends BaseModel
{
    const STATUS_APPROVAL = 1; // 安全
    const STATUS_MANUAL = 2; // 人工
    const STATUS_REJECT = 3; // 驳回
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_credit_result';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['credit_id', 'aid'], 'required'],
            [['credit_id', 'aid', 'come_from'], 'integer'],
            [['create_time'], 'safe'],
            [['res_json'], 'string'],
            [['mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '授信评测结果ID（唯一）',
            'credit_id' => '授信评测ID（唯一）',
            'mobile' => '借款手机号',
            'aid' => '决策请求来源',
            'come_from' => '决策码',
            'create_time' => '创建时间',
            'res_json' => '授信结果数据集',
        ];
    }

    public function saveData($saveData)
    { 
        $nowtime = date('Y-m-d H:i:s');
        $saveData['create_time'] = $nowtime;
        $error = $this->chkAttributes($saveData);
        if ($error) {
            return false;
        }
        return $this->save();
    }

}
