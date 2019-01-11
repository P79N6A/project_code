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
class School extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_school';
    }
    /**
     * 根据关键字查询学校列表
     * @param type $key_word
     * @param array $column 需要查询的字段
     */
    public function getSchoolByKeyword($key_word='',$column=array()){        
        $school = School::find();
        if(!empty($column)){
            $select = implode(',', $column);
            $school->select($select);
        }
        if($key_word!=''){
            $school->filterWhere(['like','school_name',$key_word]);
        }
        $school_list = $school->all();
        return $school_list;
    }  
  
}
