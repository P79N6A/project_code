<?php

namespace app\models\antifraud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "af_contact".
 * 亲属联系人匹配
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property integer $com_r_total
 * @property integer $com_r_rank
 * @property string $com_r_total_mavg
 * @property string $com_r_duration
 * @property integer $com_r_duration_rank
 * @property string $com_r_duration_mavg
 * @property integer $com_c_total
 * @property integer $com_c_rank
 * @property string $com_c_total_mavg
 * @property string $com_c_duration
 * @property integer $com_c_duration_rank
 * @property string $com_c_duration_mavg
 * @property integer $com_r_overdue
 * @property integer $com_c_overdue
 * @property string $create_time
 */
class Contact extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_contact';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'user_id', 'com_r_total', 'com_r_rank', 'com_r_duration', 'com_r_duration_rank', 'com_c_total', 'com_c_rank', 'com_c_duration', 'com_c_duration_rank', 'com_r_overdue', 'com_c_overdue'], 'integer'],
            [['com_r_total_mavg', 'com_r_duration_mavg', 'com_c_total_mavg', 'com_c_duration_mavg'], 'number'],
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
            'id' => 'ID',
            'request_id' => '请求处理id',
            'user_id' => '用户ID',
            'com_r_total' => '亲属联系人通话次数',
            'com_r_rank' => '亲属联系人通话次数排名',
            'com_r_total_mavg' => '亲属联系人次数月均',
            'com_r_duration' => '亲属联系人通话时长',
            'com_r_duration_rank' => '亲属联系人通话时长排名',
            'com_r_duration_mavg' => '亲属联系人通话时长月均',
            'com_c_total' => '社会联系人通话次数',
            'com_c_rank' => '社会联系人通话次数排名',
            'com_c_total_mavg' => '社会联系人次数月均',
            'com_c_duration' => '社会联系人通话时长',
            'com_c_duration_rank' => '社会联系人通话时长排名',
            'com_c_duration_mavg' => '社会联系人月均通话时长月均',
            'com_r_overdue' => '亲属联系人为先花逾期客户',
            'com_c_overdue' => '社会联系人为先花逾期客户',
            'create_time' => '记录创建时间',
        ];
    }

    public function getContact($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v,'');
            $res[$v] = (float)($val ? (sprintf('%.2f',$val)) : 0);
        }
        return $res;
    }
}
