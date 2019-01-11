<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_app_version".
 *
 * @property string $id
 * @property string $display_ver
 * @property string $internal_ver
 * @property string $forced_upgrade
 * @property string $download_url
 * @property string $app_url
 * @property string $description
 * @property string $create_time
 * @property integer $version
 */
class App extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_app_version';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['forced_upgrade'], 'string'],
            [['create_time'], 'safe'],
            [['display_ver', 'internal_ver'], 'string', 'max' => 20],
            [['download_url', 'app_url'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'display_ver' => 'Display Ver',
            'internal_ver' => 'Internal Ver',
            'forced_upgrade' => 'Forced Upgrade',
            'download_url' => 'Download Url',
            'app_url' => 'App Url',
            'description' => 'Description',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }
    
    public function addAppversion($condition){
        if(empty($condition) || !is_array($condition)){
            return FALSE;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }
    
    public function updateAppversion($condition){
        if(empty($condition) || !is_array($condition)){
            return FALSE;
        }
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    public function getAppUrl()
    {
        return self::find()->orderBy('id desc')->one();
    }
}
