<?php

namespace app\models\peanut;

use Yii;

/**
 * This is the model class for table "pea_request".
 *
 * @property string $id
 * @property string $order_id
 * @property string $user_id
 * @property integer $st_source
 * @property string $create_time
 * @property string $modify_time
 * @property string $version
 */
class PeaRequest extends BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pea_request';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'st_source'], 'required'],
            [['user_id', 'st_source', 'version'], 'integer'],
            [['create_time', 'modify_time'], 'safe'],
            [['order_id'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '请求ID（唯一）',
            'order_id' => '订单ID',
            'user_id' => '业务端用户ID',
            'st_source' => '来源：  1 注册，2登录  3 提现',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
            'version' => '乐观锁',
        ];
    }

    public function saveData($postData)
    {
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $postData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            return $this->returnError(false, $error);
        }
        $res = $this->save();
        if (!$res) {
            return false;
        }
        return $this->id;
    }
}
