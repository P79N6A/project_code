<?php

namespace app\models\antifraud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "af_black".
 *
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property integer $db_auth_has_black
 * @property integer $addr_has_black
 * @property integer $db_idcard_area_black
 * @property string $create_time
 */
class Black extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_black';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_antifraud');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'user_id', 'db_auth_has_black', 'addr_has_black', 'db_idcard_area_black'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'id',
            'request_id' => '请求处理id',
            'user_id' => '用户ID',
            'db_auth_has_black' => '一级关系中黑名单人数过多',
            'addr_has_black' => '通讯录中有黑名单',
            'db_idcard_area_black' => '身份证所属地区属于黑名单',
            'create_time' => '创建时间',
        ];
    }

    public function getBlack($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
}
