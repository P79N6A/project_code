<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_withhold_tmp".
 *
 * @property integer $id
 * @property string $loan_id
 */
class WithholdTmp extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_withhold_tmp';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id','status'], 'integer']
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
        ];
    }
}
