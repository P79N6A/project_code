<?php

namespace app\models\xs;

use Yii;
use app\common\Logger;

/**
 * This is the model class for table "{{%fraudmetrix_detail}}".
 *
 * @property string $id
 * @property string $fid
 * @property integer $bid_fm_sx
 * @property integer $bid_fm_court_sx
 * @property integer $bid_fm_court_enforce
 * @property integer $bid_fm_lost
 * @property integer $bph_fm_fack
 * @property integer $bph_fm_small
 * @property integer $bph_fm_sx
 * @property integer $mid_fm
 * @property integer $mph_fm
 * @property string $create_time
 */
class XsFraudmetrixDetail extends \app\models\repo\CloudBase
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'dc_fraudmetrix_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['fid', 'bid_fm_sx', 'bid_fm_court_sx', 'bid_fm_court_enforce', 'bid_fm_lost', 'bph_fm_fack', 'bph_fm_small', 'bph_fm_sx', 'mid_fm', 'mph_fm'], 'integer'],
            [['create_time'], 'required'],
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
            'fid' => '同盾表id',
            'bid_fm_sx' => '身份证失信:0:否; 1:是',
            'bid_fm_court_sx' => '身份证法院失信:0:否; 1:是',
            'bid_fm_court_enforce' => '身份证法院执行:0:否; 1:是',
            'bid_fm_lost' => '身份证失联:0:否; 1:是',
            'bph_fm_fack' => '手机虚假号码证据库:0:否; 1:是',
            'bph_fm_small' => '手机通信小号证据库:0:否; 1:是',
            'bph_fm_sx' => '手机失信证据库:0:否; 1:是',
            'mid_fm' => '身份证多投',
            'mph_fm' => '手机多投',
            'create_time' => '创建时间',
        ];
    }
    public function saveData($data){ 
        $postData = [ 
            'fid' => $data['fid'],
            'create_time' =>$data["create_time"],
        ]; 

        $isOk = false;
        if(isset($data['bid_fm_sx']) && $data['bid_fm_sx'] > 0 ){
            $isOk = true;
            $postData['bid_fm_sx'] = 1;
        }
        if(isset($data['bid_fm_court_sx']) && $data['bid_fm_court_sx'] > 0 ){
            $isOk = true;
            $postData['bid_fm_court_sx'] = 1;
        }
        if(isset($data['bid_fm_court_enforce']) && $data['bid_fm_court_enforce'] > 0 ){
            $isOk = true;
            $postData['bid_fm_court_enforce'] = 1;
        }
        if(isset($data['bid_fm_lost']) && $data['bid_fm_lost'] > 0 ){
            $isOk = true;
            $postData['bid_fm_lost'] = 1;
        }
        if(isset($data['bph_fm_fack']) && $data['bph_fm_fack'] > 0 ){
           $isOk = true;
           $postData['bph_fm_fack'] =  1;
        }
        if(isset($data['bph_fm_small']) && $data['bph_fm_small'] > 0 ){
           $isOk = true;
           $postData['bph_fm_small'] =  1;
        }
        if(isset($data['bph_fm_sx']) && $data['bph_fm_sx'] > 0 ){
           $isOk = true;
           $postData['bph_fm_sx'] =  1;
        }
        if (isset($data['mid_fm']) && $data['mid_fm'] >0) {
            $isOk = true;
            $postData['mid_fm'] = $data['mid_fm'];
        }
        if(isset($data['mph_fm']) && $data['mph_fm'] > 0 ){
           $isOk = true;
           $postData['mph_fm'] = $data['mph_fm'];
        }
        if(!$isOk){
            return false;
        }

        $error = $this->chkAttributes($postData); 
        if ($error) { 
            Logger::dayLog("xs","db","XsFraudmetrix/saveData","save failed", $postData, $error);
            return false; 
        } 

        return $this->save(); 
    } 


}
