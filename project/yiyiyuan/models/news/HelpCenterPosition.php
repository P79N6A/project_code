<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
/**
 * This is the model class for table "yi_help_center_position".
 *
 * @property integer $id
 * @property integer $help_id
 * @property integer $position
 * @property integer $sort
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class HelpCenterPosition extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_help_center_position';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['help_id', 'create_time'], 'required'],
            [['help_id', 'position', 'sort', 'status', 'version'], 'integer'],
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
            'help_id' => 'Help ID',
            'position' => 'Position',
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
    public function getHelpCenterList() {
        return $this->hasOne(HelpCenterList::className(), ['id' => 'help_id'])->where(['status'=>1]);
    }
    
    public function getHelpcenterposition($condition,$order=''){
       
       if(empty($condition)){
           return NULL;
       }
       $position_data =  HelpCenterPosition::find()->where(['status' => 1]);
       if(!empty($condition)){
           $position_data = $position_data->andWhere($condition);
       }
       if(!empty($order)){
           $position_data =  $position_data->orderBy($order);
       }
       $position_data =  $position_data->all();
       return $position_data;    
    }
}
