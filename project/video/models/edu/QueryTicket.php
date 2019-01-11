<?php

namespace app\models\edu;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "xhh_query_ticket".
 *
 * @property integer $id
 * @property integer $t_id
 * @property string $ducredit_appid
 * @property string $ducredit_token
 * @property string $t_create_time
 * @property string $t_update_time
 * @property string $log_id
 * @property string $create_time
 */
class QueryTicket extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_query_ticket';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['t_id'], 'integer'],
            [['ducredit_appid', 't_create_time', 't_update_time', 'create_time'], 'required'],
            [['t_create_time', 't_update_time', 'create_time'], 'safe'],
            [['ducredit_appid', 'ducredit_token', 'log_id'], 'string', 'max' => 50],
            [['errmsg'], 'string', 'max' => 255 ],
            [['is_used'], 'string', 'max' => 10 ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            't_id' => 'T ID',
            'ducredit_appid' => 'Ducredit Appid',
            'ducredit_token' => 'Ducredit Token',
            't_create_time' => 'T Create Time',
            't_update_time' => 'T Update Time',
            'log_id' => 'Log ID',
            'is_used'   => 'Is Used',
            'create_time' => 'Create Time',
            'errmsg'      => 'Errmsg',
        ];
    }

    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $save_data = [
                't_id'                  => ArrayHelper::getValue($data_set, 't_id'), //ID',
                'ducredit_appid'        => ArrayHelper::getValue($data_set, 'ducredit_appid'), //学历号',
                'ducredit_token'        => ArrayHelper::getValue($data_set, 'ducredit_token'), //爬取信息',
                't_create_time'         => ArrayHelper::getValue($data_set, 't_create_time'), //应用信息时间',
                't_update_time'         => ArrayHelper::getValue($data_set, 't_update_time'), //时间',
                'log_id'                => ArrayHelper::getValue($data_set, 'log_id'), //
                'is_used'               => ArrayHelper::getValue($data_set, 'is_used', 0),
                'errmsg'                => ArrayHelper::getValue($data_set, 'errmsg', ''),
                'create_time'           => date("Y-m-d H:i:s", time())
        ];
        $errors = $this->chkAttributes($save_data);
        if ($errors){
            Logger::dayLog("edu/QueryTicket", '保存数据出错提示', json_encode($errors));
        }
        return $this->save();
    }
}