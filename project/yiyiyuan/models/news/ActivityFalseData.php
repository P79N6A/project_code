<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_activity_false_data".
 *
 * @property integer $id
 * @property string $mobile
 * @property integer $integral
 * @property string $content
 * @property integer $status
 * @property integer $version
 * @property string $create_time
 */
class ActivityFalseData extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_activity_false_data';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['integral', 'status', 'version'], 'integer'],
            [['create_time','last_modify_time'], 'safe'],
            [['mobile'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'integral' => 'Integral',
            'status' => 'Status',
            'version' => 'Version',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
        ];
    }
    /**
     * @return array|\yii\db\ActiveRecord[]
     * 获取假数据
     */
    public function getFalseData($limit)
    {
        return self::find()->where(['status'=>2])->orderBy('integral desc')->limit($limit)->asArray()->all();
    }
    /**
     * @return array|\yii\db\ActiveRecord[]
     * 获取真数据
     */
    public function getTrueData()
    {
        return self::find()->where(['status'=>1])->orderBy('integral desc')->limit(17)->asArray()->all();
    }


    /**
     * @param $condition
     * @return array|bool|null
     * 修改
     */
    public function save_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

    /**
     * @param $uid
     * @param $sel
     * @return int
     * 修改
     */
    public function Handlesave($data)
    {
        if(empty($data) || !is_array($data)){
            return false;
        }
        $result = Yii::$app->db->createCommand()->update('yi_activity_false_data',[
            'integral'=>$data['integral'],
            'last_modify_time'=>date('Y-m-d H:i:s'),
        ],['mobile'=>$data['mobile']])->execute();
        return $result;
    }
}
