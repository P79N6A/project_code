<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_behavior_record".
 *
 * @property integer $id
 * @property string $user_id
 * @property string $loan_id
 * @property integer $type
 * @property string $create_time
 */
class BehaviorRecord extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_behavior_record';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'loan_id'], 'required'],
            [['user_id', 'loan_id', 'type'], 'integer'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'loan_id' => 'Loan ID',
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * @param $condition
     * @return bool
     */
    public function addList($condition) {
        foreach ($condition as $key => $val) {
            $this->{$key} = $val;
        }
        $this->create_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    /**
     * 获取记录，根据loan_id和type
     * @param $loan_id
     * @param $type 1借款驳回 2借款驳回（有导流） 7评测驳回（有导流）
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/8/1 17:33
     */
    public function getByLoanId($loan_id, $type) {
        if (empty($loan_id) || empty($type)) {
            return null;
        }
        $where = [
            'loan_id' => $loan_id,
            'type' => $type
        ];
        return self::find()->where($where)->one();
    }
}