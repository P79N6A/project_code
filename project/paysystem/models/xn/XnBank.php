<?php

namespace app\models\xn;

/**
 * This is the model class for table "nobel_date_config".
 *
 */
class XnBank extends \app\models\BaseModel {
    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'xn_bank';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            [['bank_name', 'bank_addr'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'bank_name' => 'Bank Name',
            'bank_addr' => 'Bank Addr',
            'status' => 'Status',
        ];
    }
    /**
     * 
     * 获取银行
     *
     * @return []
     */
    public function getBankInfo($bank_addr) {
        if(empty($bank_addr)){
            return false;
        }
        $where = [
            'status' => 1,
            'bank_addr'=>$bank_addr
        ];
        $data = static::find()->where($where)->one();
        return $data;
    }
  
    
  
   
}
