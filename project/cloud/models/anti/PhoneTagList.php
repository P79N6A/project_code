<?php

namespace app\models\anti;

use Yii;
use app\common\Logger;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "phone_tag_list".
 *
 * @property string $id
 * @property string $jxl_id
 * @property string $status
 * @property string $tag_info
 * @property string $source
 * @property string $create_time
 */
class PhoneTagList extends AntiBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tag_info_list';
    }

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['phone', 'source','user_phone', 'modify_time', 'create_time', 'type'], 'required'],
            [['source', 'status', 'type'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['phone','user_phone'], 'string', 'max' => 20],
            [['tag_type'], 'string', 'max' => 255],
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'user_phone' => '目标手机号',
            'phone' => '用户手机号',
            'source' => '标签来源：1 聚信立；2 数据魔盒； 3 上数',
            'tag_type' => '电话标签',
            'modify_time' => '最近修改时间',
            'create_time' => '添加时间',
            'status' => 'Status',
            'type' => '1  插入 ；2 更新',
        ]; 
    } 

    public function saveData($data)
    {
        $time = date("Y-m-d H:i:s"); 
        $data['create_time'] = $time;
        $data['modify_time'] = $time;
        $error = $this->chkAttributes($data); 
        if ($error) { 
            Logger::dayLog("anti/PhoneTagList","save failed", $data, $error);
            return false;
        }
        return $this->save();
    }

    public function getTagInfo($where, $limit, $select = '*')
    {
        $res = static::find()->where($where)->select($select)->asArray()->limit($limit)->all();
        return $res;
    }


    public function getTagMaxId()
    {
        $res = static::find()->max('id');
        return $res;
    }

    public function lockStatus($ids,$status)
    {
        $nums = self::updateAll(['status' => $status, 'modify_time' => date('Y-m-d H:i:s')], ['id' => $ids]);
        return $nums;
    }

    public function lockStatusByPhone($ids,$status)
    {
        $nums = self::updateAll(['status' => $status, 'modify_time' => date('Y-m-d H:i:s')], ['phone' => $ids]);
        return $nums;
    }
    
    public function getExistTag($phone_list)
    {
        if (empty($phone_list)) {
            return false;
        }
        $phone_str = "'".implode("','",$phone_list)."'";
        $where = ['in','phone',$phone_str];
        $res = static::find()->select('phone,tag_type')->where($where)->asArray();
        $sql = $res->createCommand()->getRawSql();
        Logger::dayLog("anti/sql",$sql);
        $res = $res->all();
        return $res;
    }

    public function getInitCount()
    {
        $time = date("Y-m-d", strtotime("-3 day"));
        return $this->find()->where(['and',['status'=>0],['>=','create_time',$time]])->count();
    }
}
