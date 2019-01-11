<?php

namespace app\models\anti; 

use Yii; 

/** 
 * This is the model class for table "af_jac_base". 
 * 
 * @property string $id
 * @property string $request_id
 * @property integer $aid
 * @property string $user_id
 * @property string $loan_id
 * @property string $mobile
 * @property integer $base_status
 * @property integer $jac_status
 * @property string $create_time
 * @property string $modify_time
 */ 
class AfJacBase extends AntiBaseModel
{ 
    const INIT = 0;
    const DOING = 1;
    const FINISHED = 2;
    /** 
     * @inheritdoc 
     */ 
    public static function tableName() 
    { 
        return 'af_jac_base'; 
    }

    /** 
     * @inheritdoc 
     */ 
    public function rules() 
    { 
        return [
            [['jac_match_id', 'request_id', 'aid', 'user_id', 'base_id','loan_id', 'base_status', 'jac_status'], 'integer'],
            [['mobile', 'base_status', 'jac_status', 'create_time', 'modify_time'], 'required'],
            [['create_time', 'modify_time'], 'safe'],
            [['mobile'], 'string', 'max' => 32]
        ]; 
    } 

    /** 
     * @inheritdoc 
     */ 
    public function attributeLabels() 
    { 
        return [ 
            'id' => 'ID',
            'jac_match_id' => 'af_jac_matcah表主键',
            `request_id` => '请求处理id',
            'aid' => '业务ID',
            'base_id' => 'af_base表主键ID',
            'user_id' => '用户ID',
            'loan_id' => '贷款ID',
            'mobile' => '用户手机号',
            'base_status' => '反欺诈数据分析状态:0:初始; 1:锁定; 2:成功',
            'jac_status' => '杰卡德关系状态:0:初始; 1:锁定; 2:成功',
            'create_time' => '创建时间',
            'modify_time' => '修改时间',
        ]; 
    } 

    /**
     * 获取需要匹配间接关系的数据
     */
    public function getJaccardData($data, $field = '*')
    {
        if (empty($data)) {
            return false;
        }
        $where = [
            'AND',
            ['jac_status' => $data['jac_status']],
            ['>', 'create_time', $data['create_time']],
        ];
//        $query = static::find()->where($where)->select($field)->asArray()->limit(500)->orderBy('create_time desc');
//        echo $query->createCommand()->getRawSql(); exit;
        return static::find()->where($where)->select($field)->asArray()->limit(500)->orderBy('create_time desc')->all();
    }

    /**
     * 锁定为间接关系处理中状态
     */
    public function lockJcards($ids) {
        if (empty($ids)) {
            return false;
        }
        $sets = [
            'jac_status' => self::DOING,
            'modify_time' => date('Y-m-d H:i:s'),
        ];
        $where = [
            'id' => $ids,
            'jac_status' => self::INIT,
        ];
        $result = static::updateAll($sets, $where);
        return $result;
    }

    /**
     * 更新为结束状态
     */
    public function finishJcard($id,$jac_match_id) {
        if (empty($id)) {
            return false;
        }
        $sets = [
            'jac_status' => self::FINISHED,
            'modify_time' => date('Y-m-d H:i:s'),
            'jac_match_id' => $jac_match_id,
        ];
        $where = [
            'id' => $id
        ];
        $result = static::updateAll($sets, $where);
        return $result;
    }
}
