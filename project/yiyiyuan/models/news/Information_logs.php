<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_information_logs".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $operation_type
 * @property integer $type
 * @property string $result
 * @property integer $source
 * @property string $create_time
 */
class Information_logs extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_information_logs';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'create_time'], 'required'],
            [['user_id', 'operation_type', 'type', 'source'], 'integer'],
            [['result'], 'string'],
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
            'user_id' => 'User ID',
            'operation_type' => 'Operation Type',
            'type' => 'Type',
            'result' => 'Result',
            'source' => 'Source',
            'create_time' => 'Create Time',
        ];
    }
    
        
    /**
     * 重构创建日志
     * @param type $condition
     * @return boolean
     * @author Zhangchao<zhangchao@xianhuahua.com>
     */
    public function save_informationlogs($condition) {
        if (!isset($condition['user_id']) || empty($condition['user_id'])) {
            return false;
        }
        if (!isset($condition['operation_type']) || empty($condition['operation_type'])) {
            return false;
        }
        $data = $condition;
        $data['result'] =  serialize($condition['result']);
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if($error){
            return false;
        }
        return $this->save();
    }

    /**
     * 拼装保存信息
     * @param type $user
     * @param type $operation
     * @param type $result
     * @param type $source
     * @param type $type
     * @return boolean
     */
    public function save_idenlogs($user, $operation, $result, $source = 1, $type = 1) {
        $redis_key = array(
            '1' => array(
                '1' => 'user_iden_dif_' . $user->user_id,
                '2' => 'user_iden_exist_' . $user->user_id,
                '3' => 'user_iden_verify_' . $user->user_id,
                '4' => 'h5_user_iden_times' . $user->user_id,
            ),
            '2' => array(
                '4' => 'user_pic_times_' . $user->user_id,
            )
        );
        $conditon = array(
            'user_id' => $user->user_id,
            'operation_type' => $operation,
            'type' => $type,
            'result' => $result,
            'source' => $source
        );
        $this->save_informationlogs($conditon);
        if ($type != 0) {
            $user_iden_keys = $redis_key[$operation][$type];
            $user_iden = Yii::$app->redis->get($user_iden_keys);
            if (empty($user_iden)) {
                Yii::$app->redis->setex($user_iden_keys, 2592000, 1);
            } else {
                Yii::$app->redis->set($user_iden_keys, $user_iden + 1);
            }
            return Yii::$app->redis->get($user_iden_keys);
        } else {
            foreach ($redis_key[1] as $val) {
                Yii::$app->redis->del($val);
            }
            return false;
        }
    }
        /**
     * 返回验证限制是否还能认证
     * @param type $user
     * @param type $operation 1 身份验证，2 活体验证
     */
    public function getMark($user, $operation) {
        $redis_key = array(
            '1' => array(
                '1' => 'user_iden_dif_' . $user->user_id,
                '2' => 'user_iden_exist_' . $user->user_id,
                '3' => 'user_iden_verify_' . $user->user_id,
                '4' => 'h5_user_iden_times' . $user->user_id,
            ),
            '2' => array(
                '1' => 'user_pic_times_' . $user->user_id,
            )
        );
        $num = array(
            '1' => array(0, 1, 1, 2, 5),
            '2' => array(0, 5),
        );
        $mark = 1;
        switch ($operation) {
            case 1:
                $arr = $redis_key[1];
                for ($i = 1; $i <= count($arr); $i++) {
                    $user_iden_keys = $arr[$i];
                    $user_iden = Yii::$app->redis->get($user_iden_keys);
                    if ($user_iden >= $num[1][$i]) {
                        $mark = 0;
                        break;
                    }
                }
                break;
            case 2:
                $arr = $redis_key[2];
                for ($i = 1; $i <= count($arr); $i++) {
                    $user_iden_keys = $arr[$i];
                    $user_iden = Yii::$app->redis->get($user_iden_keys);
                    if ($user_iden >= $num[2][$i]) {
                        $mark = 0;
                        break;
                    }
                }
                break;
        }
        return $mark;
    }

    /**
     * 返回人工认证状态
     * @param type $user
     */
    public function getVideoAuthCount($user_id) {
        if(empty($user_id)){
            return false;
        }
        $user_iden_keys ='user_pic_times_' . $user_id;
        $user_iden = Yii::$app->redis->get($user_iden_keys);
        if (empty($user_iden)) {
            $times=0;
        } else {
            $times=$user_iden;
        }
        if($times<3){
            $artificial_video_status=1;//不能人工认证
        }else if($times>=3 && $times<5){
            $artificial_video_status = 2; //可以人工认证(失败超3次)
        }else if($times>=5){
            $artificial_video_status = 3; //只能人工认证（失败超5次）
        }
        return $artificial_video_status;
    }
}
