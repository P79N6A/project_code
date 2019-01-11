<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_contacts_flows".
 *
 * @property string $id
 * @property string $user_id
 * @property string $contacts_name
 * @property integer $relation_common
 * @property string $mobile
 * @property string $relatives_name
 * @property integer $relation_family
 * @property string $phone
 * @property string $create_time
 */
class Contacts_flows extends BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_contacts_flows';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'contacts_name', 'mobile', 'relatives_name', 'phone', 'create_time'], 'required'],
            [['user_id', 'relation_common', 'relation_family'], 'integer'],
            [['create_time'], 'safe'],
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
            'create_time' => 'Create Time',
        ];
    }

    /*
     * 添加联系人修改记录
     */
    public function addContactsFlows($condition){
        if(empty($condition)){
            return false;
        }
        foreach ($condition as $key=>$val){
            $this->{$key}=$val;
        }
        $nowtime = date('Y-m-d H:i:s');
        $this->create_time = $nowtime;
        $result = $this->save();
        if($result){
            return Yii::$app->db->getLastInsertID();
        }else{
            return false;
        }
    }

    /**
     * 添加联系人更改记录
     * @param $condition
     * @return array|bool|null
     */
    public function save_contactsFlows($condition)
    {
        if( !is_array($condition) || empty($condition) ){
            return false;
        }
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
