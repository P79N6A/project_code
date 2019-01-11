<?php
namespace app\models\news;
/**
 * 继承自己Friend类。专注于处理两两好友之间在的关系
 */

/**
* This is the model class for table "yi_friends".
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

class FriendPairs extends Friends
{
	/**
	 * 会员id
	 */
    private $user_id;
    private $fuser_id;
	
	/**
	 * user表模型类
	 */
	public $userInfo;
	public $fuserInfo;
	
	/**
	 * friends表模型类
	 */
    public $user;
    public $fuser;
	
	/**
	 * 设置Friend
	 */
	public function setPair($user_id, $fuser_id){
		//1 参数检测
		$this->user_id  = intval($user_id);
		$this->fuser_id = intval($fuser_id);
		if( !$this->user_id || !$this->fuser_id ){
			return FALSE;
		}
		
		//2 获取基本信息
        $this->userInfo = $this->getUserInfo($user_id);
        if (!$this->userInfo) {
            return $this->returnError(false,"没有用户未找到");
        }
        $this->fuserInfo= $this->getUserInfo($fuser_id);
        if (!$this->fuserInfo) {
            return $this->returnError(false,"好友用户未找到");
        }
		
		//3 获取好友信息
		$this->getPair();
		return true;
	}
	public function isFriend(){
		return $this->user && $this->fuser;
	}
	/**
	 * 获取一对好友
	 */
	public function getPair(){
		//1 获取好友
		if($this->user && $this->fuser){
			return true;
		}
		$user = $this -> getFriend($this->user_id,  $this->fuser_id);
		$fuser= $this -> getFriend($this->fuser_id, $this->user_id);
		
		//2 要么全有，要么全没有
		if( $user && $fuser ){
			$this->user = $user;
			$this->fuser= $fuser; 
			return true;
		}elseif($user || $fuser){
			// 有一个没有，那么删除另外的一个关系
			$this->deletePair();
			return false;
		}else{
			return false;
		}
	}
	/**
	 * 添加一对好友
	 * 注意：此方法不检测是否存在, 若需要请使用getaddPair
	 */
	public function addPair(){
		//1 获取两人之间的关系
		if($this->user || $this->fuser){
			return $this->returnError(false,"已经存在了，无法再次添加");
		}
		
		//2 获取现在的关系
        $relations = $this->getPairRelation();		
		//$this->beginTransaction();// 事务开启
		
        //3 我添加好友信息
        $user = new Friends();
		$res = $user->add($relations[$this->user_id]);
		if(!$res){
			//$this->endTransaction(false);
			return null;
		}
		
        //4 好友添加我的信息
        $fuser = new Friends();
		$res = $fuser->add($relations[$this->fuser_id]);
		if(!$res){
			//$this->endTransaction(false);
			return null;
		}
		
		//5 返回结果
		//$this->endTransaction(true);
		
		$this->user = $user;
		$this->fuser= $fuser; 
		return true;
	}
	/**
	 * 更新两个好友之间的关系
	 */
	public function updatePair(){
		//1 参数验证
		if(!$this->user || !$this->fuser){
			return $this->returnError(false,"没有找到好友信息,您可以尝试添加");
		}
		
        //2 获取现在的好友关系
        $relations = $this->getPairRelation();		
		//$this->beginTransaction();// 事务开启
		
		//3 更新我与好友的关系
		$this->user -> attributes = $relations[$this->user_id];
		$this->user->save();
		
        //3 更新好友与我的关系
		$this->fuser -> attributes = $relations[$this->fuser_id];
		$this->fuser->save();
		
		//4 返回结果
		//$this->endTransaction(true);
		return true;
	}
	/**
	 * 刷新好友关系
	 */
	public function refresh(){
		$isfrd = $this -> isFriend();
        if( $isfrd ){
        	return $this -> updatePair();
        }else{
        	return $this -> addPair();
        }
	}
	/**
	 * 检测两者的关系。从会员表中获取
	 */
	public function getPairRelation(){
		//1 若一方不存在，则两者没有任何关系
		$user_id = $this->user_id;
		$fuser_id = $this->fuser_id;
		$relation = $this->getInitRelation(); //设置初步状态
		
		//1 获取认证, 被认证
		if ($this -> isAuth($fuser_id, $user_id) ){
			// $user_id认证了$fuser_id
			$relation['auth'] = 1;
		}
		if ($this -> isAuth($user_id, $fuser_id) ){
			//  $user_id 被 $fuser_id 认证
			$relation['authed'] = 1;
		}
		
		//2 公司关系
		if($this->userInfo -> company == $this->fuserInfo -> company){
			$relation['same_company'] = 1;
		}
		
		//3 学校关系
		if($this->userInfo -> school_id == $this->fuserInfo -> school_id){
			$relation['same_school'] = 1;
		}
		
		//4 邀请关系
		if($this->userInfo -> invite_code == $this->fuserInfo -> from_code){
			$relation['invite'] = 1;// 主动邀请
		}elseif($this->userInfo -> from_code == $this->fuserInfo -> invite_code){
			$relation['invite'] = 1;// 被邀请
		}
		
		
		//6 检测是否存在关系
		$hasRelation = $this->chkRelation($relation);
		if(!$hasRelation){
			return null;
		}
		
		
		//7 获取好友类型（几度好友)
		if($relation['auth'] == 1 && $relation['authed'] == 1){
			$relation['type'] = 1;// 1度好友
		}elseif($relation['auth'] == 1 || $relation['authed'] == 1){
			$relation['type'] = 2;// 2度好友
		}else{
			$relation['type'] = 3;// 其它关系
		}
		
		
		//8 我与好友的关系
		$nowTime = date('Y-m-d H:i:s');
		$relation1 = [
			'user_id' 	=> $user_id,
			'fuser_id' 	=> $fuser_id,
			'type' 		=> $relation['type'],
			'auth' 		=> isset($relation['auth'])   ? $relation['auth']   : 0,
			'authed' 	=> isset($relation['authed']) ? $relation['authed'] : 0,
			'company' 	=> isset($this->fuserInfo->company) && $this->fuserInfo->company  ? $this->fuserInfo->company : '', // 存好友的公司
			'same_company' 	=> isset($relation['same_company']) ? $relation['same_company'] : 0,
			'school_id' 	=> isset($this->fuserInfo->school_id) && $this->fuserInfo->school_valid  ? $this->fuserInfo->school_id : 0,  // 存好友的学校
			'same_school' 	=> isset($relation['same_school'])  ? $relation['same_school']   : 0,
			'invite' 		=> isset($relation['invite']) ? $relation['invite'] : 0,
			'like' 			=> 0,
			'create_time'   => $nowTime,
			'modify_time'   => $nowTime,
		];
		
		//9 好友与我的关系
		$relation2 = [
			'user_id' 	=> $fuser_id, // 调换位置
			'fuser_id' 	=> $user_id,  // 调换位置
			'type' 		=> $relation['type'],
			'auth' 		=> isset($relation['authed']) ? $relation['authed'] : 0,// 调换位置
			'authed' 	=> isset($relation['auth'])   ? $relation['auth']   : 0,// 调换位置
			'company' 	=> isset($this->userInfo->company) && $this->userInfo->company ? $this->userInfo->company : '', // 存我的公司
			'same_company' 	=> isset($relation['same_company']) ? $relation['same_company'] : 0,
			'school_id' 	=> isset($this->userInfo->school_id) && $this->userInfo->school_valid ? $this->userInfo->school_id : 0, // 存我的学校
			'same_school' 	=> isset($relation['same_school'])   ? $relation['same_school']   : 0,
			'invite' 		=> isset($relation['invite']) ? $relation['invite'] : 0,
			'like' 			=> isset($relation['like'])   ? $relation['like']   : 0,
			'create_time'   => $nowTime,
			'modify_time'   => $nowTime,
		];
		
		//10  组合结果
		$relations = [
			$user_id  => $relation1,
			$fuser_id => $relation2
		];
		
		return $relations;
	}
	
        
	/**
	 * 删除我的一个好友
	 */
	public function deletePair(){
		if( $this ->user ){
			$this ->user -> delete();
		}
		if( $this ->fuser ){
			$this ->fuser -> delete();
		}
		return true;
	}
}