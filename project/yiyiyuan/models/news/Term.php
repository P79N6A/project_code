<?php

namespace app\models\news;

use app\commonapi\Keywords;
use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_term".
 *
 * @property string $id
 * @property string $user_id
 * @property string $db_term
 * @property integer $db_canterm
 * @property string $db_amount
 * @property string $xy_term
 * @property integer $xy_canterm
 * @property string $xy_amount
 * @property string $last_modify_time
 * @property string $create_time
 */
class Term extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_term';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id'], 'required'],
            [['user_id', 'db_canterm', 'xy_canterm'], 'integer'],
            [['db_amount', 'xy_amount'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['db_term', 'xy_term'], 'string', 'max' => 10],
            [['user_id'], 'unique']
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
            'db_term' => 'Db Term',
            'db_canterm' => 'Db Canterm',
            'db_amount' => 'Db Amount',
            'xy_term' => 'Xy Term',
            'xy_canterm' => 'Xy Canterm',
            'xy_amount' => 'Xy Amount',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function saveTerm($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $this;
    }

    public function updateTerm($condition) {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $this->save();
        } catch (\Exception $ex) {
            return FALSE;
        }
        return $this;
    }

    public function getTremByUserId($user_id){
        if(!intval($user_id)){
            return null;
        }
        return self::find()->where(['user_id'=>$user_id])->one();
    }

    /**
     * 获取用户可分期的最大额度
     * @param $user_id
     * @param $type 1：信用借款 4：担保借款
     * @return int|mixed
     */
    public function getTremAmountMax($user_id,$type){
        if(Keywords::machTermOpen() == 2){//分期开关
            return 0;
        }
        if(!intval($user_id) || empty($user_id)){
            return 0;
        }
        $userTrem = self::find()->where(['user_id'=>$user_id])->one();
        if(!$userTrem){
            return 0;
        }
        if($type == 1){
            $maxAmount = $userTrem->xy_amount;
        }elseif($type == 4){
            $maxAmount = $userTrem->db_amount;
        }else{
            $maxAmount = 0;
        }
        return $maxAmount;
    }

}
