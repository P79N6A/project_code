<?php

namespace app\models\mycat;

use app\common\Logger;
use Yii;

/**
 * This is the model class for table "address_list".
 *
 * @property string $id
 * @property integer $aid
 * @property string $user_id
 * @property string $user_phone
 * @property string $phone
 * @property string $name
 * @property string $modify_time
 * @property string $create_time
 */
class AddressList extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'address_list';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_tidb');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'modify_time', 'create_time'], 'required'],
            [['aid', 'user_id'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['user_phone', 'phone'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => 'Aid',
            'user_id' => 'User ID',
            'user_phone' => 'User Phone',
            'phone' => 'Phone',
            'name' => 'Name',
            'modify_time' => 'Modify Time',
            'create_time' => 'Create Time',
        ];
    }
    
    public function getPhoneData($user_phone, $phone_data)
    {
        if (empty($user_phone) || empty($phone_data)){
            return false;
        }
        $where_config = [
            'AND',
            ['=', 'user_phone', $user_phone],
            ['in', 'phone', $phone_data],
        ];
        return self::find()->where($where_config)->all();
//        $whre_config = [
//            'AND',
//            ['=', 'user_phone', '18501940843'],
//            ['in', 'phone', ['13676878332', '13676878332']]
//        ];
        return self::find()->where($whre_config)->all();
    }

    public function getLimitData($phone, $phone_list)
    {
        if (empty($phone_list) || empty($phone)){
            return false;
        }
        $where_config = [
            'AND',
            ['=', 'user_phone', (string)$phone],
            ['in', 'phone', $phone_list]
        ];
        $result = self::find()->where($where_config)->select(['phone'])->all();
        return $result;
    }
} 