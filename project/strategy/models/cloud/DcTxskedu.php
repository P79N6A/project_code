<?php

namespace app\models\cloud;

use Yii;

/**
 * This is the model class for table "dc_txskedu".
 *
 * @property string $id
 * @property string $user_id
 * @property integer $aid
 * @property string $name
 * @property string $idcard
 * @property string $retCode
 * @property string $result_info
 * @property string $modify_time
 * @property string $create_time
 */
class DcTxskedu extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_txskedu';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'aid', 'name', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['user_id', 'aid'], 'integer'],
            [['result_info'], 'string'],
            [['modify_time', 'create_time'], 'safe'],
            [['name', 'idcard', 'retCode'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '业务端用户唯一标识',
            'aid' => '请求来源：1 一亿元；8 7-14',
            'name' => '用户真实姓名',
            'idcard' => '身份证',
            'retCode' => '请求状态码',
            'result_info' => '返回结果详情',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 获取学信网信息
     */
    public function getOne($user_id, $idcard){
        $where = ['AND',
            [
                'user_id' => $user_id,
                'idcard' => $idcard,
                'retCode' => '0000',
            ],
            ['!=','result_info','null'],
        ];
        $data = static::find() -> where($where)->orderBy('id DESC')->asArray()->one();
        return $data;
    }  
}
