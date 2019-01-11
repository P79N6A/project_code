<?php

namespace app\models\loan;

use Yii;

/**
 * This is the model class for table "operation".
 *
 * @property string $id
 * @property string $user_id
 * @property string $request_id
 * @property integer $status
 * @property string $response
 * @property integer $type
 * @property integer $source
 * @property string $modify_time
 * @property string $create_time
 */
class SfOperation extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'request_id', 'status', 'type', 'source'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
            [['response'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增字段',
            'user_id' => 'User ID',
            'request_id' => '请求ID',
            'status' => '状态：0初始；1处理中；2成功；3失败；',
            'response' => '响应结果',
            'type' => '1 通话详单；2 京东 默认1',
            'source' => '来源:开放平台提供',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }
}
