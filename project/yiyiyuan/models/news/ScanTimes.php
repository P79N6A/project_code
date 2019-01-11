<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_scan_times".
 *
 * @property integer $id
 * @property string $mobile
 * @property integer $type
 * @property string $create_time
 */
class ScanTimes extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_scan_times';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile', 'create_time'], 'required'],
            [['type'], 'integer'],
            [['create_time'], 'safe'],
            [['mobile'], 'string', 'max' => 20]
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
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 存储短息
     * @param $condition
     * @return bool
     */
    public function save_scan($condition) {
        if( !is_array($condition) || empty($condition) ){
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }

    public function getByMobileType($mobile,$type){
        if(!$mobile || !$type){
            return null;
        }
        return self::find()->where(['mobile' => $mobile,'type' => $type])->one();
    }

    /**
     * 判断弹层的显示次数
     * @param type $mark 0：全部时间浏览次数 1：当天浏览次数
     */
    public function getScanCount($mobile, $type, $mark = 0) {
        if ($mark == 0) {
            $scan_count = self::find()->where(['mobile' => $mobile, 'type' => $type])->count();
        } else {
            $scan_count = self::find()->where(['mobile' => $mobile, 'type' => $type])->andFilterWhere(['>=', 'create_time', date('Y-m-d 00:00:00')])->count();
        }
        if ($scan_count == 0) {
            $scan = new self();
            $scan->mobile = $mobile;
            $scan->type = $type;
            $scan->create_time = date('Y-m-d H:i:s');
            $scan->save();
        }
        return $scan_count;
    }

}
