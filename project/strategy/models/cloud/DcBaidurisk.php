<?php

namespace app\models\cloud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "dc_baidurisk".
 *
 * @property string $id
 * @property string $basic_id
 * @property string $identity_id
 * @property string $realname
 * @property string $phone
 * @property string $idcard
 * @property integer $retCode
 * @property string $retMsg
 * @property string $black_level
 * @property string $detail_info
 * @property string $modify_time
 * @property string $create_time
 */
class DcBaidurisk extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_baidurisk';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['basic_id', 'retCode'], 'integer'],
            [['identity_id', 'realname', 'phone', 'idcard', 'modify_time', 'create_time'], 'required'],
            [['detail_info'], 'string'],
            [['modify_time', 'create_time'], 'safe'],
            [['identity_id'], 'string', 'max' => 50],
            [['realname', 'phone', 'idcard'], 'string', 'max' => 20],
            [['retMsg'], 'string', 'max' => 64],
            [['black_level'], 'string', 'max' => 4]
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
            'identity_id' => '用户唯一标识',
            'realname' => '用户真实姓名',
            'phone' => '手机',
            'idcard' => '身份证',
            'retCode' => '百度金融请求返回码',
            'retMsg' => '百度金融请求返回信息',
            'black_level' => '百度金融用户评级',
            'detail_info' => '百度金融征信详情',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getBaiduRisk($data,$select)
    {
        $select = explode(',',$select);
        $where = [
            'and',
            // ['identity_id' => $data['user_id']],
            ['phone' => $data['mobile']],
            ['idcard' => $data['identity']],
            ['retCode' => '0'],
        ];
        $res = $this->find()->where($where)->select($select)->limit(1)->orderby('id DESC')->asArray()->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val;
        }
        return $res;
    }
}
