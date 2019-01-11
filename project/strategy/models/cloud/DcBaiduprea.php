<?php

namespace app\models\cloud;

use Yii;

/**
 * This is the model class for table "dc_baiduprea".
 *
 * @property string $id
 * @property string $basic_id
 * @property string $realname
 * @property string $phone
 * @property string $idcard
 * @property string $reqid
 * @property integer $retCode
 * @property string $retMsg
 * @property integer $score
 * @property string $models
 * @property string $modify_time
 * @property string $create_time
 */
class DcBaiduprea extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_baiduprea';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basic_id', 'retCode', 'score'], 'integer'],
            [['realname', 'phone', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['modify_time', 'create_time'], 'safe'],
            [['realname', 'phone', 'idcard'], 'string', 'max' => 20],
            [['reqid'], 'string', 'max' => 32],
            [['retMsg', 'models'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'basic_id' => '请求表id',
            'realname' => '用户真实姓名',
            'phone' => '手机',
            'idcard' => '身份证',
            'reqid' => '唯一请求识别码',
            'retCode' => '百度金融请求返回码',
            'retMsg' => '百度金融请求返回信息',
            'score' => '信用分值',
            'models' => '信用分模型',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    /**
     * 获取百度金融结果
     */
    public function getResult($phone,$idcard){
        $datetime = date('Y-m-d H:i:s', strtotime('-3 month'));
        $where = ['AND',
            [
                'phone' => $phone,
                'idcard' => $idcard,
                'retCode' => 0,
            ],
            ['!=','retMsg','null'],
            ['>','create_time',$datetime],
        ];
        $data = static::find() -> where($where)->orderBy('create_time DESC')  -> limit(1) ->one();
        return $data;
    }
}
