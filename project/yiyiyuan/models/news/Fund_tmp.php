<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_fund_tmp".
 *
 * @property string $id
 * @property string $loan_id
 * @property integer $status
 * @property string $create_time
 */
class Fund_tmp extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_fund_tmp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'status'], 'integer'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }
}
