<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_rong_loan".
 *
 * @property string $id
 * @property string $mobile
 * @property string $loan_id
 * @property string $r_loan_id
 * @property string $application_amount
 * @property integer $application_term
 * @property integer $source
 * @property string $last_modify_time
 * @property string $create_time
 */

class RongLoan extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_rong_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {

        return [
            [['loan_id', 'r_loan_id', 'application_amount'], 'required'],
            [['loan_id', 'application_term', 'source'], 'integer'],
            [['application_amount'], 'number'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['mobile', 'device_type', 'r_loan_id'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'loan_id' => 'Loan ID',
            'r_loan_id' => 'R Loan ID',
            'application_amount' => 'Application Amount',
            'application_term' => 'Application Term',
            'source' => 'Source',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'device_type' => 'Device_type',
        ];
    }

    /**
     * 保存数据
     * @param array $data
     * @return bool
     */

    public function saveRongLoan(array $data)
    {
        $cur_time = date('Y-m-d H:i:s');
        $postData = [
            'mobile'=>empty($data['mobile']) ? "" : $data['mobile'], //用户手机号码
            'loan_id' => 0, //借款id
            'r_loan_id' => empty($data['r_loan_id']) ? "" : $data['r_loan_id'], //r360借款id
            'application_amount' => empty($data['application_amount']) ? 0 : $data['application_amount'], //借款金额
            'application_term' => empty($data['application_term']) ? 0 : $data['application_term'], //借款天数
            'source' => isset($data['source']) ? $data['source'] : 7, //借款来源
            'last_modify_time' => $cur_time, //最后修改时间
            'create_time' => $cur_time, //创建时间
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    /**
     * 更新一亿元的loan_id
     * @param type $loan_id
     * @return boolean
     */
    public function updateRongLoan($loan_id) {
        $cur_time = date('Y-m-d H:i:s');
        $postData = [
            'loan_id' => $loan_id,
            'last_modify_time' => $cur_time, //最后修改时间
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

        /**
         * 更新一亿元的device_type
         * @param string $device
         * @return bool
         */
    public function updateRongLoanDevice($device = 'ios') {
        $cur_time = date('Y-m-d H:i:s');
        $postData = [
            'device_type' => $device,
            'last_modify_time' => $cur_time, //最后修改时间
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }
}
