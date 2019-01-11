<?php

namespace app\models\anti;

use Yii;

/**
 * This is the model class for table "af_base".
 *
 * @property string $id
 * @property integer $request_id
 * @property integer $aid
 * @property integer $user_id
 * @property integer $loan_id
 * @property integer $jxlstat_id
 * @property integer $match_status
 * @property string $modify_time
 * @property string $create_time
 */
class AfBase extends AntiBaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_base';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'aid', 'user_id', 'loan_id', 'jxlstat_id', 'create_time'], 'required'],
            [['request_id', 'aid', 'user_id', 'loan_id', 'jxlstat_id', 'match_status'], 'integer'],
            [['modify_time', 'create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键，自增',
            'request_id' => '请求处理id',
            'aid' => '业务ID',
            'user_id' => '用户ID',
            'loan_id' => '贷款ID',
            'jxlstat_id' => '聚信立stat表的id',
            'match_status' => '二级匹配状态:0:初始; 1:锁定; 2:成功',
            'modify_time' => '修改时间',
            'create_time' => '创建时间'
        ];
    }

    /**
     * 获取需要匹配间接关系的数据
     */
    public function getJaccardData($data, $field = '*')
    {
        if (empty($data)) {
            return false;
        }
        $where = [
            'AND',
            ['match_status' => $data['match_status']],
            ['>', 'create_time', $data['create_time']],
        ];
//        $query = static::find()->where($where)->select($field)->asArray()->limit(500)->orderBy('create_time desc');
//        echo $query->createCommand()->getRawSql(); exit;
        return static::find()->where($where)->select($field)->asArray()->limit(500)->orderBy('create_time desc')->all();
    }

    /**
     * 锁定为间接关系处理中状态
     */
    public function lockJcards($ids) {
        if (empty($ids)) {
            return false;
        }
        $sets = [
            'match_status' => 5,
            'modify_time' => date('Y-m-d H:i:s'),
        ];
        $where = [
            'id' => $ids,
            'match_status' => 4,
        ];
        $result = static::updateAll($sets, $where);
        return $result;
    }

    /**
     * 更新为结束状态
     */
    public function finishJcard($id) {
        if (empty($id)) {
            return false;
        }
        $sets = [
            'match_status' => 6,
            'modify_time' => date('Y-m-d H:i:s'),
        ];
        $where = [
            'id' => $id
        ];
        $result = static::updateAll($sets, $where);
        return $result;
    }
}
