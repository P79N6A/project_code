<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;
/**
 * This is the model class for table "dc_split_value".
 *
 * @property string $id
 * @property string $first_split
 * @property string $reloan_split
 * @property string $create_time
 */
class XsSplitValue extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_split_value';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['first_split', 'reloan_split', 'create_time'], 'required'],
            [['first_split', 'reloan_split'], 'string'],
            [['create_time'], 'safe']
        ];
    } 

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'first_split' => '初贷分位值',
            'reloan_split' => '复贷分位值',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 记录分位值
     */
    public function saveData($postData)
    {
        $postData['create_time'] = date("Y-m-d H:i:s"); 
        $error = $this->chkAttributes($postData); 
        if ($error) { 
            Logger::dayLog("split/saveData","save failed", $postData, $error);
            return false; 
        }
        return $this->save(); 
    }

    /**
     * get分位值
     */
    public function getOne()
    {
        return $this->find()->orderBy('id DESC')->asArray()->limit(1)->one();
    }
}
