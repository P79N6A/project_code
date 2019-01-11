<?php

namespace app\models\yyy;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "yi_white_list".
 *
 * @property string $id
 * @property string $user_id
 * @property string $name
 * @property string $idno
 * @property string $mobile
 * @property integer $user_type
 * @property integer $grade
 * @property string $amount
 * @property string $last_modify_time
 * @property string $create_time
 */
class WhiteList extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_white_list';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_yyy');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'name', 'idno', 'mobile', 'user_type', 'grade'], 'required'],
            [['user_id', 'user_type', 'grade'], 'integer'],
            [['amount'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['name'], 'string', 'max' => 64],
            [['idno', 'mobile'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键，递增',
            'user_id' => '用户ID',
            'name' => '用户姓名',
            'idno' => '用户身份证号',
            'mobile' => '用户手机号码',
            'user_type' => '用户类型：1',
            'grade' => '用户等级：1标识铜牌；2标识银牌；3标识金牌；4标识钻石',
            'amount' => '用户可借款额度',
            'last_modify_time' => '最后更新时间',
            'create_time' => '创建时间',
        ];
    }

    public function getWhiteList($user_info)
    {
        
        $where = ['and',['idno' => $user_info['identity']],['mobile' => $user_info['mobile']]];
        $whiteList = $this->find()->where($where)->one();
        if (empty($whiteList)) {
            return 0;
        } 
        return 1;
    }
}
