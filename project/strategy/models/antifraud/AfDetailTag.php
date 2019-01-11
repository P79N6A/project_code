<?php

namespace app\models\antifraud;

use Yii;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "af_detail_tag".
 *
 * @property string $id
 * @property integer $aid
 * @property string $user_id
 * @property string $loan_id
 * @property string $request_id
 * @property integer $detail_saynum
 * @property integer $detail_telnum
 * @property string $advertis
 * @property string $express
 * @property string $harass
 * @property string $house_agent
 * @property string $cheat
 * @property string $company_tel
 * @property string $invite
 * @property string $taxi
 * @property string $education
 * @property string $insurance
 * @property string $ring
 * @property string $service_tel
 * @property string $delinquency
 * @property string $modify_time
 * @property string $create_time
 */
class AfDetailTag extends BaseDBModel
{
    private $all_data_key_list;
    private $detail_key_list;
    public function __construct(){
        $this->all_data_key_list = [
                            'advertis',
                            'express',
                            'harass',
                            'house_agent',
                            'cheat',
                            'taxi',
                            'ring',
                            'insurance',
                            'company_tel'
                        ];
        $this->detail_key_list = [
                            'aeavy_number_lable',
                            'weight_loss_label',
                            'aeavy_number_proportion',
                            'weight_loss_proportion',
                            'aeavy_number_sign',
                            'weight_loss_sign',
                        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'af_detail_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['aid', 'user_id', 'loan_id', 'request_id', 'detail_saynum', 'detail_telnum'], 'integer'],
            [['modify_time', 'create_time'], 'required'],
            [['modify_time', 'create_time'], 'safe'],
            [['advertis', 'express', 'harass', 'house_agent', 'cheat', 'company_tel', 'invite', 'taxi', 'education', 'insurance', 'ring', 'service_tel', 'delinquency'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => '业务ID',
            'user_id' => '用户ID',
            'loan_id' => '借款ID',
            'request_id' => '请求处理id',
            'detail_saynum' => '详单通话次数',
            'detail_telnum' => '详单通话号码数',
            'advertis' => '广告推销-存json',
            'express' => '快递送餐-存json',
            'harass' => '骚扰电话-存json',
            'house_agent' => '房产中介-存json',
            'cheat' => '疑似欺诈-存json',
            'company_tel' => '企业电话-存json',
            'invite' => '招聘猎头-存json',
            'taxi' => '出租车-存json',
            'education' => '教育培训-存json',
            'insurance' => '保险理财-存json',
            'ring' => '响一声-存json',
            'service_tel' => '客服电话-存json',
            'delinquency' => '违法犯罪-存json',
            'modify_time' => '修改时间',
            'create_time' => '创建时间',
        ];
    }

    public function getOne($where, $select = '*'){
        return $this->find()->where($where)->select($select)->orderby('id DESC')->one();
    }

    public function getExpress($where){
        $def_data = [
            "aeavy_number_lable"      => 111,
            "weight_loss_label"       => 111,
            "aeavy_number_proportion" => 111,
            "weight_loss_proportion"  => 111,
            "aeavy_number_sign"       => 111,
            "weight_loss_sign"        => 111,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $express_res = $this->getOne($where,'express');
        if (empty($express_res) || empty($express_res['express'])) {
            return $def_data;
        }
        $express_json = ArrayHelper::getValue($express_res,'express','');
        $express = json_decode($express_json,true);
        $ret_arr = [
            "aeavy_number_lable"      => ArrayHelper::getValue($express,'aeavy_number_lable',111),
            "weight_loss_label"       => ArrayHelper::getValue($express,'weight_loss_label',111),
            "aeavy_number_proportion" => ArrayHelper::getValue($express,'aeavy_number_proportion',111),
            "weight_loss_proportion"  => ArrayHelper::getValue($express,'weight_loss_proportion',111),
            "aeavy_number_sign"       => ArrayHelper::getValue($express,'aeavy_number_sign',111),
            "weight_loss_sign"        => ArrayHelper::getValue($express,'weight_loss_sign',111),
        ];
        return $ret_arr;
    }

    public function getDetailTag($user_id){
        if (empty($user_id)) {
            return $this->setAllData();
        }
        $where = ['user_id' => $user_id];
        #query db
        $all_data_db = $this->getOne($where);

        # set all data
        $all_data = $this->setAllData($all_data_db);
        return $all_data;
    }

    public function getDetailTagByWhere($where){
        if (empty($where) || !is_array($where)) {
            return $this->setAllData();
        }
        #query db
        $all_data_db = $this->getOne($where);

        # set all data
        $all_data = $this->setAllData($all_data_db);
        return $all_data;
    }
    private function setAllData($allData = null){
        # set all date
        $all_data = [];
        foreach ($this->all_data_key_list as $field) {
            $value_json = ArrayHelper::getValue($allData,$field,null);
            $value = null;
            if ($value_json) {
                $value = json_decode($value_json,true);
            }
            foreach ($this->detail_key_list as $detail_key) {
                $subvalue = ArrayHelper::getValue($value,$detail_key,0);

                if ($detail_key == 'aeavy_number_proportion') {
                    $detail_key = 'aeavy_number_p';
                }

                if ($detail_key == 'weight_loss_proportion') {
                    $detail_key = 'weight_loss_p';
                }

                $new_key = $field.'_'.$detail_key;
                $all_data[$new_key] = $subvalue;
            }
        }
        return $all_data;
    }
    public function getAdvertis($where){
        $def_data = [
            "advertis_aeavy_number_lable" => 0,
            "advertis_weight_loss_label"  => 0,
            "advertis_aeavy_number_p"     => 0,
            "advertis_weight_loss_p"      => 0,
            "advertis_aeavy_number_sign"  => 0,
            "advertis_weight_loss_sign"   => 0,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $advertis_res = $this->getOne($where,'advertis');
        if (empty($advertis_res) || empty($advertis_res['advertis'])) {
            return $def_data;
        }
        $advertis_json = ArrayHelper::getValue($advertis_res,'advertis','');
        $advertis = json_decode($advertis_json,true);
        $ret_arr = [
            "advertis_aeavy_number_lable" => ArrayHelper::getValue($advertis,'aeavy_number_lable',0),
            "advertis_weight_loss_label"  => ArrayHelper::getValue($advertis,'weight_loss_label',0),
            "advertis_aeavy_number_p"     => ArrayHelper::getValue($advertis,'aeavy_number_proportion',0),
            "advertis_weight_loss_p"      => ArrayHelper::getValue($advertis,'weight_loss_proportion',0),
            "advertis_aeavy_number_sign"  => ArrayHelper::getValue($advertis,'aeavy_number_sign',0),
            "advertis_weight_loss_sign"   => ArrayHelper::getValue($advertis,'weight_loss_sign',0),
        ];
        return $ret_arr;
    }

    public function getHarass($where){
        $def_data = [
            "harass_aeavy_number_lable" => 0,
            "harass_weight_loss_label"  => 0,
            "harass_aeavy_number_p"     => 0,
            "harass_weight_loss_p"      => 0,
            "harass_aeavy_number_sign"  => 0,
            "harass_weight_loss_sign"   => 0,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $harass_res = $this->getOne($where,'harass');
        if (empty($harass_res) || empty($harass_res['harass'])) {
            return $def_data;
        }
        $harass_json = ArrayHelper::getValue($harass_res,'harass','');
        $harass = json_decode($harass_json,true);
        $ret_arr = [
            "harass_aeavy_number_lable" => ArrayHelper::getValue($harass,'aeavy_number_lable',0),
            "harass_weight_loss_label"  => ArrayHelper::getValue($harass,'weight_loss_label',0),
            "harass_aeavy_number_p"     => ArrayHelper::getValue($harass,'aeavy_number_proportion',0),
            "harass_weight_loss_p"      => ArrayHelper::getValue($harass,'weight_loss_proportion',0),
            "harass_aeavy_number_sign"  => ArrayHelper::getValue($harass,'aeavy_number_sign',0),
            "harass_weight_loss_sign"   => ArrayHelper::getValue($harass,'weight_loss_sign',0),
        ];
        return $ret_arr;
    }

    public function getHouseAgent($where){
        $def_data = [
            "house_agent_aeavy_number_lable" => 0,
            "house_agent_weight_loss_label"  => 0,
            "house_agent_aeavy_number_p"     => 0,
            "house_agent_weight_loss_p"      => 0,
            "house_agent_aeavy_number_sign"  => 0,
            "house_agent_weight_loss_sign"   => 0,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $house_agent_res = $this->getOne($where,'house_agent');
        if (empty($house_agent_res) || empty($house_agent_res['house_agent'])) {
            return $def_data;
        }
        $house_agent_json = ArrayHelper::getValue($house_agent_res,'house_agent','');
        $house_agent = json_decode($house_agent_json,true);
        $ret_arr = [
            "house_agent_aeavy_number_lable" => ArrayHelper::getValue($house_agent,'aeavy_number_lable',0),
            "house_agent_weight_loss_label"  => ArrayHelper::getValue($house_agent,'weight_loss_label',0),
            "house_agent_aeavy_number_p"     => ArrayHelper::getValue($house_agent,'aeavy_number_proportion',0),
            "house_agent_weight_loss_p"      => ArrayHelper::getValue($house_agent,'weight_loss_proportion',0),
            "house_agent_aeavy_number_sign"  => ArrayHelper::getValue($house_agent,'aeavy_number_sign',0),
            "house_agent_weight_loss_sign"   => ArrayHelper::getValue($house_agent,'weight_loss_sign',0),
        ];
        return $ret_arr;
    }

        public function getCheat($where){
        $def_data = [
            "cheat_aeavy_number_lable" => 0,
            "cheat_weight_loss_label"  => 0,
            "cheat_aeavy_number_p"     => 0,
            "cheat_weight_loss_p"      => 0,
            "cheat_aeavy_number_sign"  => 0,
            "cheat_weight_loss_sign"   => 0,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $cheat_res = $this->getOne($where,'cheat');
        if (empty($cheat_res) || empty($cheat_res['cheat'])) {
            return $def_data;
        }
        $cheat_json = ArrayHelper::getValue($cheat_res,'cheat','');
        $cheat = json_decode($cheat_json,true);
        $ret_arr = [
            "cheat_aeavy_number_lable" => ArrayHelper::getValue($cheat,'aeavy_number_lable',0),
            "cheat_weight_loss_label"  => ArrayHelper::getValue($cheat,'weight_loss_label',0),
            "cheat_aeavy_number_p"     => ArrayHelper::getValue($cheat,'aeavy_number_proportion',0),
            "cheat_weight_loss_p"      => ArrayHelper::getValue($cheat,'weight_loss_proportion',0),
            "cheat_aeavy_number_sign"  => ArrayHelper::getValue($cheat,'aeavy_number_sign',0),
            "cheat_weight_loss_sign"   => ArrayHelper::getValue($cheat,'weight_loss_sign',0),
        ];
        return $ret_arr;
    }

    public function getTaxi($where){
        $def_data = [
            "taxi_aeavy_number_lable" => 0,
            "taxi_weight_loss_label"  => 0,
            "taxi_aeavy_number_p"     => 0,
            "taxi_weight_loss_p"      => 0,
            "taxi_aeavy_number_sign"  => 0,
            "taxi_weight_loss_sign"   => 0,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $taxi_res = $this->getOne($where,'taxi');
        if (empty($taxi_res) || empty($taxi_res['taxi'])) {
            return $def_data;
        }
        $taxi_json = ArrayHelper::getValue($taxi_res,'taxi','');
        $taxi = json_decode($taxi_json,true);
        $ret_arr = [
            "taxi_aeavy_number_lable" => ArrayHelper::getValue($taxi,'aeavy_number_lable',0),
            "taxi_weight_loss_label"  => ArrayHelper::getValue($taxi,'weight_loss_label',0),
            "taxi_aeavy_number_p"     => ArrayHelper::getValue($taxi,'aeavy_number_proportion',0),
            "taxi_weight_loss_p"      => ArrayHelper::getValue($taxi,'weight_loss_proportion',0),
            "taxi_aeavy_number_sign"  => ArrayHelper::getValue($taxi,'aeavy_number_sign',0),
            "taxi_weight_loss_sign"   => ArrayHelper::getValue($taxi,'weight_loss_sign',0),
        ];
        return $ret_arr;
    }

        public function getRing($where){
        $def_data = [
            "ring_aeavy_number_lable" => 0,
            "ring_weight_loss_label"  => 0,
            "ring_aeavy_number_p"     => 0,
            "ring_weight_loss_p"      => 0,
            "ring_aeavy_number_sign"  => 0,
            "ring_weight_loss_sign"   => 0,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $ring_res = $this->getOne($where,'ring');
        if (empty($ring_res) || empty($ring_res['ring'])) {
            return $def_data;
        }
        $ring_json = ArrayHelper::getValue($ring_res,'ring','');
        $ring = json_decode($ring_json,true);
        $ret_arr = [
            "ring_aeavy_number_lable" => ArrayHelper::getValue($ring,'aeavy_number_lable',0),
            "ring_weight_loss_label"  => ArrayHelper::getValue($ring,'weight_loss_label',0),
            "ring_aeavy_number_p"     => ArrayHelper::getValue($ring,'aeavy_number_proportion',0),
            "ring_weight_loss_p"      => ArrayHelper::getValue($ring,'weight_loss_proportion',0),
            "ring_aeavy_number_sign"  => ArrayHelper::getValue($ring,'aeavy_number_sign',0),
            "ring_weight_loss_sign"   => ArrayHelper::getValue($ring,'weight_loss_sign',0),
        ];
        return $ret_arr;
    }

    public function getInsurance($where){
        $def_data = [
            "insurance_aeavy_number_lable" => 0,
            "insurance_weight_loss_label"  => 0,
            "insurance_aeavy_number_p"     => 0,
            "insurance_weight_loss_p"      => 0,
            "insurance_aeavy_number_sign"  => 0,
            "insurance_weight_loss_sign"   => 0,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $insurance_res = $this->getOne($where,'insurance');
        if (empty($insurance_res) || empty($insurance_res['insurance'])) {
            return $def_data;
        }
        $insurance_json = ArrayHelper::getValue($insurance_res,'ring','');
        $insurance = json_decode($insurance_json,true);
        $ret_arr = [
            "insurance_aeavy_number_lable" => ArrayHelper::getValue($ring,'aeavy_number_lable',0),
            "insurance_weight_loss_label"  => ArrayHelper::getValue($ring,'weight_loss_label',0),
            "insurance_aeavy_number_p"     => ArrayHelper::getValue($ring,'aeavy_number_proportion',0),
            "insurance_weight_loss_p"      => ArrayHelper::getValue($ring,'weight_loss_proportion',0),
            "insurance_aeavy_number_sign"  => ArrayHelper::getValue($ring,'aeavy_number_sign',0),
            "insurance_weight_loss_sign"   => ArrayHelper::getValue($ring,'weight_loss_sign',0),
        ];
        return $ret_arr;
    }
    public function getCompanyTel($where)
    {
        $def_data = [
            "company_aeavy_number_lable" => 0,
            "company_weight_loss_label"  => 0,
            "company_aeavy_number_p"     => 0,
            "company_weight_loss_p"      => 0,
            "company_aeavy_number_sign"  => 0,
            "company_weight_loss_sign"   => 0,
        ];
        if (empty($where) || !is_array($where)) {
            return $def_data;
        }
        $company_res = $this->getOne($where,'company');
        if (empty($company_res) || empty($company_res['company'])) {
            return $def_data;
        }
        $company_json = ArrayHelper::getValue($company_res,'ring','');
        $company = json_decode($company_json,true);
        $ret_arr = [
            "company_aeavy_number_lable" => ArrayHelper::getValue($ring,'aeavy_number_lable',0),
            "company_weight_loss_label"  => ArrayHelper::getValue($ring,'weight_loss_label',0),
            "company_aeavy_number_p"     => ArrayHelper::getValue($ring,'aeavy_number_proportion',0),
            "company_weight_loss_p"      => ArrayHelper::getValue($ring,'weight_loss_proportion',0),
            "company_aeavy_number_sign"  => ArrayHelper::getValue($ring,'aeavy_number_sign',0),
            "company_weight_loss_sign"   => ArrayHelper::getValue($ring,'weight_loss_sign',0),
        ];
        return $ret_arr;
    }
}
