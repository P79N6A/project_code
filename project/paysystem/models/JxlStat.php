<?php

namespace app\models;

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
 * @property integer $is_valid
 * @property string $url
 * @property integer $source
 */
class JxlStat extends \yii\db\ActiveRecord
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
        return Yii::$app->get('xhh_open');
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
            'aid' => 'Aid',
            'requestid' => 'Requestid',
            'name' => 'Name',
            'idcard' => 'Idcard',
            'phone' => 'Phone',
            'website' => 'Website',
            'create_time' => 'Create Time',
            'is_valid' => 'Is Valid',
            'url' => 'Url',
            'source' => 'Source',
        ];
    }
    public static function fromList(){
		return [
			'1' => '聚信立',
			'2' => '聚信立',
			'3' => '未知',
			'4' => '上数',
			'5' => '百荣/导流'
		];

    }
    /**
     * Undocumented function
     * 检查结果
     * @return void
     */
    public static function isValid(){
		return [
			'0' => '初始',
			'1' => '报告OK',
			'2' => '详情OK',
			'3' => '都OK'
		];

	}
}