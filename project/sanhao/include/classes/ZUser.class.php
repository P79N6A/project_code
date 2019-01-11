<?php
/**
 * @author shwdai@gmail.com
 * @modified 2010-05-05
 */
class ZUser
{
	const SECRET_KEY = '@4!@#$%@';

	static public function GenPassword($p) {
		return md5($p . self::SECRET_KEY);
	}

	static public function Create($user_row, $uc=true) {
//		if (function_exists('zuitu_uc_register') && $uc) {
//			$pp = $user_row['password'];
//			$em = $user_row['email'];
//			$un = $user_row['username'];
//			$ret = zuitu_uc_register($em, $un, $pp);
//			if (!$ret) return false;
//		}

		$user_row['password'] = self::GenPassword($user_row['password']);
		$user_row['create_time'] = $user_row['login_time'] = time();
		$user_row['ip'] = Utility::GetRemoteIp();
		$user_row['secret'] = md5(rand(1000000,9999999).time().$user_row['email']);
		$user_row['id'] = DB::Insert('user', $user_row);
		$_rid = abs(intval(cookieget('_rid')));
		if ($_rid && $user_row['id']) {
			$r_user = Table::Fetch('user', $_rid);
			if ( $r_user ) {
				ZInvite::Create($r_user, $user_row);
				ZCredit::Invite($r_user['id']);
			}
		}
		if ( $user_row['id'] == 1 ) {
			Table::UpdateCache('user', $user_row['id'], array(
						'manager'=>'Y',
						'secret' => '',
						));
		}
		return $user_row['id'];
	}

	static public function GetUser($user_id) {
		if (!$user_id) return array();
		return DB::GetTableRow('user', array('id' => $user_id));
	}

	static public function GetLoginCookie($cname='ru') {
		$cv = cookieget($cname);
		if ($cv) {
			$zone = base64_decode($cv);
			$p = explode('@', $zone, 2);
			return DB::GetTableRow('user', array(
				'id' => $p[0],
				'password' => $p[1],
			));
		}
		return Array();
	}

	static public function Modify($user_id, $newuser=array()) {
		if (!$user_id) return;
		/* uc */
		$curuser = Table::Fetch('user', $user_id);
//		if ($newuser['password'] && function_exists('zuitu_uc_updatepw') ) {
//			$em = $curuser['email'];
//			$un = $newuser['username'];
//			$pp = $newuser['password'];
//			if ( ! zuitu_uc_updatepw($em, $un, $pp)) {
//				return false;
//			}
//		}

		/* tuan db */
		$table = new Table('user', $newuser);
		$table->SetPk('id', $user_id);
		if ($table->password) {
			$plainpass = $table->password;
			$table->password = self::GenPassword($table->password);
		}
		return $table->Update( array_keys($newuser) );
	}

	static public function GetLogin($email, $unpass, $en=true) {
		if($en) $password = self::GenPassword($unpass);
		if(is_array($email)) return array();
		//$field = strpos($email, '@') ? 'email' : 'username';
		$zuituuser = DB::GetTableRow('user', array( "email='".$email."' OR username='".$email."' OR mobile='".$email."'",'password'=>$password
		));
		if ($zuituuser)  return $zuituuser;
//		if (function_exists('zuitu_uc_login')) {
//			return zuitu_uc_login($email, $unpass);
//		}
		return array();
	}
	static public function GetManageLogin($email, $unpass, $en=true) {
		if($en) $password = self::GenPassword($unpass);
		if(is_array($email)) return array();
		//$field = strpos($email, '@') ? 'email' : 'username';
		$zuituuser = DB::GetTableRow('jx_manages', array( "email='".$email."' OR username='".$email."' OR mobile='".$email."'",'password'=>$password
		));
		if ($zuituuser)  return $zuituuser;
		return array();
	}

	static public function SynLogin($email, $unpass) {
//		if (function_exists('zuitu_uc_synlogin')) {
//			return zuitu_uc_synlogin($email, $unpass);
//		}
		return true;
	}

	static public function SynLogout() {
//		if (function_exists('zuitu_uc_synlogout')) {
//			return zuitu_uc_synlogout();
//		}
		return true;
	}
        static public function Check_alifast($alipay_id,$alipay_name) {

		$aliuser = DB::GetTableRow('user', array(
					'alipay_id' => $alipay_id,
		));

		if ($aliuser) return $aliuser;

                $user['username'] = $alipay_id;
                $user['realname'] = $alipay_name;
                $user['alipay_id'] = $alipay_id;
		$user['create_time'] = $user['login_time'] = time();
		$user['ip'] = Utility::GetRemoteIp();
		
		$ali_user['id'] = DB::Insert('user', $user);

		$aliuser = DB::GetTableRow('user', array(
					   'id' => $ali_user['id'],
		));
		return $aliuser;
	}
	//获取表情
	static public function getExpression(){
		$expression = array(
				'[微笑]' => '/static/qzone/1.gif',
				'[撇嘴]' => '/static/qzone/2.gif',
				'[色]'  => '/static/qzone/3.gif',
				'[发呆]' => '/static/qzone/4.gif',
				'[得意]' => '/static/qzone/5.gif',
				'[流泪]' => '/static/qzone/6.gif',
				'[害羞]' => '/static/qzone/7.gif',
				'[闭嘴]' => '/static/qzone/8.gif',
				'[睡]'  => '/static/qzone/9.gif',
				'[大哭]' => '/static/qzone/10.gif',
				'[尴尬]' => '/static/qzone/11.gif',
				'[发怒]' => '/static/qzone/12.gif',
				'[调皮]' => '/static/qzone/13.gif',
				'[龇牙]' => '/static/qzone/14.gif',
				'[惊讶]' => '/static/qzone/15.gif',
				'[难过]' => '/static/qzone/16.gif',
				'[酷]'  => '/static/qzone/17.gif',
				'[冷汗]' => '/static/qzone/18.gif',
				'[抓狂]' => '/static/qzone/19.gif',
				'[吐]'  => '/static/qzone/20.gif',
				'[偷笑]' => '/static/qzone/21.gif',
				'[可爱]' => '/static/qzone/22.gif',
				'[白眼]' => '/static/qzone/23.gif',
				'[傲慢]' => '/static/qzone/24.gif',
				'[饥饿]' => '/static/qzone/25.gif',
				'[困]'  => '/static/qzone/26.gif',
				'[惊恐]' => '/static/qzone/27.gif',
				'[流汗]' => '/static/qzone/28.gif',
				'[憨笑]' => '/static/qzone/29.gif',
				'[大兵]' => '/static/qzone/30.gif',
				'[奋斗]' => '/static/qzone/31.gif',
				'[咒骂]' => '/static/qzone/32.gif',
				'[疑问]' => '/static/qzone/33.gif',
				'[嘘...]' => '/static/qzone/34.gif',
				'[晕]'=> '/static/qzone/35.gif',
				'[折磨]'  => '/static/qzone/36.gif',
				'[衰]' => '/static/qzone/37.gif',
				'[骷髅]' => '/static/qzone/38.gif',
				'[敲打]' => '/static/qzone/39.gif',
				'[再见]' => '/static/qzone/40.gif',
				'[擦汗]' => '/static/qzone/41.gif',
				'[抠鼻]' => '/static/qzone/42.gif',
				'[鼓掌]' => '/static/qzone/43.gif',
				'[糗大了]'=> '/static/qzone/44.gif',
				'[坏笑]'=> '/static/qzone/45.gif',
				'[左哼哼]'=> '/static/qzone/46.gif',
				'[右哼哼]'=> '/static/qzone/47.gif',
				'[哈欠]' => '/static/qzone/48.gif',
				'[鄙视]' => '/static/qzone/49.gif',
				'[委屈]' => '/static/qzone/50.gif',
				'[快哭了]'=> '/static/qzone/51.gif',
				'[阴险]' => '/static/qzone/52.gif',
				'[亲亲]' => '/static/qzone/53.gif',
				'[吓]'  => '/static/qzone/54.gif',
				'[可怜]' => '/static/qzone/55.gif',
				'[菜刀]' => '/static/qzone/56.gif',
				'[西瓜]' => '/static/qzone/57.gif',
				'[啤酒]' => '/static/qzone/58.gif',
				'[篮球]' => '/static/qzone/59.gif',
				'[乒乓]' => '/static/qzone/60.gif',
				'[咖啡]' => '/static/qzone/61.gif',
				'[饭]'  => '/static/qzone/62.gif',
				'[猪头]' => '/static/qzone/63.gif',
		);
		return $expression ;
	}
}
