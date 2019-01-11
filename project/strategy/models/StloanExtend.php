<?php

namespace app\models;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "st_loan_extend".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $mth6_dlq_ratio
 * @property string $mth3_dlq7_num
 * @property string $mth3_wst_sys
 * @property string $mth3_dlq_num
 * @property string $wst_dlq_sts
 * @property string $create_time
 */
class StloanExtend extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'st_loan_extend';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id'], 'required'],
            [['loan_id', 'mth3_dlq7_num', 'mth3_wst_sys', 'mth3_dlq_num', 'wst_dlq_sts', 'request_id'], 'integer'],
            [['mth6_dlq_ratio'], 'number'],
            [['create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '请求ID（唯一）',
            'loan_id' => '借款id',
            'mth6_dlq_ratio' => '客户过去6个月有过预期的贷款比例',
            'mth3_dlq7_num' => '客户过去3个月逾期超过7天的贷款数 ',
            'mth3_wst_sys' => '客户过去3个月最坏逾期天数',
            'mth3_dlq_num' => '客户过去3个月逾期次数（按照贷款记）',
            'wst_dlq_sts' => '最长逾期时间',
            'create_time' => '创建时间',
            'request_id' => '请求表关联ID',
        ]; 
    } 

    public function addInfo($postData)
    { 
        // if ($postData['from'] == 4) {
            $postData = $this->transType($postData);
        // }
        $nowtime = date('Y-m-d H:i:s');
        $postData['create_time'] = $nowtime;
        $postData['modify_time'] = $nowtime;
        $error = $this->chkAttributes($postData);
        if ($error) {
            Logger::dayLog('loanextend', 'insert_error', $error,$postData);
            return false;
        }
        return $this->save();
    }

    public function transType($postData)
    {
        foreach ($postData as $k => $val) {
            if ($k == 'mth3_dlq_num' || $k == 'mth3_wst_sys' || $k == 'mth3_dlq7_num' || $k == 'mth6_dlq_ratio') {
                if ($postData[$k] === '') {
                    $postData[$k] = -1;
                }
                $postData[$k] = (float)$postData[$k];
            }
        }
        return $postData;
    }

    public function getLoanExtend($where)
    {
        return $this->find()->where($where)->asArray()->orderBy('id DESC')->one();
    }
}
