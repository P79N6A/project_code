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
class News extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_news';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            
        ];
    }
    
    /**
     * 获取消息列表
     */
    public function getNewsList($type,$user_id){
    	$sql_list = "select * from ".News::tableName()." where news_type=".$type." and (user_id=".$user_id." or type='ALL')";
    	$news_list = Yii::$app->db->createCommand($sql_list)->queryAll();
    	if(!empty($news_list)){
    		$list = array();
    		foreach ($news_list as $key=>$value){
    			$list[$key]['news_id'] = $value['id'];
    			$list[$key]['news_content'] = $value['content'];
    		}
    		return $list;
    	}else{
    		return null;
    	}
    }
}
