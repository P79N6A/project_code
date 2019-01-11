<?php

namespace app\models\peanut;

use Yii;

/**
 * This is the model class for table "pea_result".
 *
 * @property string $id
 * @property string $request_id
 * @property string $order_id
 * @property string $user_id
 * @property integer $res_status
 * @property integer $st_source
 * @property string $res_info
 * @property string $create_time
 * @property string $version
 */
class PeaResult extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pea_result';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'res_status', 'st_source', 'create_time'], 'required'],
            [['request_id', 'user_id', 'res_status', 'st_source', 'version'], 'integer'],
            [['res_info'], 'string'],
            [['create_time'], 'safe'],
            [['order_id'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键',
            'request_id' => 'pea_request请求ID，唯一',
            'order_id' => '订单ID',
            'user_id' => '用户ID',
            'res_status' => '决策结果状态',
            'st_source' => '决策请求来源',
            'res_info' => '决策返回结果',
            'create_time' => '创建时间',
            'version' => '乐观锁',
        ];
    }

    public function saveData($saveArr)
    {
        $saveArr['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($saveArr);
        if ($error) {
            return $this->returnError(false, $error);
        }
        return $this->save();
    }
}
