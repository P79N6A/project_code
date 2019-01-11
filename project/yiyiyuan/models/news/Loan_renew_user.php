<?php

namespace app\models\news;

use Yii;
use app\models\BaseModel;

/**
 * This is the model class for table "yi_loan_renew_user".
 *
 * @property string $id
 * @property string $mobile
 * @property integer $type
 * @property string $create_time
 */
class Loan_renew_user extends BaseModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'yi_loan_renew_user';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['type'], 'integer'],
            [['create_time'], 'safe'],
            [['mobile'], 'string', 'max' => 20],
            [['mobile'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'type' => 'Type',
            'create_time' => 'Create Time',
        ];
    }

    /**
     * 判断借款用户是否允许续期
     * @param $loan
     * @return bool
     */
    public function chooseRenewUser($loan) {
        $renewModel = new Renew_amount();
        $renew_result = $renewModel->getRenew($loan->loan_id);
        if ($renew_result) {
            return TRUE;
        }
        if ($loan->business_type != 1) {
            return FALSE;
        }
        $user_info = User::find()->where(['user_id' => $loan->user_id])->one();
        $loan_renew_user_info = self::find()->where(['mobile' => $user_info->mobile])->one();

        $one_start_date = date("Y-m-d 00:00:00", (strtotime($loan->end_date) - ( 1 * 24 * 60 * 60 )));
        $three_start_date = date("Y-m-d 00:00:00", (strtotime($loan->end_date) - ( 3 * 24 * 60 * 60 )));
        $now_date = date("Y-m-d H:i:s");
        if (!empty($loan_renew_user_info) && $loan->status == 9 && $loan->number < 2 && $now_date < $loan->end_date) {
            if ($loan_renew_user_info->type == 1 && $now_date > $one_start_date) {
                return TRUE;
            } elseif ($loan_renew_user_info->type == 2 && $now_date > $three_start_date) {
                return TRUE;
            } elseif ($loan_renew_user_info->type == 3) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

}
