<?php

namespace app\models;

/**
 * This is the model class for table "zf_limit".
 *
 * @property string $id
 * @property string $dayTopMoney
 * @property string $totalMoney
 * @property string $date_config
 * @property string $source
 * @property string $start_time
 * @property string $end_time
 */
class ZFLimit extends \app\models\BaseModel {
    const WSM_SOURCE = 6; //微神马
    const XN_SOURCE = 5; //小诺

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zf_limit';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['dayTopMoney', 'totalMoney', 'source'], 'integer'],
            [['date_config'], 'string'],
            [['start_time', 'end_time'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'dayTopMoney' => 'Day Top Money',
            'totalMoney' => 'Total Money',
            'date_config' => 'Date Config',
            'source' => 'Source',
            'start_time' => 'Start Time',
            'end_time' => 'End Time',
        ];
    }

    /**
     * 
     * 获取限额
     *
     * @return []
     */
    public function getTopLimit($source) {
        if(!$source){
            return false;
        }
        $data = self::find()->where(['source'=>$source])->limit(1)->one();
        return $data;
    }
  
    public function getLimitInfo()
    {
        $info = self::find()->where(['source'=>ZFLimit::WSM_SOURCE])->orderBy("id desc")->one();
        if (empty($info)){
            return false;
        }
        return $info;
    }
}
