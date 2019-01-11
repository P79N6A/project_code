<?php

namespace app\models\antifraud;

use Yii;

/**
 * This is the model class for table "af_base".
 *
 * @property string $id
 * @property string $request_id
 * @property integer $aid
 * @property string $user_id
 * @property string $loan_id
 * @property string $jxlstat_id
 * @property string $create_time
 */
class AfBase extends BaseDBModel
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
            [['request_id', 'aid', 'user_id', 'loan_id', 'jxlstat_id'], 'integer'],
            [['create_time'], 'required'],
            [['create_time', 'modify_time'], 'safe']
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
            'aid' => '业务ID',
            'user_id' => '用户ID',
            'loan_id' => '贷款ID',
            'jxlstat_id' => '聚信立stat表的id',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ];
    }

    public function getBase($where) {
        return $this->find()->where($where)->orderby('ID DESC')->limit(1)->one();
    }

    public function addBase($baseInfo) {
        $error = $this->chkAttributes($baseInfo);
        if ($error) {
            return $this->returnError(null, current($error));
        }
        //3 保存数据
        $result = $this->save();
        if (!$result) {
            return $this->returnError(null, '保存失败');
        } else {
            return $result;
        }
    }
}
