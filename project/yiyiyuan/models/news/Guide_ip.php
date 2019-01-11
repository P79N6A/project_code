<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_guide_ip".
 *
 * @property string $id
 * @property integer $comefrom
 * @property string $ip
 * @property string $create_time
 */
class Guide_ip extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_guide_ip';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['comefrom'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['ip'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'comefrom' => 'Comefrom',
            'ip' => 'Ip',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 验证次IP是否是授权的第三方来源
     * @param type $code
     * @param type $ip
     * @return type
     */
    public function validIp($code, $ip) {
        $result = self::find()->where(['comefrom' => $code, 'ip' => $ip])->one();
        return $result ? true : false;
    }

}
