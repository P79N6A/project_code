<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Card_bin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_card_bin';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            
        ];
    }
    
    public function getCardBinByCard($card){
        $sql = "SELECT * FROM " . Card_bin::tableName() . " WHERE card_length = " . strlen($card) . " AND prefix_value=left('" . $card . "',prefix_length) order by prefix_length desc";
        $cardbin = Yii::$app->db->createCommand($sql)->queryOne();
        return $cardbin;
    }
}
