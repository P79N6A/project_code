<?php

namespace app\models\anti;

use Yii;

/**
 * This is the model class for table "af_tag_base".
 *
 * @property string $id
 * @property string $base_id
 * @property integer $aid
 * @property string $user_id
 * @property string $mobile
 * @property integer $tag_status
 * @property string $create_time
 * @property string $modify_time
 */
class AfTagBase extends AntiBaseModel
{
    const INIT = 0;
    const DOING = 1;
    const FINISHED = 2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_tag_base';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['base_id', 'aid', 'user_id', 'tag_status'], 'integer'],
            [['mobile', 'tag_status', 'create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['mobile'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'base_id' => 'af_base 表ID',
            'aid' => '业务ID',
            'user_id' => '用户ID',
            'phone' => '用户手机号',
            'tag_status' => '号码标签状态:0:初始; 1:锁定; 2:完成',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ];
    }

        /**
     * 获取需要匹配间接关系的数据
     */
    public function getTagBase($data, $field = '*')
    {
        if (empty($data)) {
            return false;
        }
        $where = [
            'AND',
            ['tag_status' => $data['tag_status']],
            ['>=', 'create_time', $data['create_time']],
        ];
//        $query = static::find()->where($where)->select($field)->asArray()->limit(500)->orderBy('create_time desc');
//        echo $query->createCommand()->getRawSql(); exit;
        return static::find()->where($where)->select($field)->asArray()->limit(100)->orderBy('create_time desc')->all();
    }

    /**
     * 锁定为间接关系处理中状态
     */
    public function lockTags($ids) {
        if (empty($ids)) {
            return false;
        }
        $sets = [
            'tag_status' => self::DOING,
            'modify_time' => date('Y-m-d H:i:s'),
        ];
        $where = [
            'id' => $ids,
            'tag_status' => self::INIT,
        ];
        $result = static::updateAll($sets, $where);
        return $result;
    }

    /**
     * 更新为结束状态
     */
    public function finishTags($ids) {
        if (empty($ids)) {
            return false;
        }
        $sets = [
            'tag_status' => self::FINISHED,
            'modify_time' => date('Y-m-d H:i:s'),
        ];
        $where = [
            'id' => $ids
        ];
        $result = static::updateAll($sets, $where);
        return $result;
    }


    /**
     * @param $start_id
     * @param $end_id
     * @return array|bool|\yii\db\ActiveRecord[]
     */
    public function getUserPhoneData($start_id, $end_id)
    {
        if (empty($start_id) || empty($end_id)){
            return false;
        }
        $where_config = [
            'AND',
            ['>=', 'id', $start_id],
            ['<=', 'id', $end_id],
        ];
        return self::find()->where($where_config)->orderBy("id asc")->all();
    }
}
