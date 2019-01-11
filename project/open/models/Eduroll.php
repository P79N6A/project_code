<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%eduroll}}".
 *
 * @property integer $id
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
class Eduroll extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%eduroll}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'idcode', 'enroldate', 'graduate', 'educationdegree',], 'required', "message"=> "{attribute}不能为空"],
            [['studystyle', 'status', 'enroldate_check', 'graduate_check', 'educationdegree_check', 'studystyle_check', 'create_time'], 'integer'],
            [['name', 'idcode', 'educationdegree', 'gradudate'], 'string', 'max' => 20],
            [['enroldate'], 'string', 'max' => 10],
            [['graduate'], 'string', 'max' => 30],
		
			// 判断是否是汉字 @todo
			[['name'], 'string', 'max' => 10],
			
			// 判断身份证号 只搞18位
			[['idcode'], 'isIdcard', 'message'=>'3424242'],
			
			// 判断学历
			[['educationdegree'], 'in', 'range' => ['专科', '本科', '硕士', '博士'], 'message'=>'学历不合法'],
			
			// 判断 毕业院校 中文  @todo
			
			// 判断 学历类别
			//[['studystyle'], 'in', 'range' => [0,1,2,5,6,7,8], 'message'=>'学历类别不合法'],
			
			// 判断 入学年份 减10年
			[['enroldate'], 'isEnroldate', 'message'=>'入学年份未在限定的范围内'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '真实姓名',
            'idcode' => '省份证号',
            'enroldate' => '入学年份',
            'graduate' => '毕业院校',
            'educationdegree' => '学历 本科 硕士',
            'studystyle' => '学历类型 1普通 5成人 6自考 7网络教育 8开放教育传数字 2研究生 ',
            'status' => '检测结果 ',
            'gradudate' => '预计毕业年份',
            'enroldate_check' => '(对比)入学年份',
            'graduate_check' => '(对比)院校对比结果',
            'educationdegree_check' => '(对比)学历对比',
            'studystyle_check' => '(对比)学历类型',
            'create_time' => '创建时间',
        ];
    }
	/**
	 * 身份证是否合法
	 */
	public function isIdcard($attribute, $param){
		// 身份证要大写存储
		$this->idcode = strtoupper($this->idcode);
		$str = $this->idcode;
		if(!$this->_isIdcard($str)){
			$this->addError($attribute, '身份证号不合法!');	
		}
	}
	private function _isIdcard($str){
		$flag = false;
		$str = strtoupper($str);
		$pattern = "/^\d{17}(\d|X)$/";
		if (preg_match($pattern,$str)) {
			$flag = true;
		}
		return $flag;
	}
	
	/**
	 * 入学年份限制在10年内
	 */
	public function isEnroldate($attribute, $param){
		$enroldate = $this->enroldate;
		if( !is_numeric($enroldate) ){
			$this->addError($attribute, '入学年份必须是数字!');	
		}
		
		$yearEnd = intval(date('Y'));
		$yearStart = $yearEnd - 10;
		
		if( $yearStart <= $enroldate &&  $enroldate <= $yearEnd ){
			
		}else{
			$this->addError($attribute, '入学年份必须在10年内!');	
		}
	}
	/**
	 * 根据id号获取一条纪录
	 */
	public function getByIdcode($idcode){
		if(!$idcode){
			return null;
		}
		$one = self::find()-> where(['idcode'=>$idcode])  -> one();
		return $one;
	}
	/**
	 * 若提交的与某条日志完全相同，则返回这条日志曾经纪录的信息
	 * @param array $postData
	 * @return object ar
	 */
	public function chkData($postData){
		if( !is_array($postData) ){
			return null;
		}
		
		// 根据身份证查询日志信息
		$log = $this ->getByIdcode($postData['idcode']);
		if( !$log ){
			return null;
		}
		
		// 若当前身份证已经确认存在.
		if( $log['status'] == 1 ){
			$resultData =  [
				'gradudate' => $log['gradudate'],
	            'enroldate_check' => $log['enroldate_check'] == 1 && $log['enroldate'] == $postData['enroldate'] ? 1 : 0,
	            'graduate_check' => $log['graduate_check'] == 1 && $log['graduate'] == $postData['graduate'] ? 1 : 0,
	            'educationdegree_check' =>$log['educationdegree_check'] == 1 && $log['educationdegree'] == $postData['educationdegree'] ? 1 : 0,
	            'studystyle_check' => $log['studystyle_check'] == 1 && $log['studystyle'] == $postData['studystyle'] ? 1 : 0,
			];
			// 若名字无法对应上，则不合法
			if( $postData['name'] == $log['name'] ){
				$resultData['status'] = $this-> getStatus($resultData);
			}else{
				$resultData['name_check'] = '0';
				$resultData['status'] = 0;
			}
			
			return $resultData;
		}
		
		return null;
		
	}
	/**
	 * 检测状态是否全部通过了
	 */
	public function getStatus($data){
		
		if( $data['enroldate_check'] == 1 && // 入学日期比对结果
			$data['graduate_check'] == 1  && //  院校比对结果
			$data['educationdegree_check'] == 1// 学习层次比对结果  专科 本科 硕士 博士
			// && $data['studystyle_check'] == 1 //  学历类别比对结果  普通 成人 
		){
			return 1;
		}else{
			return 0;
		}
	}
	/**
	 * 用新数据更新老数据
	 */
	public function saveByNewData($postData){
		if( empty($postData) ){
			return false;
		}
		
		// 更新到身份证表中
		$this -> saveIdcode($postData['name'],$postData['idcode']);
		
		$o = $this->getByIdcode($postData['idcode']);
		
		if( $o ){
			// 用新数据更新未设置的老数据结构
			if( $postData['enroldate_check'] == 1 ){
				$o-> enroldate_check = $postData['enroldate_check'];// 入学日期比对结果
				$o-> enroldate = $postData['enroldate'];// 入学日期比对结果
			}
			if( $postData['graduate_check'] == 1 ){
				$o -> graduate_check = $postData['graduate_check']; // 院校比对结果
			}
			if( $postData['educationdegree_check'] == 1 ){
				$o -> educationdegree_check = $postData['educationdegree_check'];// 学习层次比对结果  专科 本科 硕士 博士
			}
			if( $postData['studystyle_check'] == 1 ){
				$o-> studystyle_check = $postData['studystyle_check'];// 学历类别比对结果  普通 成人 
			}
			if( $postData['gradudate']){
				$o -> gradudate = $postData['gradudate']; // 预计毕业年份
			}
			$o -> status = $this->getStatus($o->attributes);
			return $o -> save();
		}else{
			$o = new self();
			$o->attributes = $postData;
			
			if ( !$o->validate() ) {
				//var_dump($o->errors);
				return false;
			}
			return $o -> save();
		}
		
	}
	/**
	 * 保存身份证信息到身份证表
	 */
	private function saveIdcode($name, $idcard){
		$oIdcard = new \app\models\Idcard;
		$res = $oIdcard -> existsIdcard($idcard);
		if( !$res ){
			$oIdcard -> attributes = [
				'name' => $name,
				'idcard' =>  $idcard,
				'create_time' => time()
			];
			return $oIdcard -> save();
		}
		return $res;
	}
	
}
