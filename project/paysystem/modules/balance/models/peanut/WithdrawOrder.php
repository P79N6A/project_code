<?php

namespace app\modules\balance\models\peanut;

use Yii;
use yii\helpers\ArrayHelper;


class WithdrawOrder extends PeanutBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'pea_withdraw_order';
    }

    /**
     * 手续费
     * @param $condition
     * @return int
     */
    public function settleFee($condition)
    {
        if (empty($condition)){
            return 0;
        }
        $where_config = [
            'AND',
            ['>=', 'create_time', ArrayHelper::getValue($condition, 'start_time')],
            ['<=', 'create_time', ArrayHelper::getValue($condition, 'end_time')],
            ['=', 'status', 'SUCCESS'],
        ];
        $total = self::find()->where($where_config)->sum('settle_fee');
        return empty($total) ? 0 : $total;
    }
}