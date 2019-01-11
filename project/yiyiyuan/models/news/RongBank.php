<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_rong_bank".
 *
 * @property integer $id
 * @property string $order_no
 * @property string $bank_card
 * @property string $open_bank
 * @property string $user_name
 * @property string $id_number
 * @property string $user_mobile
 * @property string $bank_address
 * @property integer $source
 * @property integer $notify_status
 * @property string $last_modify_time
 * @property string $create_time
 */
class RongBank extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_rong_bank';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_no'], 'required'],
            [['source', 'notify_status'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['order_no', 'open_bank', 'id_number', 'user_mobile', 'bank_address'], 'string', 'max' => 20],
            [['bank_card'], 'string', 'max' => 64],
            [['user_name'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_no' => 'Order No',
            'bank_card' => 'Bank Card',
            'open_bank' => 'Open Bank',
            'user_name' => 'User Name',
            'id_number' => 'Id Number',
            'user_mobile' => 'User Mobile',
            'bank_address' => 'Bank Address',
            'source' => 'Source',
            'notify_status' => 'Notify Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }

    public function saveRongBank($data)
    {
        $cur_time = date('Y-m-d H:i:s');
        $postData = [
            'order_no' => empty($data['order_no']) ? '' : $data['order_no'] , //r360订单编号',
            'bank_card' => empty($data['bank_card']) ? '' : $data['bank_card'], //绑卡卡号',
            'open_bank' => empty($data['open_bank']) ? '' : $data['open_bank'], // 绑卡开户行',
            'user_name' => empty($data['user_name']) ? '' : $data['user_name'], // 姓名,
            'id_number' => empty($data['id_number']) ? '' : $data['id_number'], // 身份证号',
            'user_mobile' => empty($data['user_mobile']) ? '' : $data['user_mobile'], //手机号',
            'bank_address' => empty($data['bank_address']) ? '' : $data['bank_address'], //开户行地址',
            'source' => empty($data['source']) ? '' : $data['source'], //借款来源',
            'notify_status' => 1, //通知状态',
            'last_modify_time' => $cur_time, // 最后修改时间',
            'create_time' =>  $cur_time, //创建时间',
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function updateRongBank($notify_status) {
        $cur_time = date('Y-m-d H:i:s');
        $postData = [
            'notify_status' => $notify_status,
            'last_modify_time' => $cur_time, //最后修改时间
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function updateRongBankInfo($data)
    {
        $cur_time = date('Y-m-d H:i:s');
        $postData = [
            'order_no' => empty($data['order_no']) ? '' : $data['order_no'] , //r360订单编号',
            'bank_card' => empty($data['bank_card']) ? '' : $data['bank_card'], //绑卡卡号',
            'open_bank' => empty($data['open_bank']) ? '' : $data['open_bank'], // 绑卡开户行',
            'user_name' => empty($data['user_name']) ? '' : $data['user_name'], // 姓名,
            'id_number' => empty($data['id_number']) ? '' : $data['id_number'], // 身份证号',
            'user_mobile' => empty($data['user_mobile']) ? '' : $data['user_mobile'], //手机号',
            'bank_address' => empty($data['bank_address']) ? '' : $data['bank_address'], //开户行地址',
            'source' => empty($data['source']) ? '' : $data['source'], //借款来源',
            'notify_status' => 1, //通知状态',
            'last_modify_time' => $cur_time, // 最后修改时间',
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }
}
