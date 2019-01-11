<?php

namespace app\models;

/**
 * This is the model class for table "{{%bairong_detail}}".
 *
 * @property integer $id
 * @property integer $flag_specialList_c
 * @property integer $flag_applyLoan
 * @property integer $sl_cell_bank_fraud
 * @property integer $al_m3_cell_bank_allnum
 * @property integer $al_m3_cell_bank_orgnum
 * @property integer $al_m3_cell_bank_selfnum
 * @property integer $al_m3_cell_notbank_allnum
 * @property integer $al_m3_cell_notbank_orgnum
 * @property integer $al_m3_cell_notbank_selfnum
 * @property integer $al_m3_id_bank_allnum
 * @property integer $al_m3_id_bank_orgnum
 * @property integer $al_m3_id_bank_selfnum
 * @property integer $al_m3_id_notbank_allnum
 * @property integer $al_m3_id_notbank_orgnum
 * @property integer $al_m3_id_notbank_selfnum
 * @property integer $al_m6_cell_bank_allnum
 * @property integer $al_m6_cell_bank_orgnum
 * @property integer $al_m6_cell_bank_selfnum
 * @property integer $al_m6_cell_notbank_allnum
 * @property integer $al_m6_cell_notbank_orgnum
 * @property integer $al_m6_cell_notbank_selfnum
 * @property integer $al_m6_id_bank_allnum
 * @property integer $al_m6_id_bank_orgnum
 * @property integer $al_m6_id_bank_selfnum
 * @property integer $al_m6_id_notbank_allnum
 * @property integer $al_m6_id_notbank_orgnum
 * @property integer $al_m6_id_notbank_selfnum
 * @property integer $al_m12_cell_bank_allnum
 * @property integer $al_m12_cell_bank_orgnum
 * @property integer $al_m12_cell_bank_selfnum
 * @property integer $al_m12_cell_notbank_allnum
 * @property integer $al_m12_cell_notbank_orgnum
 * @property integer $al_m12_cell_notbank_selfnum
 * @property integer $al_m12_id_bank_allnum
 * @property integer $al_m12_id_bank_orgnum
 * @property integer $al_m12_id_bank_selfnum
 * @property integer $al_m12_id_notbank_allnum
 * @property integer $al_m12_id_notbank_orgnum
 * @property integer $al_m12_id_notbank_selfnum
 * @property string $create_time
 * @property string $modify_time
 */
class BairongDetail extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return '{{%bairong_detail}}';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['bairong_id','flag_specialList_c', 'flag_applyLoan', 'sl_cell_bank_fraud', 'al_m3_cell_bank_allnum', 'al_m3_cell_bank_orgnum', 'al_m3_cell_bank_selfnum', 'al_m3_cell_notbank_allnum', 'al_m3_cell_notbank_orgnum', 'al_m3_cell_notbank_selfnum', 'al_m3_id_bank_allnum', 'al_m3_id_bank_orgnum', 'al_m3_id_bank_selfnum', 'al_m3_id_notbank_allnum', 'al_m3_id_notbank_orgnum', 'al_m3_id_notbank_selfnum', 'al_m6_cell_bank_allnum', 'al_m6_cell_bank_orgnum', 'al_m6_cell_bank_selfnum', 'al_m6_cell_notbank_allnum', 'al_m6_cell_notbank_orgnum', 'al_m6_cell_notbank_selfnum', 'al_m6_id_bank_allnum', 'al_m6_id_bank_orgnum', 'al_m6_id_bank_selfnum', 'al_m6_id_notbank_allnum', 'al_m6_id_notbank_orgnum', 'al_m6_id_notbank_selfnum', 'al_m12_cell_bank_allnum', 'al_m12_cell_bank_orgnum', 'al_m12_cell_bank_selfnum', 'al_m12_cell_notbank_allnum', 'al_m12_cell_notbank_orgnum', 'al_m12_cell_notbank_selfnum', 'al_m12_id_bank_allnum', 'al_m12_id_bank_orgnum', 'al_m12_id_bank_selfnum', 'al_m12_id_notbank_allnum', 'al_m12_id_notbank_orgnum', 'al_m12_id_notbank_selfnum'], 'integer'],
            [['create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => '主键',
            'bairong_id' => '百融表id',
            'flag_specialList_c' => '启用黑名单',
            'flag_applyLoan' => '启用多投',
            'sl_cell_bank_fraud' => '黑名单',
            'al_m3_cell_bank_allnum' => 'Al M3 Cell Bank Allnum',
            'al_m3_cell_bank_orgnum' => 'Al M3 Cell Bank Orgnum',
            'al_m3_cell_bank_selfnum' => 'Al M3 Cell Bank Selfnum',
            'al_m3_cell_notbank_allnum' => 'Al M3 Cell Notbank Allnum',
            'al_m3_cell_notbank_orgnum' => 'Al M3 Cell Notbank Orgnum',
            'al_m3_cell_notbank_selfnum' => 'Al M3 Cell Notbank Selfnum',
            'al_m3_id_bank_allnum' => 'Al M3 Id Bank Allnum',
            'al_m3_id_bank_orgnum' => 'Al M3 Id Bank Orgnum',
            'al_m3_id_bank_selfnum' => 'Al M3 Id Bank Selfnum',
            'al_m3_id_notbank_allnum' => 'Al M3 Id Notbank Allnum',
            'al_m3_id_notbank_orgnum' => 'Al M3 Id Notbank Orgnum',
            'al_m3_id_notbank_selfnum' => 'Al M3 Id Notbank Selfnum',
            'al_m6_cell_bank_allnum' => 'Al M6 Cell Bank Allnum',
            'al_m6_cell_bank_orgnum' => 'Al M6 Cell Bank Orgnum',
            'al_m6_cell_bank_selfnum' => 'Al M6 Cell Bank Selfnum',
            'al_m6_cell_notbank_allnum' => 'Al M6 Cell Notbank Allnum',
            'al_m6_cell_notbank_orgnum' => 'Al M6 Cell Notbank Orgnum',
            'al_m6_cell_notbank_selfnum' => 'Al M6 Cell Notbank Selfnum',
            'al_m6_id_bank_allnum' => 'Al M6 Id Bank Allnum',
            'al_m6_id_bank_orgnum' => 'Al M6 Id Bank Orgnum',
            'al_m6_id_bank_selfnum' => 'Al M6 Id Bank Selfnum',
            'al_m6_id_notbank_allnum' => 'Al M6 Id Notbank Allnum',
            'al_m6_id_notbank_orgnum' => 'Al M6 Id Notbank Orgnum',
            'al_m6_id_notbank_selfnum' => 'Al M6 Id Notbank Selfnum',
            'al_m12_cell_bank_allnum' => 'Al M12 Cell Bank Allnum',
            'al_m12_cell_bank_orgnum' => 'Al M12 Cell Bank Orgnum',
            'al_m12_cell_bank_selfnum' => 'Al M12 Cell Bank Selfnum',
            'al_m12_cell_notbank_allnum' => 'Al M12 Cell Notbank Allnum',
            'al_m12_cell_notbank_orgnum' => 'Al M12 Cell Notbank Orgnum',
            'al_m12_cell_notbank_selfnum' => 'Al M12 Cell Notbank Selfnum',
            'al_m12_id_bank_allnum' => 'Al M12 Id Bank Allnum',
            'al_m12_id_bank_orgnum' => 'Al M12 Id Bank Orgnum',
            'al_m12_id_bank_selfnum' => 'Al M12 Id Bank Selfnum',
            'al_m12_id_notbank_allnum' => 'Al M12 Id Notbank Allnum',
            'al_m12_id_notbank_orgnum' => 'Al M12 Id Notbank Orgnum',
            'al_m12_id_notbank_selfnum' => 'Al M12 Id Notbank Selfnum',
            'create_time' => '创建时间',
            'modify_time' => '最后修改时间',
        ];
    }
    public function saveData($data) {
        $time = date('Y-m-d H:i:s');
        $saveData = [
            'bairong_id' => isset($data['bairong_id']) ? $data['bairong_id'] : 0,
            'flag_specialList_c' => isset($data['flag_specialList_c']) ? intval($data['flag_specialList_c']) : 0,
            'flag_applyLoan' => isset($data['flag_applyLoan']) ? intval($data['flag_applyLoan']) : 0,
            'sl_cell_bank_fraud' => isset($data['sl_cell_bank_fraud']) ? $data['sl_cell_bank_fraud'] : null,
            'al_m3_cell_bank_allnum' => isset($data['al_m3_cell_bank_allnum']) ? $data['al_m3_cell_bank_allnum'] : null,
            'al_m3_cell_bank_orgnum' => isset($data['al_m3_cell_bank_orgnum']) ? $data['al_m3_cell_bank_orgnum'] : null,
            'al_m3_cell_bank_selfnum' => isset($data['al_m3_cell_bank_selfnum']) ? $data['al_m3_cell_bank_selfnum'] : null,
            'al_m3_cell_notbank_allnum' => isset($data['al_m3_cell_notbank_allnum']) ? $data['al_m3_cell_notbank_allnum'] : null,
            'al_m3_cell_notbank_orgnum' => isset($data['al_m3_cell_notbank_orgnum']) ? $data['al_m3_cell_notbank_orgnum'] : null,
            'al_m3_cell_notbank_selfnum' => isset($data['al_m3_cell_notbank_selfnum']) ? $data['al_m3_cell_notbank_selfnum'] : null,
            'al_m3_id_bank_allnum' => isset($data['al_m3_id_bank_allnum']) ? $data['al_m3_id_bank_allnum'] : null,
            'al_m3_id_bank_orgnum' => isset($data['al_m3_id_bank_orgnum']) ? $data['al_m3_id_bank_orgnum'] : null,
            'al_m3_id_bank_selfnum' => isset($data['al_m3_id_bank_selfnum']) ? $data['al_m3_id_bank_selfnum'] : null,
            'al_m3_id_notbank_allnum' => isset($data['al_m3_id_notbank_allnum']) ? $data['al_m3_id_notbank_allnum'] : null,
            'al_m3_id_notbank_orgnum' => isset($data['al_m3_id_notbank_orgnum']) ? $data['al_m3_id_notbank_orgnum'] : null,
            'al_m3_id_notbank_selfnum' => isset($data['al_m3_id_notbank_selfnum']) ? $data['al_m3_id_notbank_selfnum'] : null,
            'al_m6_cell_bank_allnum' => isset($data['al_m6_cell_bank_allnum']) ? $data['al_m6_cell_bank_allnum'] : null,
            'al_m6_cell_bank_orgnum' => isset($data['al_m6_cell_bank_orgnum']) ? $data['al_m6_cell_bank_orgnum'] : null,
            'al_m6_cell_bank_selfnum' => isset($data['al_m6_cell_bank_selfnum']) ? $data['al_m6_cell_bank_selfnum'] : null,
            'al_m6_cell_notbank_allnum' => isset($data['al_m6_cell_notbank_allnum']) ? $data['al_m6_cell_notbank_allnum'] : null,
            'al_m6_cell_notbank_orgnum' => isset($data['al_m6_cell_notbank_orgnum']) ? $data['al_m6_cell_notbank_orgnum'] : null,
            'al_m6_cell_notbank_selfnum' => isset($data['al_m6_cell_notbank_selfnum']) ? $data['al_m6_cell_notbank_selfnum'] : null,
            'al_m6_id_bank_allnum' => isset($data['al_m6_id_bank_allnum']) ? $data['al_m6_id_bank_allnum'] : null,
            'al_m6_id_bank_orgnum' => isset($data['al_m6_id_bank_orgnum']) ? $data['al_m6_id_bank_orgnum'] : null,
            'al_m6_id_bank_selfnum' => isset($data['al_m6_id_bank_selfnum']) ? $data['al_m6_id_bank_selfnum'] : null,
            'al_m6_id_notbank_allnum' => isset($data['al_m6_id_notbank_allnum']) ? $data['al_m6_id_notbank_allnum'] : null,
            'al_m6_id_notbank_orgnum' => isset($data['al_m6_id_notbank_orgnum']) ? $data['al_m6_id_notbank_orgnum'] : null,
            'al_m6_id_notbank_selfnum' => isset($data['al_m6_id_notbank_selfnum']) ? $data['al_m6_id_notbank_selfnum'] : null,
            'al_m12_cell_bank_allnum' => isset($data['al_m12_cell_bank_allnum']) ? $data['al_m12_cell_bank_allnum'] : null,
            'al_m12_cell_bank_orgnum' => isset($data['al_m12_cell_bank_orgnum']) ? $data['al_m12_cell_bank_orgnum'] : null,
            'al_m12_cell_bank_selfnum' => isset($data['al_m12_cell_bank_selfnum']) ? $data['al_m12_cell_bank_selfnum'] : null,
            'al_m12_cell_notbank_allnum' => isset($data['al_m12_cell_notbank_allnum']) ? $data['al_m12_cell_notbank_allnum'] : null,
            'al_m12_cell_notbank_orgnum' => isset($data['al_m12_cell_notbank_orgnum']) ? $data['al_m12_cell_notbank_orgnum'] : null,
            'al_m12_cell_notbank_selfnum' => isset($data['al_m12_cell_notbank_selfnum']) ? $data['al_m12_cell_notbank_selfnum'] : null,
            'al_m12_id_bank_allnum' => isset($data['al_m12_id_bank_allnum']) ? $data['al_m12_id_bank_allnum'] : null,
            'al_m12_id_bank_orgnum' => isset($data['al_m12_id_bank_orgnum']) ? $data['al_m12_id_bank_orgnum'] : null,
            'al_m12_id_bank_selfnum' => isset($data['al_m12_id_bank_selfnum']) ? $data['al_m12_id_bank_selfnum'] : null,
            'al_m12_id_notbank_allnum' => isset($data['al_m12_id_notbank_allnum']) ? $data['al_m12_id_notbank_allnum'] : null,
            'al_m12_id_notbank_orgnum' => isset($data['al_m12_id_notbank_orgnum']) ? $data['al_m12_id_notbank_orgnum'] : null,
            'al_m12_id_notbank_selfnum' => isset($data['al_m12_id_notbank_selfnum']) ? $data['al_m12_id_notbank_selfnum'] : null,
            'create_time' => $time,
            'modify_time' =>$time,
        ];
        $error = $this->chkAttributes($saveData);
        if($error){
            return false;
        }
        $result = $this->save();
        return $result;
    }
}
