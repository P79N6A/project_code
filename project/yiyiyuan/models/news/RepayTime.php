<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_repay_time".
 *
 * @property string $id
 * @property string $loan_id
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class RepayTime extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_repay_time';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'last_modify_time', 'create_time'], 'required'],
            [['loan_id', 'status', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe']
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
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    /**
     * 批量新增记录
     * @param $condition
     * @return bool
     */
    public function batchAddRecord($data) {
        if (empty($data) || !is_array($data)) {
            return 0;
        }
        $key = ['loan_id','status','last_modify_time','create_time','version'];
        try {
            $num = Yii::$app->db->createCommand()->batchInsert(RepayTime::tableName(), $key, $data)->execute();
        } catch (Exception $e) {
            $num = 0;
        }
        return $num;
    }
}
