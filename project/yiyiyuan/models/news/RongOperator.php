<?php

namespace app\models\news;

use Yii;

/**
 * This is the model class for table "yi_rong_operator".
 *
 * @property string $id
 * @property string $r_loan_id
 * @property integer $source
 * @property string $filename
 * @property integer $status
 * @property integer $type
 * @property string $last_modify_time
 * @property string $create_time
 */
class RongOperator extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_rong_operator';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['r_loan_id', 'filename'], 'required'],
            [['source', 'status', 'type'], 'integer'],
            [['last_modify_time', 'create_time'], 'safe'],
            [['r_loan_id'], 'string', 'max' => 20],
            [['filename'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'r_loan_id' => 'R Loan ID',
            'source' => 'Source',
            'filename' => 'Filename',
            'status' => 'Status',
            'type' => 'Type',
            'last_modify_time' => 'Last Modify Time',
            'create_time' => 'Create Time',
        ];
    }
    public function saveRongOperator($data)
    {
        $cur_time = date('Y-m-d H:i:s');
        $postData = [
            "r_loan_id" => $data['r_loan_id'], //r360借款id',
            "source" => $data['source'], //来源',
            "filename" => $data['filename'], //文件名',
            "type" => $data['type'], //1:运营商
            "status" => 1, //通知状态',
            "last_modify_time" => $cur_time, //最后修改时间',
            "create_time" => $cur_time, //创建时间',
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }

    public function updateRongOperator($notify_status) {
        $cur_time = date('Y-m-d H:i:s');
        $postData = [
            'status' => $notify_status,
            'last_modify_time' => $cur_time, //最后修改时间
        ];

        $error = $this->chkAttributes($postData);
        if ($error) {
            return false;
        }
        $result = $this->save();
        return $result;
    }
}
