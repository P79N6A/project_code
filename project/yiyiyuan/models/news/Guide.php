<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_guide".
 *
 * @property string $id
 * @property string $user_id
 * @property string $mobile
 * @property string $identity
 * @property string $realname
 * @property string $create_time
 */
class Guide extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_guide';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from', 'user_id', 'status', 'return_status'], 'integer'],
            [['create_time', 'send_time'], 'safe'],
            [['mobile', 'identity'], 'string', 'max' => 20],
            [['realname'], 'string', 'max' => 32],
            [['uid'], 'string', 'max' => 64],
            [['return_message', 'detailMessage'], 'string', 'max' => 350]
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
            'mobile' => 'Mobile',
            'identity' => 'Identity',
            'realname' => 'Realname',
            'create_time' => 'Create Time',
        ];
    }

    //乐观所版本号
    public function optimisticLock()
    {
        return "version";
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     */
    public function addGuide($condition)
    {
        if (!is_array($condition) || empty($condition) || !isset($condition['user_id']) || empty($condition['user_id'])) {
            return false;
        }
        $count = self::find()->where(['user_id' => $condition['user_id']])->count();
        if ($count > 0) {
            return false;
        }
        $condition['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    //加悲观锁
    public function addLock()
    {
        try {
            $this->status = 3;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //保存为不符合规则
    public function updateNotAccord()
    {
        $this->status = 4;
        $this->send_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

    public function updateInsure($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $condition['send_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
