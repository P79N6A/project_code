<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%eduroll_log}}".
 *
 * @property integer $id
 * @property integer $log_id
 * @property string $name
 * @property string $idcode
 * @property string $enroldate
 * @property string $graduate
 * @property string $educationdegree
 * @property integer $studystyle
 * @property integer $status
 * @property string $gradudate
 * @property integer $enroldate_check
 * @property integer $graduate_check
 * @property integer $educationdegree_check
 * @property integer $studystyle_check
 * @property integer $create_time
 */
class EdurollLog extends  \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%eduroll_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'idcode', 'enroldate', 'graduate', 'educationdegree', 'studystyle', 'create_time'], 'required'],
            [['studystyle', 'status', 'enroldate_check', 'graduate_check', 'educationdegree_check', 'studystyle_check', 'create_time'], 'integer'],
            [['name', 'idcode', 'educationdegree', 'gradudate'], 'string', 'max' => 20],
            [['enroldate'], 'string', 'max' => 10],
            [['graduate'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'idcode' => 'Idcode',
            'enroldate' => 'Enroldate',
            'graduate' => 'Graduate',
            'educationdegree' => 'Educationdegree',
            'studystyle' => 'Studystyle',
            'status' => 'Status',
            'gradudate' => 'Gradudate',
            'enroldate_check' => 'Enroldate Check',
            'graduate_check' => 'Graduate Check',
            'educationdegree_check' => 'Educationdegree Check',
            'studystyle_check' => 'Studystyle Check',
            'create_time' => 'Create Time',
        ];
    }
	/**
	 * 每日同一身份证三次查询
	 */
	public function chkQueryNum($idcode){
		if(!$idcode){
			return false;
		}
		$today = strtotime(date('Y-m-d'));
		$total = self::find()-> where(['idcode'=>$idcode]) 
							 -> andWhere(['>=','create_time',$today])
							 -> count();
		
		// 每日 限定为3次
		if( YII_ENV_DEV ){
			$limit = 10;
		}else{
			$limit = 3;
		}
		
		return $total < $limit;
	}
	
	/**
	 * 根据id号获取多条纪录
	 */
	public function getsByIdcode($idcode){
		if(!$idcode){
			return null;
		}
		/*$query->orderBy([
    		'id' => SORT_ASC,
    		'name' => SORT_DESC,
		];*/
		
		$one = self::find()-> where(['idcode'=>$idcode]) -> orderBy(['id'=>SORT_DESC]) -> limit(10) ->  all();
		return $one;
	}
	/**
	 * 若提交的与某条日志完全相同，则返回这条日志曾经纪录的信息
	 * @param array $postData
	 * @return object ar
	 */
	public function chkLogs($postData){
		if( !is_array($postData) ){
			return null;
		}
		
		// 根据身份证查询日志信息
		$logs = $this ->getsByIdcode($postData['idcode']);
		if( !$logs ){
			return null;
		}
			
		// 从日志里面过滤是否有完全相同的数据（由于这一块以后想优化成自动匹配检查过的数据，故没有直接在数据库按条件查询）
		foreach($logs as $log){
			// 与某条日志完全相同，则返回那条日记的信息
			if( $log['name'] == $postData['name'] &&
				$log['educationdegree'] == $postData['educationdegree'] &&
				$log['graduate'] == $postData['graduate'] &&
				//$log['studystyle'] == $postData['studystyle'] &&
				$log['enroldate'] == $postData['enroldate'] 
			 ){
				return $log -> attributes;;
			}
		}
		
		return null;
		
	}
	
}
