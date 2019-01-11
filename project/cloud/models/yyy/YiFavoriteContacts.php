<?php

namespace app\models\yyy;

use Yii;

/**
 * This is the model class for table "yi_favorite_contacts".
 *
 * @property string $id
 * @property string $user_id
 * @property string $contacts_name
 * @property integer $relation_common
 * @property string $mobile
 * @property string $relatives_name
 * @property integer $relation_family
 * @property string $phone
 * @property string $last_modify_time
 * @property string $create_time
 */
class YiFavoriteContacts extends YyyBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_favorite_contacts';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'contacts_name', 'mobile', 'relatives_name', 'phone', 'last_modify_time'], 'required'],
            [['user_id', 'relation_common', 'relation_family'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['contacts_name', 'mobile', 'relatives_name', 'phone'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'contacts_name' => '常用联系人姓名',
            'relation_common' => '1朋友;2同事;3兄弟;4姐妹',
            'mobile' => '常用联系人电话',
            'relatives_name' => '亲属联系人姓名',
            'relation_family' => '1父母;2配偶',
            'phone' => '亲属联系人电话',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
        ];
    }

    public function getFavorite($user_id)
    {
        return $this->find()->where(['user_id' => $user_id])->limit(1)->orderby('ID DESC')->one();
    }
}
