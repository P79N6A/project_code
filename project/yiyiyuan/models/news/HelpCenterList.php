<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_help_center_list".
 *
 * @property integer $id
 * @property string $title
 * @property string $contact
 * @property integer $type
 * @property integer $use_number
 * @property integer $sort
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class HelpCenterList extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_help_center_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'contact', 'create_time'], 'required'],
            [['contact'], 'string'],
            [['type', 'useful_number', 'useless_number','sort', 'status', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['title'], 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'contact' => 'Contact',
            'type' => 'Type',
            'useful_number' => 'Useful Number',
            'useless_number' => 'Useless Number',
            'sort' => 'Sort',
            'status' => 'Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
    
    public function optimisticLock() {
        return "version";
    }
    
    public function getHelpCenterPosition() {
        return $this->hasOne(HelpCenterPosition::className(), ['help_id' => 'id'])->where(['status'=>1]);
    }
    
    public function getHelpcenterlist($condition,$order=''){
       
       if(empty($condition)){
           return NULL;
       }
       $list_data =  HelpCenterList::find()->where(['status'=>1]);
       if(!empty($condition)){
           $list_data = $list_data->andWhere($condition);
       }
       if(!empty($order)){
           $list_data =  $list_data->orderBy($order);
       }
       $list_data =  $list_data->all();
       return $list_data;    
    }
    
    public function getHelpcenterByHelpId($help_id){
        if(empty($help_id)){
            return NULL;
        }
        return self::findone($help_id);
    }
    
    public function update_list_useful_useless($useful_useless){
        if (empty($useful_useless) || !in_array($useful_useless, [1,2])) {
            return false;
        }
        if($useful_useless == 1){
            $data['useful_number'] = $this->useful_number + 1;
            $data['useless_number'] = $this->useless_number - 1;
        }elseif($useful_useless == 2){
            $data['useful_number'] = $this->useful_number - 1;
            $data['useless_number'] = $this->useless_number + 1;
        }
        if($data['useful_number'] < 0){
            $data['useful_number'] = 0;
        }
        if($data['useless_number'] < 0){
            $data['useless_number'] = 0;
        }
        $time = date('Y-m-d H:i:s');
        $data['last_modify_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
