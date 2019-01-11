<?php

namespace app\models\day;

use Yii;

/**
 * This is the model class for table "qj_scan_times".
 *
 * @property string $id
 * @property string $relation
 * @property integer $type
 * @property string $last_modify_time
 * @property string $create_time
 * @property integer $version
 */
class Scan_times extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'qj_scan_times';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['relation_id'], 'required'],
            [['relation_id', 'type', 'version'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'relation_id' => 'Relation ID',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
            'version' => 'Version',
        ];
    }

    public function optimisticLock() {
        return "version";
    }

    /**
     * 新增记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/10/15 15:02
     */
    public function addRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $time = date('Y-m-d H:i:s');
        $data['last_modify_time'] = $time;
        $data['create_time'] = $time;
        $data['version'] = 0;
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新记录
     * @param $condition
     * @return bool
     * @author 王新龙
     * @date 2018/10/15 15:02
     */
    public function updateRecord($condition) {
        if (empty($condition) || !is_array($condition)) {
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] = date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 查询记录，根据relation_id及type
     * @param $relation_id
     * @param $type
     * @return array|null|\yii\db\ActiveRecord
     * @author 王新龙
     * @date 2018/10/15 15:02
     */
    public function getByRelationIdAndType($relation_id, $type) {
        if (empty($relation_id) || empty($type)) {
            return null;
        }
        return self::find()->where(['relation_id' => $relation_id, 'type' => $type])->one();
    }

    /**
     * 获取还款&展期弹窗
     * @param $user_id
     * @param int $type
     * @return array
     * @author 王新龙
     * @date 2018/10/15 15:56
     */
    public function getRepayPopup($user_id, $type = 1) {
        $popup = [
            'type' => $type,//1还款 2展期
            'status' => 1,//1成功 2失败
            'is_popup' => 2,//1弹 2不弹
            'days' => 7,
            'end_date' => '0000-00-00'
        ];
        if ($type == 1) {
            $where = ['user_id' => $user_id, 'status' => [1, 4]];
            $o_loan_repay = (new Loan_repay_guide())->find()->where($where)->orderBy('id desc')->one();
            if (!empty($o_loan_repay)) {
                $popup['status'] = $o_loan_repay->status;
                $o_scan_times = (new Scan_times())->getByRelationIdAndType($o_loan_repay->id, 1);
                if (empty($o_scan_times)) {
                    $popup['is_popup'] = 1;
                    $data = [
                        'relation_id' => $o_loan_repay->id,
                        'type' => 1
                    ];
                    (new Scan_times())->addRecord($data);
                }
            }
        } else {
            $where = ['user_id' => $user_id, 'status' => [1, 4]];
            $o_renwal = (new Renewal_payment_record_guide())->find()->where($where)->orderBy('id desc')->one();
            if (!empty($o_renwal)) {
                $o_user_loan = (new User_loan_guide())->getHaveinLoan($user_id);
                if (!empty($o_user_loan)) {
                    $popup['days'] = $o_user_loan->days;
                    $popup['end_date'] = date('Y-m-d', strtotime($o_user_loan->end_date)-86400);
                }
                $popup['status'] = $o_renwal->status;
                $o_scan_times = (new Scan_times())->getByRelationIdAndType($o_renwal->id, 2);
                if (empty($o_scan_times)) {
                    $popup['is_popup'] = 1;
                    $data = [
                        'relation_id' => $o_renwal->id,
                        'type' => 2
                    ];
                    (new Scan_times())->addRecord($data);
                }
            }
        }
        return $popup;
    }
}
