<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_goods_order".
 *
 * @property string $id
 * @property string $order_id
 * @property string $goods_id
 * @property string $loan_id
 * @property string $user_id
 * @property integer $number
 * @property integer $fee
 * @property string $order_amount
 * @property string $order_status
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */

/**
 * 1.新增一条数据
 * 2.更新一条数据
 * 3.根据loan_id查询分期订单
 */
class GoodsOrder extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_goods_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'goods_id', 'loan_id', 'user_id', 'number', 'fee', 'order_amount', 'order_status', 'create_time', 'last_modify_time'], 'required'],
            [['goods_id', 'loan_id', 'user_id', 'number', 'fee', 'version'], 'integer'],
            [['order_amount'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['order_id'], 'string', 'max' => 64],
            [['order_status'], 'string', 'max' => 16]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'goods_id' => 'Goods ID',
            'loan_id' => 'Loan ID',
            'user_id' => 'User ID',
            'number' => 'Number',
            'fee' => 'Fee',
            'order_amount' => 'Order Amount',
            'order_status' => 'Order Status',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
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

    public function getLoan()
    {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'loan_id']);
    }

    public function getLoanextend()
    {
        return $this->hasOne(User_loan_extend::className(), ['loan_id' => 'loan_id']);
    }

    /**
     * 1.新增一条数据
     * @param $condition
     * @return bool
     */
    public function addGoodsOrder($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $data = $condition;
        $data['order_status'] = 'INIT';
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 2.更新一条数据
     * @param $condition
     * @return bool
     */
    public function update_list($condition)
    {
        if (!is_array($condition) || empty($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 3.根据loan_id查询分期订单
     * @param $loanId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getLoanByLoanId($loanId)
    {
        if (empty($loanId) || !is_numeric($loanId)) {
            return NULL;
        }
        return self::find()->where(['loan_id' => $loanId])->one();
    }

    //批量锁定
    public function updateAllLock($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return false;
        }
        return self::updateAll(['order_status' => 'LOCK'], ['id' => $ids, 'order_status' => 'INIT']);
    }

    //分期订单改为驳回
    public function updateReject()
    {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->order_status = 'REJECT';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //分期订单改为成功
    public function updateSuccess()
    {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->order_status = 'SUCCESS';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //分期订单改为失败
    public function updateFail()
    {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->order_status = 'FAIL';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //分期订单改为结束
    public function updateFinished()
    {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->order_status = 'FINISHED';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //分期订单改为锁定
    public function updateLock()
    {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->order_status = 'LOCK';
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    //获取init的数据列表
    public function listInit($stime, $limit = 200)
    {//@todo with方式连user_loan表 和user_loan_extend
        $where = [
            'AND',
            ['>=', GoodsOrder::tableName() . '.create_time', $stime],
            [GoodsOrder::tableName() . '.order_status' => 'INIT'],
            ['IN', User_loan_extend::tableName() . '.status', ['SUCCESS', 'REJECT']],
        ];
        return self::find()->joinWith('loanextend', true, 'LEFT JOIN')->where($where)->limit($limit)->all();
    }

}
