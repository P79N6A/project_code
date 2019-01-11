<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_card_limit".
 *
 * @property string $id
 * @property string $bank_name
 * @property string $card_type
 * @property integer $status
 * @property string $start_time
 * @property string $end_time
 * @property integer $type
 * @property string $operation
 * @property string $create_time
 */
class CardLimit extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_card_limit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['card_type', 'status', 'type'], 'integer'],
            [['start_time', 'end_time', 'create_time'], 'safe'],
            [['bank_name', 'operation'], 'string', 'max' => 60]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_name' => 'Bank Name',
            'card_type' => 'Card Type',
            'status' => 'Status',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
            'type' => 'Type',
            'operation' => 'Operation',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 修改限制卡信息
     * @author zhangchao <[email address]>
     */
    public function updateCardLimit($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data  = $condition;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
            return $result;
        } catch (Exception $ex) {
            return FALSE;
        }
    }
}