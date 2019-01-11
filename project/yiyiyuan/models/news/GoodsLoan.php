<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_loan".
 *
 * @property string $id
 * @property string $loan_id
 * @property integer $loan_status
 * @property string $status
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class GoodsLoan extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_loan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'loan_status', 'version'], 'integer'],
            [['status', 'last_modify_time', 'create_time'], 'required'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['status'], 'string', 'max' => 16],
            [['loan_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'loan_status' => 'Loan Status',
            'status' => 'Status',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    /**
     * 乐观所版本号
     * * */
    public function optimisticLock()
    {
        return "version";
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     */
    public function addGoodsLoan($loanId, $loanStatus)
    {
        if (!is_numeric($loanId) || empty($loanId)) {
            return false;
        }
        if (!in_array($loanStatus, [1, 2])) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data['loan_id'] = $loanId;
        $data['loan_status'] = $loanStatus;
        $data['status'] = 'INIT';
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        try {
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 1.新增记录
     * @param $condition
     * @return bool
     */
    public function addSuccessGoodsLoan($userRemitListObj)
    {
        if (!is_object($userRemitListObj) || empty($userRemitListObj) || empty($userRemitListObj->loan)) {
            return false;
        }
        if ($userRemitListObj->remit_status != 'SUCCESS') {
            return false;
        }
        if (!in_array($userRemitListObj->loan->business_type, [5, 6])) {
            return false;
        }
        $result = $this->addGoodsLoan($userRemitListObj->loan_id, 1);
        return $result;
    }

    //批量锁定
    public function updateAllLock($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        return self::updateAll(['status' => 'LOCK'], ['id' => $ids, 'status' => 'INIT']);
    }

    //单条锁定
    public function updateLock()
    {
        try {
            $this->status = 'LOCK';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //改为成功
    public function updateSuccess()
    {
        try {
            $this->status = 'SUCCESS';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //改为失败
    public function updateFail()
    {
        try {
            $this->status = 'FAIL';
            $this->last_modify_time = date('Y-m-d H:i:s');
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //获取init的数据列表
    public function listInit($limit = 200)
    {
        $start_date = date('Y-m-d H:i:00', strtotime('-600 minutes'));
        $end_date = date('Y-m-d H:i:00');
        $where = [
            'AND',
            ['>=', 'create_time', $start_date],
            ['<', 'create_time', $end_date],
            ['status' => 'INIT'],
        ];
        return self::find()->where($where)->limit($limit)->all();
    }
}
