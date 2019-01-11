<?php

namespace app\models\news;

use app\models\BaseModel;
use Yii;

/**
 * This is the model class for table "yi_exchange".
 *
 * @property string $id
 * @property string $loan_id
 * @property integer $exchange
 * @property integer $type
 * @property string $exchange_date
 * @property string $last_modify_time
 * @property string $createtime
 */
 
class Exchange extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'yi_exchange';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['loan_id', 'last_modify_time', 'createtime'], 'required'],
            [['loan_id', 'exchange', 'type'], 'integer'],
            [['exchange_date', 'last_modify_time', 'createtime'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'loan_id' => 'Loan ID',
            'exchange' => 'Exchange',
            'type' => 'Type',
            'exchange_date' => 'Exchange Date',
            'last_modify_time' => 'Last Modify Time',
            'createtime' => 'Createtime',
        ];
    }

    /**
     * 根据loan_id查询刚兑记录
     * @param type $loan_id
     * @return obj|bool
     */
    public function getByLoanId($loan_id) {
        if (empty($loan_id) || !is_numeric($loan_id)) {
            return NULL;
        }
        $cg_remit = self::find()->where(['loan_id'=>$loan_id])->one();
        return $cg_remit;
    }

    /**
     * 添加一条数据
     * @param $condition
     * @return bool
     */
    public function add_list($condition){
        if(!is_array($condition) || empty($condition)){
            return false;
        }
        if (!empty($condition['loan_id'])) {
            $res = self::find()->where(['loan_id'=>$condition['loan_id']])->one();
            if($res){
                $result = $res->update_list($condition);
                return $result;
            }
        }
        $data = $condition;
        $data['last_modify_time'] =  date('Y-m-d H:i:s');
        $data['createtime'] =  date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }

    /**
     * 更新一条数据
     * @param $condition
     * @return bool
     */
    public function update_list($condition) {
        if(!is_array($condition) || empty($condition)){
            return false;
        }
        $data = $condition;
        $data['last_modify_time'] =  date('Y-m-d H:i:s');
        $error = $this->chkAttributes($data);
        if ($error) {
            return false;
        }
        return $this->save();
    }
    
    
    public function saveOutCunguan(){
        $this->type = 2;
        $this->last_modify_time = date('Y-m-d H:i:s');
        $result = $this->save();
        return $result;
    }

}
