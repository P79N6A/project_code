<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

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
class Favorite_contacts extends BaseModel
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
            'contacts_name' => 'Contacts Name',
            'relation_common' => 'Relation Common',
            'mobile' => 'Mobile',
            'relatives_name' => 'Relatives Name',
            'relation_family' => 'Relation Family',
            'phone' => 'Phone',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function getFavorite()
    {
    	return $this->hasOne(User::className(), ['user_id' => 'user_id']);
    }

    /*
     * 添加联系人信息
     */
    public function addFavoriteContacts($condition){
        if(empty($condition)){
            return false;
        }
        foreach ($condition as $key=>$val){
            $this->{$key}=$val;
        }
        $nowtime = date('Y-m-d H:i:s');
        $this->last_modify_time = $nowtime;
        $this->create_time = $nowtime;
        $result = $this->save();
        if($result){
            return Yii::$app->db->getLastInsertID();
        }
        return false;
    }

    /*
     * 修改联系人信息
     */
    public function updateFavoriteContacts($condition){
        if(empty($condition)){
            return false;
        }
        foreach ($condition as $key=>$val){
            $this->{$key} = $val;
        }
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }
    /**
     * 查找联系人信息
     */
    public function getFavoriteByUserId($user_id){
        return Favorite_contacts::find()->where(['user_id'=>$user_id])->one();
    }

    /**
     * 添加联系人信息
     * @param $condition
     * @return array|bool|null|string
     */
    public function save_favoriteContacts($condition)
    {
        if( !is_array($condition) || empty($condition) ){
            return false;
        }
        $nowtime = date('Y-m-d H:i:s');
        $condition['last_modify_time'] = $nowtime;
        $condition['create_time'] = $nowtime;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 修改联系人信息
     * @param $condition
     * @return bool
     */
    public function update_favoriteContacts($condition)
    {
        if( !is_array($condition) || empty($condition) ){
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }
}
