<?php

namespace app\models\open;

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
class OpJxlStat extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'jxl_stat';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_open');
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

    public function getJxl($where)
    {
        return $this->find()->where($where)->orderby('ID DESC')->limit(1)->one();
    }

    public function getByPhone($phone){
        $limitTime = 86400 * 120;
        $t = time() - $limitTime;
        $d = date('Y-m-d H:i:s', $t);

        $count = static::find()
            ->where(['phone' => $phone])
            ->andWhere(['not in', 'website', ['jingdong']])
            ->andWhere(['>=', 'create_time', $d])
            ->asArray()
            ->orderBy('create_time DESC')
            ->one();
        return $count;
    }
}
