<?php

namespace app\models\own;

use Yii;
use app\models\own\OwnBaseModel;
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
class StatisticsType extends OwnBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_statistics_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }
    
    public function createData($data){
        $model = new self();
        $model -> come_from = $data['come_from'];
        $model -> title = $data['title'];
        $model -> status = $data['status'];
        $model -> create_time = $data['create_time'];
        return $model -> save();
    }

  
}
