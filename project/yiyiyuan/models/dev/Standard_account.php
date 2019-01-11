<?php

namespace app\models\dev;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property string $id
 * @property string $mobile
 * @property string $password
 * @property string $school
 * @property integer $edu_levels
 * @property string $entrance_time
 * @property string $account_name
 * @property string $identity
 * @property string $create_time
 */
class Standard_account extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_standard_account';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
        ];
    }

    public function getstandardByUserId($user_id, $select = '') {
        if (empty($user_id)) {
            return FALSE;
        }
        $stat_info = Standard_account::find()->where(['user_id' => $user_id]);
        if (!empty($select)) {
            $stat_info = $stat_info->select($select);
        }
        $result = $stat_info->one();
        if (empty($result)) {
            return null;
        }
        return $result;
    }

    public function addStandardAccount($user, $invest_share, $profit) {
        $now_time = date('Y-m-d H:i:s');
        $standardAccountModel = new Standard_account();
        $standardAccountModel->version = 1;
        $standardAccountModel->user_id = $user->user_id;
        $standardAccountModel->total_invested = $invest_share;
        $standardAccountModel->total_oninvest = $invest_share;
        $standardAccountModel->total_oninterest = $profit;
        $standardAccountModel->total_historyinterest = 0;
        $standardAccountModel->total_yield = 0;
        $standardAccountModel->last_modify_time = $now_time;
        $standardAccountModel->create_time = $now_time;
        $result = $standardAccountModel->save();
        if ($result) {
            return Yii::$app->db->getLastInsertID();
        } else {
            return false;
        }
    }

    public function updateStandardAccount($standard_account, $invest_share, $profit) {
        $now_time = date('Y-m-d H:i:s');
        $standard_account->total_invested +=$invest_share;
        $standard_account->total_oninvest +=$invest_share;
        $standard_account->total_oninterest +=$profit;
        $standard_account->last_modify_time = $now_time;
        $standard_account->version +=1;
        $result = $standard_account->save();
        return $result;
    }
    
    /**
     * 收益返还时修改用户的标的账户
     */
    public function setStandardAccount($profit, $now_time, $user_id, $share){
    	$sql_standard_account = "update ".Standard_account::tableName()." set total_oninvest=(total_oninvest-".$share."),total_historyinterest=(total_historyinterest+".$profit."),last_modify_time='$now_time',version=version+1 where user_id=".$user_id;
    	$ret_standard_account = Yii::$app->db->createCommand($sql_standard_account)->execute();
    	
    	return $ret_standard_account;
    }

}
