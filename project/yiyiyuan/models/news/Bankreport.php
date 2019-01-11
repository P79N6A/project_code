<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_user_bank_report".
 *
 * @property string $loan_id
 * @property string $pcd_tot_m6_num
 * @property string $pcd_tot_m6_night_num
 * @property string $pcd_tot_m6_mcnt
 * @property string $pcd_avg_m6_pay
 * @property string $pcd_avg_m6_night_pay
 * @property string $pcd_max_m6_num
 * @property string $pcd_max_m6_pay
 * @property string $pcd_max_m6_pvn
 * @property string $pcd_consec_m6_mcnt
 * @property string $pcd_consec_m6_avg_mpay
 * @property string $pcd_xconsec_m6_no_mcnt
 * @property string $pcd_xconsec_m6_mcnt
 * @property string $pcd_xconsec_m6_avg_mpay
 * @property string $pcd_m6_if_health
 * @property string $pcd_m6_if_lexury
 * @property string $pcd_m6_if_tour
 * @property string $pcd_m6_if_house
 * @property string $pcd_m6_if_fin
 * @property string $pcd_m6_if_ent
 * @property string $pcd_m6_if_flight
 * @property string $pcd_m6_if_car
 * @property string $pcd_m6_if_wholesale
 * @property string $pcd_m6_if_meal
 * @property string $pcd_m6_if_ins
 * @property string $bin_type
 */
class Bankreport extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_user_bank_report';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'bin_type'], 'required'],
            [['loan_id'], 'integer'],
            [['pcd_tot_m6_num', 'pcd_tot_m6_night_num', 'pcd_tot_m6_mcnt', 'pcd_avg_m6_pay', 'pcd_avg_m6_night_pay', 'pcd_max_m6_num', 'pcd_max_m6_pay', 'pcd_max_m6_pvn', 'pcd_consec_m6_mcnt', 'pcd_consec_m6_avg_mpay', 'pcd_xconsec_m6_no_mcnt', 'pcd_xconsec_m6_mcnt', 'pcd_xconsec_m6_avg_mpay', 'pcd_m6_if_health', 'pcd_m6_if_lexury', 'pcd_m6_if_tour', 'pcd_m6_if_house', 'pcd_m6_if_fin', 'pcd_m6_if_ent', 'pcd_m6_if_flight', 'pcd_m6_if_car', 'pcd_m6_if_wholesale', 'pcd_m6_if_meal', 'pcd_m6_if_ins', 'bin_type'], 'string', 'max' => 10]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'loan_id' => 'Loan ID',
            'pcd_tot_m6_num' => 'Pcd Tot M6 Num',
            'pcd_tot_m6_night_num' => 'Pcd Tot M6 Night Num',
            'pcd_tot_m6_mcnt' => 'Pcd Tot M6 Mcnt',
            'pcd_avg_m6_pay' => 'Pcd Avg M6 Pay',
            'pcd_avg_m6_night_pay' => 'Pcd Avg M6 Night Pay',
            'pcd_max_m6_num' => 'Pcd Max M6 Num',
            'pcd_max_m6_pay' => 'Pcd Max M6 Pay',
            'pcd_max_m6_pvn' => 'Pcd Max M6 Pvn',
            'pcd_consec_m6_mcnt' => 'Pcd Consec M6 Mcnt',
            'pcd_consec_m6_avg_mpay' => 'Pcd Consec M6 Avg Mpay',
            'pcd_xconsec_m6_no_mcnt' => 'Pcd Xconsec M6 No Mcnt',
            'pcd_xconsec_m6_mcnt' => 'Pcd Xconsec M6 Mcnt',
            'pcd_xconsec_m6_avg_mpay' => 'Pcd Xconsec M6 Avg Mpay',
            'pcd_m6_if_health' => 'Pcd M6 If Health',
            'pcd_m6_if_lexury' => 'Pcd M6 If Lexury',
            'pcd_m6_if_tour' => 'Pcd M6 If Tour',
            'pcd_m6_if_house' => 'Pcd M6 If House',
            'pcd_m6_if_fin' => 'Pcd M6 If Fin',
            'pcd_m6_if_ent' => 'Pcd M6 If Ent',
            'pcd_m6_if_flight' => 'Pcd M6 If Flight',
            'pcd_m6_if_car' => 'Pcd M6 If Car',
            'pcd_m6_if_wholesale' => 'Pcd M6 If Wholesale',
            'pcd_m6_if_meal' => 'Pcd M6 If Meal',
            'pcd_m6_if_ins' => 'Pcd M6 If Ins',
            'bin_type' => 'Bin Type',
        ];
    }


    public function addList($condition, $loan_id,$type)
    {
        if (empty($condition)) return false;
        $attr = $this->attributeLabels();
        $this->isNewRecord = true;

        foreach($attr as $key => $val ){
            if( !array_key_exists($key, $condition) )  continue ;  
            $this->$key = $condition[$key];
        }
        $this->loan_id = $loan_id;
        $this->bin_type = $type;

        $ret = $this->save(false);
        return $ret;
    }

}
