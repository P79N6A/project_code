<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_loan_pic".
 *
 * @property string $id
 * @property string $loan_id
 * @property string $user_id
 * @property integer $request_id
 * @property integer $template_id
 * @property integer $status
 * @property string $create_time
 * @property string $path
 * @property string $last_modify_time
 * @property integer $version
 */
class Loan_pic extends \app\models\BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_pic';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['loan_id', 'user_id', 'create_time', 'last_modify_time'], 'required'],
            [['loan_id', 'user_id', 'template_id', 'request_id', 'status', 'version'], 'integer'],
            [['start_date', 'order_time', 'pay_time', 'create_time', 'last_modify_time'], 'safe'],
            [['amount', 'goods_price'], 'number'],
            [['realname', 'sex', 'mobile', 'address_pro', 'address_city', 'goods_status', 'order_number', 'page_time'], 'string', 'max' => 32],
            [['full_address'], 'string', 'max' => 256],
            [['goods_tag', 'path1', 'path2', 'path3'], 'string', 'max' => 128],
            [['trade_number'], 'string', 'max' => 64]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'realname' => 'Realname',
            'sex' => 'Sex',
            'mobile' => 'Mobile',
            'start_date' => 'Start Date',
            'amount' => 'Amount',
            'address_pro' => 'Address Pro',
            'address_city' => 'Address City',
            'full_address' => 'Full Address',
            'template_id' => 'Template ID',
            'goods_tag' => 'Goods Tag',
            'goods_status' => 'Goods Status',
            'goods_price' => 'Goods Price',
            'order_number' => 'Order Number',
            'trade_number' => 'Trade Number',
            'order_time' => 'Order Time',
            'pay_time' => 'Pay Time',
            'page_time' => 'Page Time',
            'request_id' => 'Request ID',
            'status' => 'Status',
            'create_time' => 'Create Time',
            'path1' => 'Path1',
            'path2' => 'Path2',
            'path3' => 'Path3',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock() {
        return "version";
    }

    public function getByLoanId($loan_id) {
        if (!is_numeric($loan_id)) {
            return NULL;
        }
        $oLoanpic = self::find()->where(['loan_id' => $loan_id])->one();
        return $oLoanpic;
    }

    public function getByUserId($user_id) {
        if (!is_numeric($user_id)) {
            return NULL;
        }
        $oLoanpic = self::find()->where(['user_id' => $user_id])->andWhere(['not in', 'status', [6,8]])->all();
        return $oLoanpic;
    }

    public function savePic($path1, $path2 = '', $path3 = '') {
        if (empty($path1)) {
            return FALSE;
        }
        $data['path1'] = $path1;
        if (!empty($path2)) {
            $data['path2'] = $path2;
        }
        if (!empty($path3)) {
            $data['path3'] = $path3;
        }
        $data['status'] = 6;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function lockAll($ids) {
        return self::updateAll(['status' => 100], ['id' => $ids]);
    }

    public function lock() {
        $data['status'] = 100;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function saveFail() {
        $data['status'] = 11;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function savRepeat() {
        $data['status'] = 3;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function UpdatetStatus($status) {
        $data['status'] = $status;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

}
