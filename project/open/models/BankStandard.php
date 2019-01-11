<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%bank_standard}}".
 *
 * @property integer $id
 * @property string $bankname
 * @property string $alias
 * @property string $bankcode
 * @property integer $forbidden
 * @property string $create_time
 */
class BankStandard extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%bank_standard}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['bankname', 'alias', 'bankcode', 'create_time'], 'required'],
            [['forbidden'], 'integer'],
            [['create_time'], 'safe'],
            [['bankname', 'alias'], 'string', 'max' => 30],
            [['bankcode'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bankname' => '标准化名称',
            'alias' => '别名:银行卡或编码',
            'bankcode' => '银行编码(暂时无用)',
            'forbidden' => '禁用状态:0:启用 1禁用',
            'create_time' => '创建时间',
        ];
    }
    /**
     * 获取标准化银行名称
     * @param  string $alias 银行名称
     * @return str
     */
    public function getStdBankName($alias){
        if(!$alias){
            return '';
        }
        $row = static::find() -> where(['alias'=>$alias]) -> limit(1) -> one();
        return is_object($row) && isset($row['bankname']) && $row['bankname']  ?  $row['bankname']  : '';
    }
}
