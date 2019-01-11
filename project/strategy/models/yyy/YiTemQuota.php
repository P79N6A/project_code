<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "yi_tem_quota".
 *
 * @property string $id
 * @property string $user_id
 * @property string $quota
 * @property string $days
 * @property string $create_time
 * @property integer $version
 */
class YiTemQuota extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_tem_quota';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'quota', 'days'], 'required'],
            [['user_id', 'version'], 'integer'],
            [['quota', 'days'], 'number'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'quota' => 'Quota',
            'days' => 'Days',
            'create_time' => 'Create Time',
            'version' => '乐观锁',
        ];
    }

    public function getTemQuota($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->orderby('ID DESC')->Asarray()->one();
        if (empty($res)) {
            return [];
        }
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
}
