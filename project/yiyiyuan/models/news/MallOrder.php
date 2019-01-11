<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_mall_order".
 *
 * @property string $id
 * @property string $order_id
 * @property string $user_id
 * @property string $a_id
 * @property string $goods_id
 * @property string $goods_content
 * @property integer $from
 * @property integer $status
 * @property string $money
 * @property string $create_time
 * @property string $last_modify_time
 * @property integer $version
 */
class MallOrder extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_mall_order';
    }

    public function getGoods() {
        return $this->hasOne(Goods_list::className(), ['id' => 'goods_id']);
    }

    public function getAddress() {
        return $this->hasOne(MallOrderAddress::className(), ['order_id' => 'order_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'user_id', 'a_id', 'goods_id', 'goods_content', 'status', 'money'], 'required'],
            [['user_id', 'a_id', 'goods_id', 'from', 'status', 'version'], 'integer'],
            [['money'], 'number'],
            [['create_time', 'last_modify_time'], 'safe'],
            [['order_id'], 'string', 'max' => 32],
            [['goods_content'], 'string', 'max' => 510]
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
            'user_id' => 'User ID',
            'a_id' => 'A ID',
            'goods_id' => 'Goods ID',
            'goods_content' => 'Goods Content',
            'from' => 'From',
            'status' => 'Status',
            'money' => 'Money',
            'create_time' => 'Create Time',
            'last_modify_time' => 'Last Modify Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    public function addOrder($data){
        if (!is_array($data) || empty($data)) {
            return false;
        }
        $time = date('Y-m-d H:i:s');
        $condition = $data;
        $condition['status']            = 0;
        $condition['create_time']       = $time;
        $condition['last_modify_time']  = $time;
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }

        return $this->save();
    }

    /**
     * 通过order_id获取订单
     * @param $orderId
     * @return array|null|\yii\db\ActiveRecord
     */
    public function getGoodsOrderByOrderId($orderId){
        if(!$orderId) {
            return null;
        }
        return self::find()->where(['order_id'=>$orderId])->one();
    }

    /**
     * 通过用户id获取商品订单
     * @param $userId
     * @return array|null|\yii\db\ActiveRecord[]
     */
    public function getGoodsListByUserId($userId){
        $userId = intval($userId);
        if(!$userId) {
            return null;
        }
        return self::find()->where(['user_id'=>$userId])->orderBy('create_time desc')->asArray()->all();
    }

    /**
     * 支付中
     * @return bool
     */
    public function doing() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status = 1;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 支付成功
     * @return bool
     */
    public function success() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status = 2;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * 已完成
     * @return bool
     */
    public function over() {
        try {
            $this->last_modify_time = date('Y-m-d H:i:s');
            $this->status = 3;
            $result = $this->save();
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }



}
