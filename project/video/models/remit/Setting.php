<?php

namespace app\models\remit;

use Yii;

/**
 * This is the model class for table "rt_setting".
 *
 * @property integer $id
 * @property string $aid
 * @property string $day_max_mount
 * @property string $create_time
 */
class Setting extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rt_setting';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'day_max_mount', 'create_time'], 'required'],
            [['aid'], 'integer'],
            [['day_max_mount'], 'number'],
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
            'aid' => '商户编号',
            'day_max_mount' => '商户最大日限额',
            'create_time' => '创建时间',
        ];
    }
    /**
     * 获取一日最大限额
     * @param  int $aid 应用id
     * @return  float
     */
    public function getMaxDay($aid){
        $aid = intval($aid);
        $data = static::find()->where(['aid'=>$aid])->one();
        // 若未设置, 那么当成限额0处理,即总是超限
        if( empty($data) ){
            return 0;
        }

        return $data['day_max_mount'];
    }
    /**
     * 当日可出款金额
     * @param  [type] $aid [description]
     * @return [type]      [description]
     */
    public function getDayRestMoney($aid, $remit_table='remit'){
        //1 一日最大限额
        $aid = intval($aid);
        $day_max_amount = $this->getMaxDay($aid);
        // $day_max_amount <= 0
        if( bccomp( $day_max_amount, 0, 4 ) !== 1  ){
            return 0;
        }

        //2 当日实际总额
        if($remit_table == 'rbremit') {
            $oRemit = new \app\models\rongbao\Remit;
        }elseif ($remit_table == 'llremit'){
            $oRemit = new \app\models\lian\LLRemit;
        }elseif($remit_table == 'bfremit'){
            $oRemit = new \app\models\baofoo\BfRemit;
        }elseif($remit_table == 'cjremit'){
            $oRemit = new \app\models\cjt\CjtRemit;
        }elseif($remit_table == 'rbcredit'){
            $oRemit = new \app\models\rbcredit\RbCreditRemit;
        }else{
            $oRemit = new Remit;
        }
        
        $day_now_amount = $oRemit -> getDayMoney($aid);

        //3 可出金额
        $rest_amount = $day_max_amount - $day_now_amount;
        // $rest_amount <= 0
        if( bccomp( $rest_amount, 0, 4 ) !== 1  ){
            return 0;
        }
        return  $rest_amount;
    }
    /**
     * 是否超过当日上限
     * @param  [type]  $aid [description]
     * @return boolean      [description]
     */
    public function isDayMax($aid, $amount, $remit_table='remit'){
        $aid = intval($aid);
        // 一日可出金额
        $rest_money = $this->getDayRestMoney($aid, $remit_table);
        // $rest_money <= 0
        if( bccomp( $rest_money, 0, 4 ) !== 1  ){
            return true; // 达上限
        }

        //$amount > $rest_money 表示达上限
        return  bccomp( $amount, $rest_money, 4 ) === 1;
    }
}
