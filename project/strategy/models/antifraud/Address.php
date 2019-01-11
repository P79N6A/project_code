<?php

namespace app\models\antifraud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "af_address".
 * db通讯录
 * @property string $id
 * @property string $request_id
 * @property string $user_id
 * @property integer $addr_count
 * @property integer $addr_parents_count
 * @property integer $addr_phones_nodups
 * @property integer $addr_phones_dups
 * @property integer $addr_collection_count
 * @property integer $addr_loan_count
 * @property integer $addr_gamble_count
 * @property integer $addr_father_count
 * @property integer $addr_mother_count
 * @property integer $addr_colleague_count
 * @property integer $addr_company_count
 * @property integer $addr_name_invalids
 * @property integer $addr_myphone_count
 * @property integer $addr_tel_count
 * @property integer $addr_relative_count
 * @property integer $addr_contacts_count
 * @property string $create_time
 */
class Address extends BaseDBModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_address';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['request_id', 'user_id', 'addr_count', 'addr_parents_count', 'addr_phones_nodups', 'addr_phones_dups', 'addr_collection_count', 'addr_loan_count', 'addr_gamble_count', 'addr_father_count', 'addr_mother_count', 'addr_colleague_count', 'addr_company_count', 'addr_name_invalids', 'addr_myphone_count', 'addr_tel_count', 'addr_relative_count', 'addr_contacts_count'], 'integer'],
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
            'addr_count' => '通讯录总量',
            'addr_parents_count' => '通讯录中多个命名为妈，爸等亲属联系方式',
            'addr_phones_nodups' => '去重后手机号数量',
            'addr_phones_dups' => '通讯录中电话号码重复',
            'addr_collection_count' => '催收字样相关联系人过多;',
            'addr_loan_count' => '贷款字样相关联系人过多;',
            'addr_gamble_count' => '赌博字样相关号码数量过多',
            'addr_father_count' => '通讯录“爸”字样的次数',
            'addr_mother_count' => '通讯录“妈”字样的次数',
            'addr_colleague_count' => '通讯录中无“同事”字样',
            'addr_company_count' => '通讯录中无“公司”字样',
            'addr_name_invalids' => '通讯录中命名异常过多',
            'addr_myphone_count' => '本人手机号出现在通讯录中',
            'addr_tel_count' => '通讯录中固定电话个数过低',
            'addr_relative_count' => '通讯录与亲属联系人匹配度',
            'addr_contacts_count' => '通讯录与常用联系人匹配度',
            'create_time' => '记录创建时间',
        ];
    }

    public function getAddress($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v);
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }

    public function getCreditData($where,$select = '*')
    {
        $select = explode(',',$select);
        $res =  $this->find()->select($select)->where($where)->Asarray()->orderby('id DESC')->one();
        foreach ($select as $k => $v) {
            $val = ArrayHelper::getValue($res,$v);
            $res[$v] = $val ? intval($val) : 0;
        }
        return $res;
    }
}
