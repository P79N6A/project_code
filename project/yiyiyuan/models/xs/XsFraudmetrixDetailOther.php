<?php

namespace app\models\xs;

use Yii;

/**
 * This is the model class for table "dc_fraudmetrix_detail_other".
 *
 * @property string $id
 * @property string $fid
 * @property integer $three_m_multi_remit
 * @property string $create_time
 */
class XsFraudmetrixDetailOther extends \app\models\xs\XsBaseNewModel {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'dc_fraudmetrix_detail_other';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['fid', 'three_m_multi_remit'], 'integer'],
            [['create_time'], 'required'],
            [['create_time'], 'safe'],
            [['fid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'fid' => '同盾表id',
            'three_m_multi_remit' => '3个月内申请人在多个平台被放款_不包含本合作方',
            'create_time' => '创建时间',
        ];
    }

    public function saveData($data) {
        $postData = [
            'fid' => $data['fid'],
            'create_time' => $data["create_time"],
        ];

        $isOk = false;
        if (isset($data['three_m_multi_remit']) && $data['three_m_multi_remit'] > 0) {
            $isOk = true;
            $postData['three_m_multi_remit'] = $data['three_m_multi_remit'];
        }
        if (!$isOk) {
            return false;
        }

        $error = $this->chkAttributes($postData);
        if ($error) {
            Logger::dayLog("xs", "db", "XsFraudmetrixDetailOther/saveData", "save failed", $postData, $error);
            return false;
        }
        return $this->save();
    }

}
