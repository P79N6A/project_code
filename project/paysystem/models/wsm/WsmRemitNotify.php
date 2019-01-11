<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/10/17
 * Time: 11:52
 */

namespace app\models\wsm;

use app\models\remit\ClientNotify;
use Yii;

/**
 * This is the model class for table "wsm_remit_notify".
 *
 * @property integer $id
 * @property integer $remit_id
 * @property string $tip
 * @property integer $remit_status
 * @property integer $notify_num
 * @property integer $notify_status
 * @property string $notify_time
 * @property string $reason
 * @property string $create_time
 */
class WsmRemitNotify extends ClientNotify
{

    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'wsm_remit_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remit_id', 'shddh', 'tip', 'notify_time', 'create_time'], 'required'],
            [['remit_status', 'notify_num', 'notify_status'], 'integer'],
            [['notify_time', 'create_time'], 'safe'],
            [['tip'], 'string', 'max' => 255],
            [['shddh', 'remit_id'], 'string', 'max' => 50],
            [['reason'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'remit_id' => 'Remit ID',
            'shddh' =>'Shddh',
            'tip' => 'Tip',
            'remit_status' => 'Remit Status',
            'notify_num' => 'Notify Num',
            'notify_status' => 'Notify Status',
            'notify_time' => 'Notify Time',
            'reason' => 'Reason',
            'create_time' => 'Create Time',
        ];
    }

    public function addNotify($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $create_time = date("Y-m-d H:i:s", time());
        $data_set = [
                'remit_id' => empty($data['remit_id'])?"" : $data['remit_id'], //出款id',
                'shddh' => empty($data['shddh'])?"" : $data['shddh'], //[资产平台系统的商户订单]商户订单号,
                'tip' => empty($data['tip'])?"" : $data['tip'], //通知内容',
                'remit_status' => empty($data['remit_status'])?"" : $data['remit_status'], //出款状态:3:处理中(暂不存在);6:成功:11:失败;',
                'notify_num' => empty($data['notify_num'])?"" : $data['notify_num'], //通知次数: 上限7次',
                'notify_status' => 0, //通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 11:通知失败',
                'notify_time' => $create_time, //下次通知时间',
                'reason' => empty($data['reason'])?"" : $data['reason'], //通知失败原因:例如没有回调地址',
                'create_time' => $create_time, //创建时间',
        ];
        $errors = $this->chkAttributes($data_set);
        if ($errors) {
            return false;
        }
        $ret = $this->save();
        if (!$ret){
            return false;
        }
        return true;
    }

    /**
     * 查询: 获取正在处理中的数据
     * @param $limit
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getDoingData($limit) {
        // 按查询时间排序
        $data = static::find()->where(['notify_status' => [0, 3]])->offset(0)->limit($limit)->all();
        return $data;
    }

    /**
     * 查询: 锁定正在查询接口的状态
     * @param $ids
     * @return int
     */
    public function lockNotify($ids) {
        if (!is_array($ids) || empty($ids)) {
            return 0;
        }
        $ups = static::updateAll(['notify_status' => 1], ['id' => $ids]);
        return $ups;
    }

    /**
     * 更新数据
     * @param $data_set
     * @return bool|int
     */
    public function updateNotify($data_set) {
        if (!is_array($data_set) || empty($data_set)) {
            return 0;
        }
        foreach ($data_set as $key => $value){
            $this->$key = $value;
        }
        $this->notify_num = $this->notify_num + 1;
        $this->notify_time = date('Y-m-d H:i:s', time());
        $ret = $this->save();
        return $ret;
    }

    public function getNotify($client_id)
    {
        if (empty($client_id)){
            return false;
        }
        return self::find()->where(['shddh' => $client_id])->one();
    }


}