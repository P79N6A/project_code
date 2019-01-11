<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_sms_depository".
 *
 * @property string $id
 * @property string $recive_mobile
 * @property string $content
 * @property integer $sms_type
 * @property string $create_time
 */
class Sms_depository extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_sms_depository';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sms_type'], 'integer'],
            [['create_time'], 'safe'],
            [['recive_mobile'], 'string', 'max' => 16],
            [['content'], 'string', 'max' => 256]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recive_mobile' => 'Recive Mobile',
            'content' => 'Content',
            'sms_type' => 'Sms Type',
            'create_time' => 'Create Time',
        ];
    }

    public function addList($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['create_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 查询当天发送次数
     * @param $mobile
     * @param $type 1开户验证码 2绑卡验证码
     * @return int
     */
    public function getSmsCount($mobile, $type)
    {
        $begintime = date('Y-m-d' . ' 00:00:00');
        $endtime = date('Y-m-d' . ' 23:59:59');
        $where = [
            'AND',
            ['recive_mobile' => $mobile],
            ['sms_type' => $type],
            ['between', 'create_time', $begintime, $endtime]
        ];
        $sms_count = self::find()->where($where)->count();
        return $sms_count;
    }
}
