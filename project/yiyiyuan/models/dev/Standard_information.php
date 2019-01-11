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
class Standard_information extends \yii\db\ActiveRecord {

    public $user_interest;

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_standard_information';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    public function getProgress() {
        return $this->hasOne(Standard_progress::className(), ['standard_id' => 'id']);
    }

    public function getStatistics() {
        return $this->hasOne(Standard_statistics::className(), ['standard_id' => 'id']);
    }

    /**
     * 查询上架中的标的
     */
    public function getStandardList() {
        $nowtime = date('Y-m-d H:ii:s');
        $standard_list = Standard_information::find()->joinWith('progress', true, 'LEFT JOIN')->where(Standard_information::tableName() . ".status = 'AUDITED' or " . Standard_information::tableName() . ".status = 'SUCCEED'")->andWhere(Standard_information::tableName() . ".online_date <= '$nowtime'")->andWhere(Standard_information::tableName() . ".open_enddate > '$nowtime'")->orderBy(Standard_information::tableName() . '.online_date' . ' asc , ' . Standard_information::tableName() . '.id' . ' asc')->all();
        return $standard_list;
    }

    public function getDetial($standard_id) {
        if (empty($standard_id)) {
            return null;
        }
        //标的详情
        $standard_information = Standard_information::find()->joinWith('progress', true, 'LEFT JOIN')->where([Standard_information::tableName() . '.id' => $standard_id])->one();
        return $standard_information;
    }

    public function updateStandardInformation($condition, $id) {
        $now_time = date('Y-m-d H:i:s');
        $standard_information = Standard_information::findOne($id);
        
        foreach ($condition as $key=>$val){
            $standard_information->{$key}=$val;
        }
        $standard_information->last_modify_time = $now_time;
        $standard_information->version += 1;
        $result = $standard_information->save();
        return $result;
    }

}
