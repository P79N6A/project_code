<?php

namespace app\models\news;

use app\models\BaseModel;

/**
 * This is the model class for table "yi_guide_notify".
 *
 * @property string $id
 * @property integer $type
 * @property integer $status
 * @property string $pid
 * @property string $rid
 * @property string $url
 * @property string $create_time
 */
class GuideNotify extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_guide_notify';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'status'], 'integer'],
            [['url'], 'required'],
            [['last_modify_time','create_time'], 'safe'],
            [['pid', 'rid'], 'string', 'max' => 32],
            [['url'], 'string', 'max' => 128]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'status' => 'Status',
            'pid' => 'Pid',
            'rid' => 'Rid',
            'url' => 'Url',
            'create_time' => 'Create Time',
        ];
    }

    public function getRepay()
    {
        return $this->hasOne(Loan_repay::className(), ['id' => 'pid']);
    }

    public function getUserloan()
    {
        return $this->hasOne(User_loan::className(), ['loan_id' => 'pid']);
    }

    public function getLoanextend()
    {
        return $this->hasOne(User_loan_extend::className(), ['loan_id' => 'pid']);
    }

    //新增
    public function add($condition)
    {
        $condition['last_modify_time'] = date("Y-m-d H:i:s");
        $condition['create_time'] = date("Y-m-d H:i:s");
        $check = $this->chkAttributes($condition);

        if (!empty($check)) {
            return false;
        }
        return $this->save();
    }

    //查询初始状态的通知记录
    public function listInitialNotify($stime, $type, $limit = 200)
    {
        $where = [
            'AND',
            ['>=', 'create_time', $stime],
            ['type' => $type],
            ['status' => 0],
        ];
        return self::find()->where($where)->limit($limit)->all();
    }

    //修改订单结果
    public function updateNoticeStatus($status)
    {
        $condition['status'] = $status;
        $condition['last_modify_time'] = date("Y-m-d H:i:s");
        $error = $this->chkAttributes($condition);
        if ($error) {
            return false;
        }
        return $this->save();
    }
}
