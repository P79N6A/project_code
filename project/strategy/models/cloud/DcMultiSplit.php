<?php

namespace app\models\cloud;

use Yii;

/**
 * This is the model class for table "dc_multi_split".
 *
 * @property string $id
 * @property string $phone
 * @property string $idcard
 * @property string $fid
 * @property integer $7_multi_all_p_class
 * @property integer $7_multi_p2p_p_class
 * @property integer $7_multi_small_p_class
 * @property integer $7_multi_big_p_class
 * @property integer $7_multi_common_p_class
 * @property integer $30_multi_all_p_class
 * @property integer $30_multi_p2p_p_class
 * @property integer $30_multi_small_p_class
 * @property integer $30_multi_big_p_class
 * @property integer $30_multi_common_p_class
 * @property string $create_time
 */
class DcMultiSplit extends BaseNewDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_multi_split';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'idcard', 'create_time'], 'required'],
            [['fid', '7_multi_all_p_class', '7_multi_p2p_p_class', '7_multi_small_p_class', '7_multi_big_p_class', '7_multi_common_p_class', '30_multi_all_p_class', '30_multi_p2p_p_class', '30_multi_small_p_class', '30_multi_big_p_class', '30_multi_common_p_class'], 'integer'],
            [['create_time'], 'safe'],
            [['phone', 'idcard'], 'string', 'max' => 20],
            [['fid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => '手机号',
            'idcard' => '身份证',
            'fid' => '同盾表id',
            '7_multi_all_p_class' => '七天多投总数分位值',
            '7_multi_p2p_p_class' => '七天多投P2P网贷分位值',
            '7_multi_small_p_class' => '七天多投小额贷款分位值',
            '7_multi_big_p_class' => '七天多投大型消费金融公司分位值',
            '7_multi_common_p_class' => '七天多投一般消费分期平台分位值',
            '30_multi_all_p_class' => '一个月多投总数分位值',
            '30_multi_p2p_p_class' => '一个月多投P2P网贷分位值',
            '30_multi_small_p_class' => '一个月多投小额贷款分位值',
            '30_multi_big_p_class' => '一个月多投大型消费金融公司分位值',
            '30_multi_common_p_class' => '一个月多投一般消费分期平台分位值',
            'create_time' => '创建时间',
        ];
    }

    public function getOne($where){
        return $this->find()->where($where)->asArray()->orderBy('id DESC')->limit(1)->one();
    }
}
