<?php

namespace app\models\tidb;

use Yii;
use yii\helpers\ArrayHelper;

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
class TiAddressList extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'address_list';
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
            'aid' => '业务id',
            'user_id' => '业务用户ID',
            'user_phone' => '用户本机手机号',
            'phone' => '通讯录手机号',
            'name' => '通讯录姓名',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getAddressByUserPhone($user_phone){
        if (empty($user_phone)) {
            return [];
        }
        $res = $this->find()->where(['user_phone' => $user_phone])->limit(10000)->asArray()->all();
        $address_list = ArrayHelper::getColumn($res,'phone',[]);
        return $address_list;
    }
}
