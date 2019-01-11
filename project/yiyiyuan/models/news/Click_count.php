<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_click_count".
 *
 * @property string $id
 * @property string $field
 * @property integer $count
 * @property string $create_time
 */
class Click_count extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_click_count';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['count'], 'integer'],
            [['create_time'], 'safe'],
            [['field'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'field' => 'Field',
            'count' => 'Count',
            'create_time' => 'Create Time',
        ];
    }
    
    /**
     * app接口点击统计方法（+1）
     * @param type $condition
     * @return boolean
     */
    public function save_clickcount($condition){
        if(!is_array($condition) || empty($condition)){
            return false;
        }
        $time_info = date("Y-m-d 00:00:00");
        $clickcount_info = self::find()->where(['>=', 'create_time', $time_info])->andWhere(['field' => $condition['field']])->one();
       
        if (!empty($clickcount_info)) {
            $clickcount_info->count  += 1;
            return $clickcount_info->save();
        } else {
            $data = $condition;
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['count'] = 1;
            $error = $this->chkAttributes($data);
            if($error){
                return false;
            }
            $model = new Click_count();
            $model->attributes = $data;
            return $model->save();
        }
    }
}
