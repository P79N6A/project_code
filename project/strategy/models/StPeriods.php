<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "st_periods".
 *
 * @property string $id
 * @property integer $aid
 * @property string $request_id
 * @property string $user_id
 * @property string $realname
 * @property string $mobile
 * @property string $identity
 * @property string $quota
 * @property integer $success_num
 * @property string $mth6_dlq_ratio
 * @property string $mth3_dlq7_num
 * @property string $mth3_wst_sys
 * @property string $mth3_dlq_num
 * @property string $wst_dlq_sts
 * @property integer $is_fq
 * @property string $create_time
 */
class StPeriods extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_periods';
    }

    /**
     * @inheritdoc
     */
    public function rules() 
    { 
        return [
            [['aid', 'request_id', 'user_id', 'success_num', 'mth3_dlq7_num', 'mth3_wst_sys', 'mth3_dlq_num', 'wst_dlq_sts', 'is_fq', 'type'], 'integer'],
            [['request_id', 'user_id', 'quota'], 'required'],
            [['quota', 'mth6_dlq_ratio'], 'number'],
            [['create_time'], 'safe'],
            [['realname', 'mobile', 'identity'], 'string', 'max' => 20]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => '请求ID（唯一）',
            'aid' => '产品类型；1 一亿元；8 豆荚贷',
            'request_id' => '请求表关联ID',
            'user_id' => '用户id',
            'realname' => '用户姓名',
            'mobile' => '用户手机号',
            'identity' => '用户身份证号',
            'quota' => '用户可借额度',
            'success_num' => '借款成功次数',
            'mth6_dlq_ratio' => '客户过去6个月有过预期的贷款比例',
            'mth3_dlq7_num' => '客户过去3个月逾期超过7天的贷款数 ',
            'mth3_wst_sys' => '客户过去3个月最坏逾期天数',
            'mth3_dlq_num' => '客户过去3个月逾期次数（按照贷款记）',
            'wst_dlq_sts' => '最长逾期时间',
            'is_fq' => '是否是分期用户',
            'create_time' => '创建时间',
            'type' => '1,初贷；2,复贷',
        ]; 
    } 

    public function addPeriodsInfo($postData)
    {
        $saveData = [
            'aid' => isset($postData['aid']) ? $postData['aid'] : 1,
            'request_id' => isset($postData['request_id']) ? $postData['request_id'] : 0,
            'user_id' => isset($postData['user_id']) ? $postData['user_id'] : 0,
            'realname' => isset($postData['realname']) ? $postData['realname'] : '',
            'mobile' => isset($postData['mobile']) ? $postData['mobile'] : '',
            'identity' => isset($postData['identity']) ? $postData['identity'] : '',
            'quota' => isset($postData['quota']) ? $postData['quota'] : 0,
            'success_num' => isset($postData['success_num']) ? $postData['success_num'] : 0,
            'mth6_dlq_ratio' => isset($postData['mth6_dlq_ratio']) ? $postData['mth6_dlq_ratio'] : 0,
            'mth3_dlq7_num' => isset($postData['mth3_dlq7_num']) ? $postData['mth3_dlq7_num'] : 0,
            'mth3_wst_sys' => isset($postData['mth3_wst_sys']) ? $postData['mth3_wst_sys'] : 0,
            'mth3_dlq_num' => isset($postData['mth3_dlq_num']) ? $postData['mth3_dlq_num'] : 0,
            'wst_dlq_sts' => isset($postData['wst_dlq_sts']) ? $postData['wst_dlq_sts'] : 0,
            'is_fq' => isset($postData['is_fq']) ? $postData['is_fq'] : 0,
            'type' => isset($postData['type']) ? $postData['type'] : 0,
        ];
        //数据类型转换
        $nowtime = date('Y-m-d H:i:s');
        $saveData['create_time'] = $nowtime;
        $error = $this->chkAttributes($saveData);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
