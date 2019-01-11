<?php

namespace app\models\edu;

use app\common\Logger;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "xhh_ducredit_ticket".
 *
 * @property integer $id
 * @property string $ducredit_appid
 * @property string $expire
 * @property string $ducredit_ticket
 * @property string $modify_time
 * @property string $create_time
 */
class DucreditTicket extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'xhh_ducredit_ticket';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ducredit_appid', 'create_time'], 'required'],
            [['expire', 'modify_time', 'create_time'], 'safe'],
            [['ducredit_appid', 'ducredit_ticket'], 'string', 'max' => 50],
            [['errmsg'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ducredit_appid' => 'Ducredit Appid',
            'expire' => 'Expire',
            'ducredit_ticket' => 'Ducredit Ticket',
            'errmsg'  => 'Errmsg',
            'modify_time' => 'Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 通过ducredit_appid获取最新的一条记录
     * @param $ducredit_appid
     * @return array|bool|null|\yii\db\ActiveRecord
     */
    public function getRecord($ducredit_appid)
    {
        if (empty($ducredit_appid)){
            return false;
        }
        $expire = date("Y-m-d H:i:s", time());
        $where_config = [
            'AND',
            ['>=', 'expire', $expire],
            ['=', 'ducredit_appid', $ducredit_appid],
        ];
        return self::find()->where($where_config)->orderBy('create_time desc')->one();
    }

    /**
     * 保存记录
     * @param $data_set
     * @return bool
     */
    public function saveData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $save_data = [
            'ducredit_appid'        => ArrayHelper::getValue($data_set, 'ducredit_appid', 0), //学历号',
            'create_time'           => date("Y-m-d H:i:s", time()), //创建时间',
        ];
        $errors = $this->chkAttributes($save_data);
        if ($errors){
            Logger::dayLog("edu/ducreditticket", '保存数据出错提示', json_encode($errors));
        }
        return $this->save();
    }

    /**
     * 更新数据
     * @param $data_set
     * @return bool
     */
    public function updateData($data_set)
    {
        if (empty($data_set)){
            return false;
        }
        $this->modify_time = date("Y-m-d H:i:s", time());
        foreach ($data_set as $k=>$v){
            $this->$k = $v;
        }
        return $this->save();
    }

    public function getTicket($ducredit_ticket)
    {
        if (empty($ducredit_ticket)){
            return false;
        }
        $where_config = [
            'AND',
            ['=', 'ducredit_ticket', $ducredit_ticket],
            ['>=', 'expire', date("Y-m-d H:i:s", time())]
        ];
        return self::find()->where($where_config)->orderBy('create_time desc')->one();
    }
}