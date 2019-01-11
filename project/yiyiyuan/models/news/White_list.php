<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;
use app\commonapi\Logger;

/**
 * This is the model class for table "yi_white_list".
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $idno
 * @property string $mobile
 * @property integer $user_type
 * @property integer $grade
 * @property string $amount
 * @property string $last_modify_time
 * @property string $create_time
 */
class White_list extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_white_list';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['user_id', 'name', 'idno', 'mobile', 'user_type', 'grade'], 'required'],
            [['user_id', 'user_type', 'grade'], 'integer'],
            [['amount'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['name'], 'string', 'max' => 64],
            [['idno', 'mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'name' => 'Name',
            'idno' => 'Idno',
            'mobile' => 'Mobile',
            'user_type' => 'User Type',
            'grade' => 'Grade',
            'amount' => 'Amount',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 通过user_id验证用户是否为白名单用户
     * @param [user_id]
     * @return [true,false]
     * */
    public function isWhiteList($user_id) {
        if (empty($user_id)) {
            return false;
        }
        $data = $this->findOne(['user_id' => $user_id]);
        if ($data) {
            return true;
        }

        return false;
    }
    
    /**
     * 添加一条纪录（如果存在记录则更新记录）
     */
    public static function addBatch($idno, $name, $mobile, $amount, $user_id) {
        if (empty($user_id)) {
            return FALSE;
        }
        // 数据
        $create_time = date('Y-m-d H:i:s');
        // 是否存在
        $o = static::find()->where(['user_id' => $user_id])->one(); 
        if (empty($o)) {
            $o = new self;
            $data = [
                'user_id' => $user_id,
                'name' => $name,
                'mobile' => $mobile,
                'idno' => $idno,
                'user_type' => 1,
                'grade' => 1,
                'amount' => $amount,
                'last_modify_time' => $create_time,
                'create_time' => $create_time,
            ];
            
            $error = $o->chkAttributes($data);
            Logger::dayLog('Alpay', "errrrr", $error);
            if($error){
                return false;
            }
            
        } else {
            $data = [
                'last_modify_time' => $create_time,
            ];
        }
        // 保存数据
        $o->attributes = $data;
        return $o->save();
    }

}
