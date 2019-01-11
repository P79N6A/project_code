<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yx_system_message_list".
 *
 * @property string $id
 * @property string $mid
 * @property string $title
 * @property string $contact
 * @property string $user_id
 * @property integer $read_status
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class SystemMessageList extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_system_message_list';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mid', 'title', 'contact', 'user_id', 'read_status','send_time', 'create_time', 'last_modify_time'], 'required'],
            [['mid', 'user_id', 'read_status', 'version'], 'integer'],
            [['contact'], 'string'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['title'], 'string', 'max' => 1024]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mid' => 'Mid',
            'title' => 'Title',
            'contact' => 'Contact',
            'user_id' => 'User ID',
            'send_time' => 'Send Time',
            'read_status' => 'Read Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
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

    /**
     * 通过user_id mid 获取用户已经收到的系统消息
     * @param $user_id
     * @param array $mids  messageApply表id数组
     * @return null
     */
    public function getByUidMids($user_id,$mids){
        $user_id = intval($user_id);
        if(!$user_id){
            return null;
        }
        if(!is_array($mids)){
            return null;
        }
        return self::find()->where(['user_id'=>$user_id,'mid'=>$mids])->all();
    }

    public function saveMsgList($condition){
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $now = date('Y-m-d H:i:s');
        $condition['read_status'] = 0;
        $condition['create_time'] = $now;
        $condition['last_modify_time'] = $now;
        $condition['version'] = 0;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 获取未读总数
     * @param $user_id
     * @return int
     */
    public function getNoReadSum($user_id){
        $user_id = intval($user_id);
        if(!$user_id){
            return 0;
        }
        return self::find()->where(['user_id' => $user_id,'read_status' => 0])->all();
    }

    public function readStatus()
    {
        try {
            $now = date('Y-m-d H:i:s');
            $this->read_status = 1;
            $this->last_modify_time = $now;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }
        //var_dump($e->getMessage());die;
     public function getMessageapply() {
        return $this->hasOne(MessageApply::className(), ['id' => 'mid']);
    }

    //系统未读消息数量
    public function getSysmsgcount($user_id,$read_status=0){
           $now_time = date('Y-m-d H:i:s');
           $count =  SystemMessageList::find()->where(['user_id'=>$user_id,'read_status'=>$read_status])->andWhere("send_time <= '$now_time'")->count();
           return $count;
    }

    //系统未读消息(列表展示)
    public function getSysmsg($user_id,$offset,$limit=10)
    {
        $now_time = date('Y-m-d H:i:s');
        $list = SystemMessageList::find()->select('id,title,contact,read_status,send_time')->where(['user_id'=>$user_id])->andWhere("send_time <= '$now_time'")->offset($offset)
                ->limit($limit)->orderBy('send_time desc')->asArray()->all();
            foreach ($list as $key => $value) {
                $list[$key]['msg_id'] = $value['id'];
                unset($list[$key]['id']);
                $list[$key]['status'] = $value['read_status'];
                unset($list[$key]['read_status']);
                //$list[$key]['time'] = date('Y-m-d H:i',strtotime($value['send_time']));
                $list[$key]['time'] = mb_substr($value['send_time'],0,16);
                unset($list[$key]['send_time']);
            }

            return $list;
    }

      /**
     * 根据消息id,用户id查询消息详情
     * @return array
     */
    public function getSysmsginfoByUserIdAndMsgId($user_id,$msg_id) {
        if (empty($msg_id) || !is_numeric($msg_id) || empty($user_id) || !is_numeric($user_id) ) {
            return null;
        }
        $msginfo = SystemMessageList::find()->where(['user_id'=>$user_id,'id'=>$msg_id])->one();
        
        return $msginfo;
    }


    public function update_info($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }

        $condition['last_modify_time'] = date('Y-m-d H:i:s');

        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

}
