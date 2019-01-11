<?php
namespace app\models\dev;

use Yii;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\User_auth;
use yii\helpers\ArrayHelper;
use \app\models\dev\FriendPairs;
/**
* This is the model class for table "yi_friends".
*
* @property string $id
* @property string $user_id
* @property string $fuser_id
* @property integer $type
* @property integer $auth
* @property integer $authed
* @property string $company
* @property string $school_id
* @property integer $invite
* @property integer $like
* @property string $modify_time
* @property string $create_time
*/
class Friends extends \app\models\BaseModel
{
    private $userModel;
    private $fuserModel;
    /**
    * @inheritdoc
    */
    public static function tableName()
    {
        return 'yi_friends';
    }
    /**
    * @inheritdoc
    */
    public function rules()
    {
        return array(array(array('user_id', 'fuser_id', 'modify_time', 'create_time'), 'required'), array(array('user_id', 'fuser_id', 'type', 'auth', 'authed', 'same_company', 'school_id', 'same_school', 'invite', 'like'), 'integer'), array(array('modify_time', 'create_time'), 'safe'), array(array('company'), 'string', 'max' => 100));
    }
    /**
    * @inheritdoc
    */
    public function attributeLabels()
    {
        return array('id' => Yii::t('app', 'ID'), 'user_id' => Yii::t('app', '会员id'), 'fuser_id' => Yii::t('app', '好友的会员id'), 'type' => Yii::t('app', '1:1度好友; 2:2度好友; 3:非好友关系(同公司，学校等等);'), 'auth' => Yii::t('app', '认证好友: 0未认证; 1认证'), 'authed' => Yii::t('app', '被好友认证: 0未被认证; 1被认证'), 'company' => Yii::t('app', '好友的公司名'), 'same_company' => Yii::t('app', '公司相同 0否 1是'), 'school_id' => Yii::t('app', '好友学校id'), 'same_school' => Yii::t('app', '学校相同 0否 1是'), 'invite' => Yii::t('app', '邀请关系;0:无 1'), 'like' => Yii::t('app', '点赞关系; 0无 1有'), 'modify_time' => Yii::t('app', '关系更新时间'), 'create_time' => Yii::t('app', '好友创建时间'));
    }

	/**
	 * 获取会员基本信息
	 * @param int $user_id
	 * @return object
	 */
	public function getUserInfo($user_id){
		$user_id = intval($user_id);
		if(!$user_id){
			return null;
		}
                return User::findOne($user_id);
	}
        
        public function getFriendsRelation($user_id,$fuser_id){
            $friendModel = new Friends();
            $relation = $friendModel->find()->where(['user_id'=>$user_id,'fuser_id'=>$fuser_id])->one();
            return $relation;
        }

        /******************************start 查询操作******************************/
    /** 
    * 获取某个帐号的一个好友 
    * @param $user_id 帐号 
    * @param $type 1度; 2度 好友; 3其它关系 null 所有
	* @return []
    */
    public function getFriends($user_id, $type, $offset=0, $limit = 100 )
    {
        $condition = array('user_id' => $user_id, 'type' => $type);
        return static::find()->where($condition)->offset($offset) -> limit($limit) -> all();
    }
    /** 
    * 获取某个帐号的一个好友 
    * @param $user_id 帐号 
    * @param $fuser_id 朋友 
    */
    public function getFriend($user_id, $fuser_id)
    {
        $condition = array('user_id' => $user_id, 'fuser_id' => $fuser_id);
        return static::find()->where($condition)->one();
    }
	/**
	 * 获取公司相同的好友
	 * @param int $user_id
	 * @param int $offset
	 * @param int $limit
	 * @return []
	 */
	public function getSameCompanys($user_id,  $offset=0, $limit = 100){
        $condition = array('user_id' => $user_id, 'same_company' => 1);
        return static::find()->where($condition)->offset($offset) -> limit($limit) -> all();
	}
	/**
	 * 公司相同的好友的数量
	 * @param $user_id
	 * @return int
	 */
	public function countSameCompany($user_id){
        $condition = array('user_id' => $user_id, 'same_company' => 1);
        return static::find()->where($condition)->count();
	}
	/**
	 * 是否修改了公司名
	 * @param $user_id
	 * @param $newCompany
	 * @return bool
	 */
	public function isModifyCompany($user_id, $newCompany){
        $condition = array('user_id' => $user_id, 'same_company' => 1);
        $friend = static::find()->where($condition)->one();
		// 没有说明是新加的公司名,那么算是修改了名称
		if(empty($friend)){
			return true;
		}
		return $friend->company != $newCompany;
	}
	
	/******************************end 查询操作******************************/
	
	/******************************start 添加操作******************************/
	/**
	 * 检测一条纪录是否存在关系
	 * @param [] $relation
	 * @return bool
	 */
	public function chkRelation($relation){
		//1 检测是否为空
		if( !$this->valid_array($relation) ){
			return false;
		}
		
		//2 若有一处与norelation不同，则说明有关系
		$noRelation = $this->getInitRelation(); // 这个表示没有任何关系. 至少要有一处与它不同
		$result = false;
		foreach($relation as $k=>$r){
			// 找到了一处，那么就是有关系
			if( isset($noRelation[$k]) && $r != $noRelation[$k] ){
				$result = true;
				break;
			}
		}
		return $result;
	}
    /**
     * 初始状态: 没任何关系的状态
     */
    public function getInitRelation()
    {
        return [
		        'auth' => 0,         // 认证
		        'authed' => 0,       // 被认证
		        'same_company' => 0, // 同公司
		        'same_school' => 0,  // 同学校
		        'invite' => 0,       // 邀请
		        'like' => 0	,		 // 点赞
        ];
    }
    /**
     * 批量添加好友关系
	 * 注意：此方法不检测是否存在
	 * @param int $user_id
	 * @param [] $friend_ids
	 * @param [] $relation
	 * @return bool
     */
    public function addBatch($user_id, $friend_ids, $relation=null)
    {
    	//1 检测每项参数
        $user_id = intval($user_id);
        if (!$user_id) {
            return false;
        }
        if (!$this->valid_array($friend_ids)) {
            return false;
        }
        if (!$this->valid_array($relation)) {
            return false;
        }
		if( !isset($relation['type']) ){
			$relation['type'] = 3;
		}
		
		//2 获取初始化数据
        $insertData = $this->getInitRelation();
        $insertData['create_time'] = $insertData['modify_time'] = date('Y-m-d H:i:s');
		if( $this->valid_array($relation) ){
			$insertData = array_merge($insertData, $relation);
		}
		
        //3 相互导入
        $insertfriends = array();
        foreach ($friend_ids as $fuser_id) {
            //1 我添加好友信息
            $me = $insertData;
            $me['user_id']   = $user_id;
            $me['fuser_id']  = $fuser_id;
            $insertfriends[] = $me;
			
            //2 好友添加我的信息
            $fr = $insertData;
            $fr['user_id'] = $fuser_id;
            $fr['fuser_id'] = $user_id;
            $insertfriends[] = $fr;
        }
		
        //4 检测是否有数据
        if ( !$this->valid_array($insertfriends) ) {
            return FALSE;
        }
		
		//5 批量更新并返回结果
		return $this->insertBatch($insertfriends);
    }
	/******************************end 添加操作******************************/

	
	/******************************start 更新操作******************************/
    /** 
    * 刷新一个好友的信息
	* 用于认证, 点赞，邀请
    * @param $user_id  主动[认证者 ]
    * @param $fuser_id 被动[被认证者 ]
	* @return bool
    */
    public function refreshFriend($user_id, $fuser_id)
    {
        //1 参数验证
        $user_id  = intval($user_id);
        $fuser_id = intval($fuser_id);
        if (!$user_id || !$fuser_id) {
            return $this->returnError(false, '填写的user_id数据为空');
        }
		
        //2 这里抛出异常是为了必须检查是否是有效的会员
    	$pairsModel = new FriendPairs();
		$res = $pairsModel -> setPair($user_id, $fuser_id);
		if(!$res){
			return $this->returnError(false, $pairsModel->errinfo);
		}
	
 		return $pairsModel -> refresh();
    }
	
    /**
     * 某会员认证完学校
     * @param $user_id
     * @return bool
     */
    public function updateSchool($user_id)
    {
    	return true;
        //1 验证数据合法
        $userInfo = $this->getUserInfo($user_id);
        if (empty($userInfo)) {
            return FALSE;
        }
		
        //2 检测学校id
        $school_id = $userInfo->school_id;
        if (!$school_id) {
            return FALSE;
        }
        if ($userInfo->school_valid != 2) {
            return FALSE;
        }
		
        //3 对好友进行更新和添加
        $updatefids = [];
        $insertfIds = [];
		
        //4 开启事务
        $result = true;
        //$this->beginTransaction();
		$relation = ['school_id' => $school_id, 'same_school' => 1];// 需要更新的关系
		
        //5 一百条一百条的处理 
        $total = $this->mySchoolCount($user_id, $school_id);
		$limit = 100;// 每100条处理一次 @todo
		$pages = ceil( $total / $limit );
		for( $i=0; $i < $pages; $i++ ){
			 //1  获取 同学校的 user_id
			$offset     = $limit * $i;
			$users = $this->mySchool($user_id, $school_id, $offset, $limit);
			if( empty($users) ){
				break;
			}
            $schooluids = ArrayHelper::getColumn($users, 'user_id');
            if (empty($schooluids)) {
                break;
            }
			
            //2 检测 $schooluids 哪些是我的好友
            $updatefids = static::find()->select('fuser_id')->where(array('user_id' => $user_id, 'fuser_id' => $schooluids))->column();
           
		   
		    //3 更新好友：本来我的好友， 现在又跟我是同学校
            if (!empty($updatefids)) {
                // 进行更新操作
                $total1 = static::updateAll($relation, array('user_id' => $user_id, 'fuser_id' => $updatefids));
                $total2 = static::updateAll($relation, array('user_id' => $updatefids, 'fuser_id' => $user_id));
            }
			
            //4 添加好友: school里面有， 但好友里面没有的。
            $friend_ids = !empty($updatefids) ? array_diff($schooluids, $updatefids) : $schooluids;
            if( $this->valid_array($friend_ids) ){
            	$result = $this->addBatch($user_id, $friend_ids, $relation);
				if(!$result){
					//$this->endTransaction(false);
					break;
				}
            }
        }
        //$this->endTransaction(true);
        return true;
    }
	
	
	/**
	 * 某会员发起更新公司的操作
	 * 1. 需要更新老公司关系
	 * 2. 处理新公司的关系
	 * @param int $user_id
	 * @return bool
	 */
	public function updateCompany($user_id,$force=false){
		return true;
        //1 验证数据合法
        $userInfo = $this->getUserInfo($user_id);
        if (empty($userInfo)) {
            return FALSE;
        }
		
        //2 检测公司信息,因为空公司也可能是一种改变这里不检测空
        //  (而下面第七步导入新公司关系会检测空值,注意区分)。
        $company = $userInfo->company ? $userInfo->company : '';
		
		//3 获取是否修改了公司, 没有修改的话不用处理了
		if($force){
			$isModify = true;
		}else{
			$isModify = $this->isModifyCompany($user_id, $company);
			if( !$isModify ){
				return true;
			}
		}
		
		//4 #################################start 老公司关系处理
		//$this->beginTransaction();
		
		//4.1 将我的好友信息中不等于现在公司的全部重置
		$condition1 = [
			'AND',
			['user_id' => $user_id, 'same_company' => 1],
			['!=','company', $company]
		];
		$update1 = static::updateAll(['same_company'=>0], $condition1);
        
        //4.2 好友中存我的公司信息也均重置,并重新设置公司名称
		$condition2 = [
			'AND',
			['fuser_id' => $user_id, 'same_company' => 1],
			['!=','company', $company]
		];
        $update2 = static::updateAll(['same_company'=>0,'company'=>$company], $condition2);
		
		//4.3 好友中存我的公司均需要改变
		$update3 = static::updateAll(['company'=>$company], ['fuser_id' => $user_id]);
		
		//5  删除没有任何关系的数据
		$noRelation = $this->getInitRelation(); // 这个表示没有任何关系
		
		//5.1  删除我的好友
		$delCondtion1 = $noRelation;
		$delCondtion1['user_id'] = $user_id;
		$del1 = static::deleteAll($delCondtion1);
		
		//5.2 删除好友存我的信息
		$delCondtion2 = $noRelation;
		$delCondtion2['fuser_id'] = $user_id;
		$del2 = static::deleteAll($delCondtion2);
		
		//6 公司空值:公司为空是不可以进行下面的导入新公司关系
        if( empty($company) ){
	        //$this->endTransaction(true);
	        return true;
        }
		// #################################end 老公司关系处理
		
		
		//7 #################################start 新公司的关系处理
        $updatefids = [];
        $insertfIds = [];

        //8 一百条一百条的处理 
		$relation = ['company' => $company, 'same_company' => 1];// 需要更新的关系
		
        $total = $this->myCompanyCount($user_id, $company);
		$limit = 100;// 每100条处理一次 @todo
		$pages = ceil( $total / $limit );
		for( $i=0; $i < $pages; $i++ ){
			 //1  获取 同公司的 user_id
			$offset= $limit * $i;
			$users = $this->myCompany($user_id, $company, $offset, $limit);
			if( empty($users) ){
				break;
			}
            $companyuids = ArrayHelper::getColumn($users, 'user_id');
            if (empty($companyuids)) {
                break;
            }
			
            //2 检测 $companyuids 哪些是我的好友
            $updatefids = static::find()->select('fuser_id')->where(array('user_id' => $user_id, 'fuser_id' => $companyuids))->column();
           
		    //3 更新好友：本来我的好友， 现在又跟我是同公司
            if (!empty($updatefids)) {
                // 进行更新操作
                $total1 = static::updateAll($relation, ['user_id' => $user_id,    'fuser_id' => $updatefids]);
                $total2 = static::updateAll($relation, ['user_id' => $updatefids, 'fuser_id' => $user_id]);
            }
			
            //4 添加好友: 同公司好友里面有， 但好友里面没有的。
            $friend_ids = !empty($updatefids) ? array_diff($companyuids, $updatefids) : $companyuids;
            if( $this->valid_array($friend_ids) ){
            	$result = $this->addBatch($user_id, $friend_ids, $relation);
				if(!$result){
					//$this->endTransaction(false);
					break;
				}
            }
	    }
		#################################end 新公司的关系处理

		//9 返回结果
        //$this->endTransaction(true);
        return true;
	}


	/******************************end 更新操作******************************/
	

	/******************************start 删除好友******************************/
	/**
	 * 删除我的所有好友
	 * @param int $user_id
	 * @return bool
	 */
	public function deleteFriends($user_id){
		$user_id = intval($user_id);
		if( !$user_id ){
			return FALSE;
		}
		$res1 = static::deleteAll(['user_id' => $user_id]);
		$res2 = static::deleteAll(['fuser_id'=> $user_id]);
		return true;
	}
	/******************************end 删除好友******************************/
	
	
	
	
	
	/*因为有的model没在线上。别的model不能上传，只能先放在这里了*/
	/******************************start 从别的model中移来的方法*******************/
	/**
     * 是否认证了$user_id
     * @param type $user_id
     * @param type $user_id_from  当前用户id
     */
    protected function isAuth($user_id, $user_id_from) {
    	//1 一亿元用户直接查询
        $user_auth = User_auth::find()->where(['user_id' => $user_id, 'is_up' => 2, 'from_user_id' => $user_id_from, 'is_yyy' => 1])->count();
        if ($user_auth > 0) {
            return true;
        }
		
		//2 从微信转一下再查
        $user = User::findOne($user_id_from);
        $user_wx = $user->userwx;
        if (!empty($user_wx)) {
            $user_auth_wx = User_auth::find()->where(['user_id' => $user_id, 'is_up' => 2, 'from_user_id' => $user_wx->id, 'is_yyy' => 2])->count();
            return $user_auth_wx > 0;
        } else {
            return false;
        }
    }
	
	/**
	 * 获取我的校友数量
	 * @param int $user_id
	 * @param int $school_id
	 * @return int 同校友数量
	 */
	protected function mySchoolCount($user_id, $school_id){
		// 获取学校相同的好友
		return User::find() -> where(['school_id' => $school_id,'school_valid'=>2])
							-> andWhere(['!=','user_id',$user_id])
							-> orderBy('user_id')
							-> count();
	}
	/**
	 * 获取我的校友列表
	 * @param int $user_id
	 * @param int $school_id
	 * @return []
	 */
	protected function mySchool($user_id, $school_id, $offset=0, $limit=100){
		// 获取学校相同的好友
		return User::find() -> where(['school_id' => $school_id,'school_valid'=>2])
							-> andWhere(['!=','user_id',$user_id])
							-> orderBy('user_id')
							-> offset($offset)
							-> limit($limit)
							-> all();
	}
	/**
	 * 获取我的公司好友数量
	 * @param int $user_id
	 * @param string $company
	 * @return int 同公司数量
	 */
	protected function myCompanyCount($user_id, $company){
		// 获取公司相同的数量
		return User::find() -> where(['company' => $company])
							-> andWhere(['!=','user_id',$user_id])
							-> orderBy('user_id')
							-> count();
	}
	/**
	 * 获取我的公司好友列表
	 * @param int $user_id
	 * @param string $company
	 * @return []
	 */
	protected function myCompany($user_id, $company, $offset=0, $limit=100){
		// 获取学校相同的好友
		return   User::find()   -> where(['company' => $company])
								-> andWhere(['!=','user_id',$user_id])
								-> orderBy('user_id')
								-> offset($offset)
								-> limit($limit)
								-> all();
	}
	/******************************end 从别的model中移来的方法*******************/
	
	
	
	
}