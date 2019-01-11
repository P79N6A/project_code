<?php

namespace app\models\news;
use app\models\news\Activitynew;

use Yii;

/**
 * This is the model class for table "yi_prize".
 *
 * @property string $id
 * @property integer $activity_id
 * @property string $title
 * @property string $denomination
 * @property integer $integral
 * @property integer $type
 * @property integer $coupon_type
 * @property string $val
 * @property string $proportion
 * @property string $probability
 * @property string $prize_pic
 * @property integer $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Prize extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_prize';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['activity_id', 'integral', 'type', 'coupon_type', 'status', 'version'], 'integer'],
            [['title', 'last_modify_time', 'create_time'], 'required'],
            [['denomination', 'val', 'proportion', 'probability'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['title', 'prize_pic'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'activity_id' => 'Activity ID',
            'title' => 'Title',
            'denomination' => 'Denomination',
            'integral' => 'Integral',
            'type' => 'Type',
            'coupon_type' => 'Coupon Type',
            'val' => 'Val',
            'proportion' => 'Proportion',
            'probability' => 'Probability',
            'prize_pic' => 'Prize Pic',
            'status' => 'Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
    /**
     * 乐观所版本号
     * @return string
     */
    public function optimisticLock()
    {
        return "version";
    }

    public function getActivitynew() {
        return $this->hasOne(Activitynew::className(), ['id' => 'activity_id']);
    }

    public function getPrizelist(){
        return $this->hasMany(PrizeList::className(),['id' => 'prize_id']);
    }

    /**
     * 获取上架状态的奖品
     * @param $purpose
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function listPrizeByPurpose($purpose)
    {
        if (empty($purpose) || !is_numeric($purpose)) {
            return null;
        }
        return self::find()->where(['purpose' => $purpose, 'status' => 1])->all();
    }

    /**
     * 获取指定id奖品
     * @param $id
     * @return null|static
     */
    public function getPrizeById($id)
    {
        if (empty($id) || !is_numeric($id)) {
            return null;
        }
        return self::findOne($id);
    }

    public function listPrizeByPurposeAid($purpose,$aid = 1)
    {
        if (empty($purpose) || !is_numeric($purpose)) {
            return null;
        }
        return self::find()->where(['purpose' => $purpose,'status' => 1, 'aid'=>$aid])->all();
    }

    public function changeNum(){
        $this->num -= 1;
        $this->send_num += 1;
        return $this->save();
    }

    public function save_address($condition) {
        if(!$condition || !is_array($condition)){
            return false;
        }
        $condition['last_modify_time'] = date('Y-m-d H:i:s');
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return $error;
        }
        return $this->save();
    }

}
