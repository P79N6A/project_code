<?php

namespace app\models\sjmh;

use Yii;
use yii\helpers\ArrayHelper;
use app\models\BaseModel;
use app\common\Logger;
/**
 * This is the model class for table "xhh_sjmh_result".
 *

 */
class SjmhResult extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_sjmh_result';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id','task_id', 'aid', 'data_url','create_time','user_id','source'], 'required'],
            [['create_time'], 'safe'],
            [['request_id', 'user_id','aid'], 'integer'],
            [[ 'task_id'], 'string', 'max' => 50],
            [['data_url'], 'string', 'max' => 200],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'task_id' => 'Task ID',
            'request_id' => 'Request ID',
            'source_type' => 'Source Type',
            'create_time' => 'Create Time',
            'data_url' => 'Data Url',
            'aid' => 'aid',
        ];
    }
    /*public function optimisticLock() {
        return "version";
    }*/
    /**
     * Undocumented function
     * 保存数据
     * @param [type] $postdata
     * @return void
     */
    public function saveData($postdata){
        // 检测数据
        if (!is_array($postdata) || empty($postdata)) {
            return $this->returnError(false, '不能为空');
        }
        $nowTime = date('Y-m-d H:i:s');
        $re = $this->getOne($postdata['request_id'],'request_id');
        if($re){
            $re->data_url = $postdata['data_url'];
            $re->create_time = $nowTime;
            return  $re->update();
        }
        $postdata['create_time'] = $nowTime;
        $error = $this->chkAttributes($postdata);
        if ($error) {
            return $this->returnError(false, $error);
        }
        $result = $this->save();

        return $result;
    }


    /*
   * 根据id 查询单条语句
   * */
    public function getOne($id,$column='id'){
        if(empty($id)){
            return false;
        }
        $result = self::find()->where(['=' , $column , $id] )->one();
        return $result;
    }
}