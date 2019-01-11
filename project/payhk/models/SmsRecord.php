<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "pay_sms".
 *
 * @property integer $id
 * @property integer $aid
 * @property string $code
 * @property string $mobile
 * @property string $channel_type
 * @property integer $status
 * @property string $create_time
 */
class SmsRecord extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pay_sms';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'code', 'mobile', 'channel_type', 'status', 'create_time'], 'required'],
            [['aid', 'status'], 'integer'],
            [['create_time'], 'safe'],
            [['code'], 'string', 'max' => 10],
            [['mobile'], 'string', 'max' => 16],
            [['channel_type'], 'string', 'max' => 20]
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
            'code' => 'Code',
            'mobile' => 'Mobile',
            'channel_type' => 'Channel Type',
            'status' => 'Status',
            'create_time' => 'Create Time',
        ];
    }

    public function createData($data){
        $data['create_time'] = date("Y-m-d H:i:s", time());
        $error = $this->chkAttributes($data);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        }else{
            return $result;
        }
    }

    public function findOneHourCount($mobile,$aid){
        $data = date('Y-m-d H:i:s',strtotime('-1 hour'));
        $where = [ 'AND',
            ['mobile'        =>  $mobile],
            ['aid'           =>  $aid],
            // ['channel_type'  =>  $channel_type],
            ['>=', 'create_time', $data],
        ];
        $info = self::find()->where($where)->count();
        return $info;
    }
}
