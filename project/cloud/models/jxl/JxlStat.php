<?php

namespace app\models\jxl;

use Yii;

/**
 * This is the model class for table "jxl_stat".
 *
 * @property integer $id
 * @property integer $aid
 * @property integer $requestid
 * @property string $name
 * @property string $idcard
 * @property string $phone
 * @property string $website
 * @property string $create_time
 * @property string $is_valid
 * @property string $url
 * @property integer $source
 */
class JxlStat extends \app\models\jxl\JxlBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jxl_stat';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'requestid', 'is_valid', 'source'], 'integer'],
            [['name', 'idcard', 'phone', 'website', 'create_time', 'url'], 'required'],
            [['create_time'], 'safe'],
            [['name', 'website'], 'string', 'max' => 50],
            [['idcard', 'phone'], 'string', 'max' => 20],
            [['url'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => '应用id',
            'requestid' => '请求id',
            'name' => '姓名',
            'idcard' => '身份证',
            'phone' => '手机号',
            'website' => '网站英文名称',
            'create_time' => '创建时间',
            'is_valid' => '检查;1:报告ok;2:详情ok;3:都ok',
            'url' => '统计JSON存储地址',
            'source' => '来源:1:XIANHUAHUA; 2:kuaip 3:rong360 4:上数',
        ];
    }

    public function getJxlInfo($where, $select = '*')
    {
        $res = static::find()->where($where)->select($select)->asArray()->all();
        return $res;
    }


    public function getJxlMaxId()
    {
        $time = date("Y-m-d H:i:s",strtotime("-20 minute"));
        $res = static::find()
               ->where(['<=','create_time',$time])
               ->select('id')
               ->orderBy('id DESC')
               ->limit(1)
               ->one();
        $max_id = isset($res['id']) ? $res['id'] : 1;
        return $max_id;
    }


}
