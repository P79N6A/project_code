<?php
/* import other */
import('configure');
//import('current');
import('rewrite');
//import('utility');
//import('mailer');
import('pay');
import('sms');
//import('upgrade');
//import('uc');
//import('cron');
//import('logger');

function template($tFile) {
	global $INI;
	if ( 0===strpos($tFile, 'manage') ) {
		return __template($tFile);
	}
	if ($INI['skin']['template']) {
		$templatedir = DIR_TEMPLATE. '/' . $INI['skin']['template'];
		$checkfile = $templatedir . '/html_header.html';
		if ( file_exists($checkfile) ) {
			return __template($INI['skin']['template'].'/'.$tFile);
		}
	}
	return __template($tFile);
}

function render($tFile, $vs=array()) {
    ob_start();
    foreach($GLOBALS AS $_k=>$_v) {
        ${$_k} = $_v;
    }
	foreach($vs AS $_k=>$_v) {
		${$_k} = $_v;
	}
	include template($tFile);
    return render_hook(ob_get_clean());
}

function render_hook($c) {
	global $INI;
	$c = preg_replace('#href="/#i', 'href="'.WEB_ROOT.'/', $c);
	$c = preg_replace('#src="/#i', 'src="'.WEB_ROOT.'/', $c);
	$c = preg_replace('#action="/#i', 'action="'.WEB_ROOT.'/', $c);

	/* theme */
	$page = strval($_SERVER['REQUEST_URI']);
	if($INI['skin']['theme'] && !preg_match('#/manage/#i',$page)) {
		$themedir = WWW_ROOT. '/static/theme/' . $INI['skin']['theme'];
		$checkfile = $themedir. '/css/index.css';
		if ( file_exists($checkfile) ) {
			$c = preg_replace('#/static/css/#', "/static/theme/{$INI['skin']['theme']}/css/", $c);
			$c = preg_replace('#/static/img/#', "/static/theme/{$INI['skin']['theme']}/img/", $c);
		}
	}
	$c = preg_replace('#([\'\=\"]+)/static/#', "$1{$INI['system']['cssprefix']}/static/", $c);
	if (strtolower(cookieget('locale','zh_cn'))=='zh_tw') {
		require_once(DIR_FUNCTION  . '/tradition.php');
		$c = str_replace(explode('|',$_charset_simple), explode('|',$_charset_tradition),$c);
	}
	/* encode id */
	$c = rewrite_hook($c);
	$c = obscure_rep($c);
	return $c;
}

function output_hook($c) {
	global $INI;
	if ( 0==abs(intval($INI['system']['gzip'])))  die($c);
	$HTTP_ACCEPT_ENCODING = $_SERVER["HTTP_ACCEPT_ENCODING"]; 
	if( strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false ) 
		$encoding = 'x-gzip'; 
	else if( strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false ) 
		$encoding = 'gzip'; 
	else $encoding == false;
	if (function_exists('gzencode')&&$encoding) {
		$c = gzencode($c);
		header("Content-Encoding: {$encoding}"); 
	}
	$length = strlen($c);
	header("Content-Length: {$length}");
	die($c);
}

$lang_properties = array();
function I($key) { 
    global $lang_properties, $LC;
    if (!$lang_properties) {
        $ini = DIR_ROOT . '/i18n/' . $LC. '/properties.ini';
        $lang_properties = Config::Instance($ini);
    }
    return isset($lang_properties[$key]) ?
        $lang_properties[$key] : $key;
}

function json($data, $type='eval') {
    $type = strtolower($type);
    $allow = array('eval','alert','updater','dialog','mix', 'refresh');
    if (false==in_array($type, $allow))
        return false;
    Output::Json(array( 'data' => $data, 'type' => $type,));
}

function redirect($url=null, $notice=null, $error=null) {
	$url = $url ? obscure_rep($url) : $_SERVER['HTTP_REFERER'];
	$url = $url ? $url : '/';
	if ($notice) Session::Set('notice', $notice);
	if ($error) Session::Set('error', $error);
	//后台管理不做处理，前端展示时跳转到伪静态的html页面
//	if ( ! preg_match('#/manage/#i',$url, $m) ) {
//		if( strpos( $url , '.php' ) ){
//			if( $pos = strpos( $url , "?" ) ){
//				//check.php?id=100
//				$url = substr( $url , 0 , ($pos-4) ) . "/".substr( $url , ($pos+4) ) . ".html" ;
//			}else{
//				$url = substr( $url , 0 , -4 ) . ".html" ;
//			}
//		}
//	}
    header("Location: {$url}");
    exit;
}
function write_php_file($array, $filename=null){
	$v = "<?php\r\n\$INI = ";
	$v .= var_export($array, true);
	$v .=";\r\n?>";
	return file_put_contents($filename, $v);
}

function write_ini_file($array, $filename=null){   
	$ok = null;   
	if ($filename) {
		$s =  ";;;;;;;;;;;;;;;;;;\r\n";
		$s .= ";; SYS_INIFILE\r\n";
		$s .= ";;;;;;;;;;;;;;;;;;\r\n";
	}
	foreach($array as $k=>$v) {   
		if(is_array($v))   { 
			if($k != $ok) {   
				$s  .=  "\r\n[{$k}]\r\n";
				$ok = $k;   
			} 
			$s .= write_ini_file($v);
		}else   {   
			if(trim($v) != $v || strstr($v,"["))
				$v = "\"{$v}\"";   
			$s .=  "$k = \"{$v}\"\r\n";
		} 
	}

	if(!$filename) return $s;   
	return file_put_contents($filename, $s);
}   

function save_config($type='ini') {
	return configure_save();
	global $INI; $q = ZSystem::GetSaveINI($INI);
	if ( strtoupper($type) == 'INI' ) {
		if (!is_writeable(SYS_INIFILE)) return false;
		return write_ini_file($q, SYS_INIFILE);
	} 
	if ( strtoupper($type) == 'PHP' ) {
		if (!is_writeable(SYS_PHPFILE)) return false;
		return write_php_file($q, SYS_PHPFILE);
	} 
	return false;
}

function save_system($ini) {
	$system = Table::Fetch('system', 1);
	$ini = ZSystem::GetUnsetINI($ini);
	$value = Utility::ExtraEncode($ini);
	$table = new Table('system', array('value'=>$value));
	if ( $system ) $table->SetPK('id', 1);
	return $table->update(array( 'value'));
}

/* user relative */
function need_login($wap=false) {
	if ( isset($_SESSION['user_id']) ) {
		if (is_post()) {
			unset($_SESSION['loginpage']);
			unset($_SESSION['loginpagepost']);
		}
		return $_SESSION['user_id'];
	}
	if ( is_get() ) {
		Session::Set('loginpage', $_SERVER['REQUEST_URI']);
	} else {
		Session::Set('loginpage', $_SERVER['HTTP_REFERER']);
		Session::Set('loginpagepost', json_encode($_POST));
	}
	if (true===$wap) {
		return redirect('login.php');	
	}
	redirect( WEB_ROOT . '/account/login.php' );
}
function need_checksns() {
	if( $_SESSION['user_id'] == '-1' )
	{
		$condition = array( 'sns_nickname' => $_SESSION["mobile"], 'sns_type' => $_SESSION['sns_type'] );
		$aField = DB::LimitQuery('jx_users_sns', array(
			'condition' => $condition,
			'one' => true,
		));
		redirect( WEB_ROOT . '/account/register.php?id='.$aField['id'] );
	}
	else 
	{
		return true;
	}
}

function need_post() {
	return is_post() ? true : redirect(WEB_ROOT . '/index.php');
}
function need_manager($super=false) {
	if ( ! is_manager() ) {
		redirect( WEB_ROOT . '/manage/login.php' );
	}
	if ( ! $super ) return true;
	if ( abs(intval($_SESSION['user_id'])) == 1 ) return true;
	return redirect( WEB_ROOT . '/manage/misc/index.php');
}
function need_partner() {
	return is_partner() ? true : redirect( WEB_ROOT . '/biz/login.php');
}

function need_open($b=true) {
	if (true===$b) {
		return true;
	}
	if ($AJAX) json('本功能未开放', 'alert');
	Session::Set('error', '你访问的功能页未开放');
	redirect( WEB_ROOT . '/index.php');
}

function need_auth($b=true) {
	//暂时不用此方法做权限处理
	return true;
	global $AJAX, $INI, $login_user;
	if (is_string($b)) {
		$auths = $INI['authorization'][$login_user['id']];
		$bs = explode('|', $b);
		$b = is_manager(true); 
		if ($b) return true;
		foreach($bs AS $bo) if(!$b) $b = in_array($bo, $auths);
	}
	if (true===$b) {
		return true;
	}
	if ($AJAX) json('无权操作', 'alert');
	die(include template('manage_misc_noright'));
}

function is_manager($super=false, $weak=false) {
	global $login_user;
	if ( $weak===false && 
			( !$_SESSION['admin_id'] 
			  || $_SESSION['admin_id'] != $login_user['id']) ) {
		return false;
	}
	if ( ! $super ) return ($login_user['manager'] = 'Y');
	return $login_user['id'] == 1;
}
function is_partner() {
	return ($_SESSION['partner_id']>0);
}

function is_newbie(){ return (cookieget('newbie')!='N'); }
function is_get() { return ! is_post(); }
function is_post() {
	return strtoupper($_SERVER['REQUEST_METHOD']) == 'POST';
}

function is_login() {
	return isset($_SESSION['user_id']);
}

function get_loginpage($default=null) {
	$loginpage = Session::Get('loginpage', true);
	if ($loginpage)  return $loginpage;
	if ($default) return $default;
	return WEB_ROOT . '/index.php';
}

function cookie_city($city) {
	global $hotcities;
	if($city) { 
		cookieset('city', $city['id']);
		return $city;
	} 
	$city_id = cookieget('city'); 
	$city = Table::Fetch('category', $city_id);
	if (!$city) $city = get_city();
	if (!$city) $city = array_shift($hotcities);
	if ($city) return cookie_city($city);
	return $city;
}

function ename_city($ename=null) {
	return DB::LimitQuery('category', array(
		'condition' => array(
			'zone' => 'city',
			'ename' => $ename,
		),
		'one' => true,
	));
}

function cookieset($k, $v, $expire=0) {
	$pre = substr(md5($_SERVER['HTTP_HOST']),0,4);
	$k = "{$pre}_{$k}";
	if ($expire==0) {
		$expire = time() + 365 * 86400;
	} else {
		$expire += time();
	}
	setCookie($k, $v, $expire, '/');
}

function cookieget($k, $default='') {
	$pre = substr(md5($_SERVER['HTTP_HOST']),0,4);
	$k = "{$pre}_{$k}";
	return isset($_COOKIE[$k]) ? strval($_COOKIE[$k]) : $default;
}

function moneyit($k) {
	return rtrim(rtrim(sprintf('%.2f',$k), '0'), '.');
}

function debug($v, $e=false) {
	global $login_user_id;
	if ($login_user_id==100000) {
		echo "<pre>";
		var_dump( $v);
		if($e) exit;
	}
}

function getparam($index=0, $default=0) {
	if (is_numeric($default)) {
		$v = abs(intval($_GET['param'][$index]));
	} else $v = strval($_GET['param'][$index]);
	return $v ? $v : $default;
}
function getpage() {
	$c = abs(intval($_GET['page']));
	return $c ? $c : 1;
}
function pagestring($count, $pagesize, $wap=false) {
	$p = new Pager($count, $pagesize, 'page');
	if ($wap) {
		return array($pagesize, $p->offset, $p->genWap());
	}
	return array($pagesize, $p->offset, $p->genBasic());
}
function pagestring2($count, $pagesize, $wap=false) {
	$p = new Pager($count, $pagesize, 'page');
	if ($wap) {
		return array($pagesize, $p->offset, $p->genWap2());
	}
	return array($pagesize, $p->offset, $p->GenBasic2());
}

function uencode($u) {
	return base64_encode(urlEncode($u));
}
function udecode($u) {
	return urlDecode(base64_decode($u));
}

/* share link */
function share_renren($team) {
	global $login_user_id;
	global $INI;
	if ($team)  {
		$query = array(
				'link' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$login_user_id}",
				'title' => $team['title'],
				);
	}
	else {
		$query = array(
				'link' => $INI['system']['wwwprefix'] . "/r.php?r={$login_user_id}",
				'title' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}

	$query = http_build_query($query);
	return 'http://share.renren.com/share/buttonshare.do?'.$query;
}

function share_kaixin($team) {
	global $login_user_id;
	global $INI;
	if ($team)  {
		$query = array(
				'rurl' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$login_user_id}",
				'rtitle' => $team['title'],
				'rcontent' => strip_tags($team['summary']),
				);
	}
	else {
		$query = array(
				'rurl' => $INI['system']['wwwprefix'] . "/r.php?r={$login_user_id}",
				'rtitle' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				'rcontent' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}
	$query = http_build_query($query);
	return 'http://www.kaixin001.com/repaste/share.php?'.$query;
}

function share_douban($team) {
	global $login_user_id;
	global $INI;
	if ($team)  {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$login_user_id}",
				'title' => $team['title'],
				);
	}
	else {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/r.php?r={$login_user_id}",
				'title' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}
	$query = http_build_query($query);
	return 'http://www.douban.com/recommend/?'.$query;
}

function share_sina($team) {
	global $login_user_id;
	global $INI;
	if ($team)  {
		$query = array(
				'appkey'=>3627244478 ,
				'url' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$login_user_id}",
				'title' => $team['title'],
				);
	}
	else {
		$query = array(
				'appkey'=>3627244478 ,
				'url' => $INI['system']['wwwprefix'] . "/r.php?r={$login_user_id}",
				'title' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}
	$query = http_build_query($query);
	return 'http://v.t.sina.com.cn/share/share.php?'.$query;
}

function share_tencent($team) {
	global $login_user_id;
	global $INI;
	if ($team)  {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$login_user_id}",
				'title' => $team['title'],
				);
	}
	else {
		$query = array(
				'url' => $INI['system']['wwwprefix'] . "/r.php?r={$login_user_id}",
				'title' => $INI['system']['sitename'] . '(' .$INI['system']['wwwprefix']. ')',
				);
	}
	$query = http_build_query($query);
	return 'http://v.t.qq.com/share/share.php?'.$query;
}
function share_mail($team) {
	global $login_user_id;
	global $INI;
	if (!$team) {
		$team = array(
				'title' => $INI['system']['sitename'] . '(' . $INI['system']['wwwprefix'] . ')',
				);
	}
	$pre[] = "发现一好网站--{$INI['system']['sitename']}，他们每天组织一次团购，超值！";
	if ( $team['id'] ) {
		$pre[] = "今天的团购是：{$team['title']}";
		$pre[] = "我想你会感兴趣的：";
		$pre[] = $INI['system']['wwwprefix'] . "/team.php?id={$team['id']}&r={$login_user_id}";
		$pre = mb_convert_encoding(join("\n\n", $pre), 'GBK', 'UTF-8');
		$sub = "有兴趣吗：{$team['title']}";
	} else {
		$sub = $pre[] = $team['title'];
	}
	$sub = mb_convert_encoding($sub, 'GBK', 'UTF-8');
	$query = array( 'subject' => $sub, 'body' => $pre, );
	$query = http_build_query($query);
	return 'mailto:?'.$query;
}

function domainit($url) {
	if(strpos($url,'//')) { preg_match('#[//]([^/]+)#', $url, $m);
} else { preg_match('#[//]?([^/]+)#', $url, $m); }
return $m[1];
}

function subtostring( $string , $length=0 ,$type=false )
{
	$string = strip_tags( $string ) ;
	if( empty( $string ) ) return '';
	if( !$length ) return $string ;
	
	if( !$type ){
		$j = 0;
		$newstr = '';
		for($i = 0; $i <= mb_strlen($string); $i++) {
			if( $j > $length ){
				$newstr .= '…' ;
				break;
			}
			$word = mb_substr($string, $i, 1, 'utf-8');
			if(ord($word) > 127) {
				$j = $j + 1;
			} else {
				$j = $j + 0.5;
			}
			if( $j <= $length ){
				$newstr .= $word;
			}
		}
	}else{
		if( mb_strlen( $string ) > $length ){
			$newstr = mb_substr( $string , 0 , $length );
			$newstr .= "…";
		}else{
			$newstr = $string ;
		}
	}

		return $newstr;
}

// that the recursive feature on mkdir() is broken with PHP 5.0.4 for
function RecursiveMkdir($path) {
	if (!file_exists($path)) {
		RecursiveMkdir(dirname($path));
		@mkdir($path, 0777);
	}
}

function upload_image($input, $image=null, $type='team', $scale=false) {
	$year = date('Y'); $day = date('md'); $n = time().rand(1000,9999).'.jpg';
	$z = $_FILES[$input];
	if ($z && strpos($z['type'], 'image')===0 && $z['error']==0) {
		if (!$image) { 
			RecursiveMkdir( IMG_ROOT . '/' . "{$type}/{$year}/{$day}" );
			$image = "{$type}/{$year}/{$day}/{$n}";
			$path = IMG_ROOT . '/' . $image;
		} else {
			RecursiveMkdir( dirname(IMG_ROOT .'/' .$image) );
			$path = IMG_ROOT . '/' .$image;
		}
		if ($type=='user') {
			Image::Convert($z['tmp_name'], $path, 48, 48, Image::MODE_CUT);
		} 
		else if($type=='team') {
			move_uploaded_file($z['tmp_name'], $path);
		}
		if($type=='team' && $scale) {
			$npath = preg_replace('#(\d+)\.(\w+)$#', "\\1_index.\\2", $path); 
			Image::Convert($path, $npath, 200, 120, Image::MODE_CUT);
			
			$npath = preg_replace('#(\d+)\.(\w+)$#', "\\1_all.\\2", $path); 
			Image::Convert($path, $npath, 315, 190, Image::MODE_CUT);
		}
		return $image;
	} 
	return $image;
}


//创建图片
function createimg($img, $type) {
	if(($type == 'image/jpeg') || ($type == 'image/pjpeg')) {
		$imgs = imagecreatefromjpeg($img);
	} elseif(($type == 'image/png') || ($type == 'image/x-png')) {
		$imgs = imagecreatefrompng($img);
	} elseif($type == 'image/gif') {
		$imgs = imagecreatefromgif($img);
	}
	return $imgs;
}


/**
 * @abstract 按照大小缩略图片
 * @param 图片，宽，高，文件名，类型，是否加水印，水印字符串，透明度
 * */
function thumbimg($imgs, $maxwidth, $maxheight, $filename, $type, $iswatermark=0, $logo="", $watermark = 100) {
	$imgwidth = imagesx($imgs);
	$imgheight = imagesy($imgs);
	if( $maxheight <= 0 ){
		//按照固定宽度缩
		if($imgwidth > $maxwidth) {
			$ratio = $maxwidth / $imgwidth;
		} else {
			$ratio = 1;
		}
		$newwidth = $imgwidth * $ratio;
		$newheight = $imgheight * $ratio;
		$x = ceil(($maxwidth - $newwidth) / 2);
		$y = ceil(($maxheight - $newheight) / 2);

		$newimg = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled($newimg, $imgs, 0, 0, 0, 0, $newwidth, $newheight, $imgwidth, $imgheight);
	}else{
		//按照固定宽和高缩略
		if($imgwidth > $maxwidth) {
			$ratio = $maxwidth / $imgwidth;
			$newwidth = $imgwidth * $ratio;
			if($maxheight == 300 && $imgheight < 300){
            	$newheight = $imgheight;
			}else{
				$newheight = $maxheight;
			}
		} else {
			$ratio = $maxwidth / $imgwidth;
			$newwidth = $imgwidth * $ratio;
            $newheight = $imgheight * $ratio;
		}

		$newimg = imagecreatetruecolor($newwidth, $newheight);
		imagecopyresampled($newimg, $imgs, 0, 0, 0, 0, $newwidth, $newheight, $imgwidth, $imgheight);
	}
		
	$flag = self::showimg($newimg, $filename, $watermark, $type);
	ImageDestroy($newimg);
	return $flag ;
	
}

function imgtype($type) {
	$arr = array('image/jpeg', 'image/pjpeg', 'image/png', 'image/x-png', 'image/gif');
	return in_array($type, $arr);
}

//图片，目录，文件名，类型，宽，高，是否是原图，是否加水印，水印字符串
function image_upload($img, $dir, $filename, $type, $width = 200, $height = 0, $isartwork = 0, $iswatermark = 0, $logo = 0) {
	RecursiveMkdir($dir);
	$subfix = imgtype($type);
	if($subfix) {
		if($isartwork == 0) {
			//$filename = $this->randnum(20);
			$imgs = createimg($img, $type);
			$flag = thumbimg($imgs, $width, $height, $dir.'/'.$filename, $type, $iswatermark, $logo);
			ImageDestroy($imgs);
			return $flag;
		} else {
			//原图
			return move_uploaded_file($img, $dir.'/'.$filename);
		}
	}
}
	
	
/**
 * 生成缩略图
 *
 * @param unknown_type $img       源文件
 * @param unknown_type $maxwidth  缩略图宽
 * @param unknown_type $maxheight 缩略图高
 * @param unknown_type $dstimg    生成的缩略图文件名[包含路径]
 * @param Boolean $is_cut 是否剪裁
 * @param Boolean $pmode 图片模式，是否保证约定尺寸输出图片（使用白色填充空白部分），0：不保证，1：保证；
 */
function imageResize($img,$maxwidth,$maxheight,$dstimg,$is_cut = false,$pmode=false)
{
	if(empty( $img ) || !file_exists( $img )) return false;
	//根据不同的格式读取源图片
	list($width, $height, $pic_info) = @getimagesize($img);
	switch($pic_info)
	{
		case 1: $image = imagecreatefromgif($img);break;//GIF
		case 2: $image = imagecreatefromjpeg($img);break;//JPG
		case 3: $image = imagecreatefrompng($img);imagesavealpha($image, true);break;//PNG
		case 6: $image = imagecreatefromwbmp($img);break;//BMP
		default:return false;
	}

	//计算成比例的宽高
	if($maxwidth && $width >= $maxwidth)
	{
		$widthratio = $maxwidth/$width;
		$RESIZEWIDTH=true;
	}
	else 
	{
		$RESIZEWIDTH=false;
	}
	if($maxheight && $height >= $maxheight)
	{
		$heightratio = $maxheight/$height;
		$RESIZEHEIGHT=true;
	}
	else 
	{
		$RESIZEHEIGHT=false;
	}

	$newwidth = $width;		// 新图片的宽度
	$newheight = $height;	// 新图片的高度
	$ratio = 1;				// 缩放比例
	$cut_side = 0;			// 剪裁的边，0：不需要；1：宽；2：高；
	$pos_x = 0;				// 偏移X
	$pos_y = 0;				// 偏移Y
	$target_width = $width;		// 原图的目标宽度
	$target_height = $height;	// 原图的目标高度

	if ($is_cut == false)
	{	// 不剪裁，保持原图的完整性
		if($RESIZEWIDTH && $RESIZEHEIGHT)
		{
			$ratio = min($widthratio, $heightratio);
		}
		elseif($RESIZEWIDTH)
		{
			$ratio = $widthratio;
		}
		elseif($RESIZEHEIGHT)
		{
			$ratio = $heightratio;
		}
		else
		{
			$ratio = 1;
		}
		$newwidth = $width * $ratio;
		$newheight = $height * $ratio;
	}
	else
	{	// 剪裁原图，保证目标图片尺寸的完整性
		if($RESIZEWIDTH && $RESIZEHEIGHT)
		{
			if ($widthratio > $heightratio)
			{
				$ratio = $widthratio;
				$newwidth = $width * $widthratio;
				$newheight = $maxheight;
				$cut_side = 2;
			}
			else
			{
				$ratio = $heightratio;
				$newwidth = $maxwidth;
				$newheight = $height * $heightratio;
				$cut_side = 1;
			}
		}
		elseif($RESIZEWIDTH)
		{
			$newheight = $maxheight;
			$cut_side = 2;
		}
		elseif($RESIZEHEIGHT)
		{
			$newwidth = $maxwidth;
			$cut_side = 1;
		}

		if ($cut_side == 1)
		{	// 剪切图片的宽
			$target_width = $newwidth / $ratio;
			$pos_x = ($width - $target_width) / 2;
		}
		else if ($cut_side == 2)
		{	// 剪切图片的高
			$target_height = $newheight / $ratio;
			$pos_y = ($height - $target_height) / 2;
		}
	}

	if(function_exists("imagecopyresampled"))
	{
		if ($pmode)
		{
			$newim = imagecreatetruecolor($maxwidth, $maxheight);//新建一个真彩色图像[黑色图像]
			$white = imagecolorallocate($newim,255,255,255);
			imagefilledrectangle($newim,0,0,$maxwidth,$maxheight,$white);
			imagecolortransparent($newim,$white);
			imagecopyresampled($newim, $image, ($maxwidth-$newwidth)/2, ($maxheight-$newheight)/2, $pos_x, $pos_y, $newwidth, $newheight, $target_width, $target_height);//重采样拷贝部分图像并调整大小
		}
		else
		{
			$newim = imagecreatetruecolor($newwidth, $newheight);//新建一个真彩色图像[黑色图像]
			$white = imagecolorallocate($newim,255,255,255);
			imagefilledrectangle($newim,0,0,$newwidth,$newheight,$white);
			imagecolortransparent($newim,$white);
			imagecopyresampled($newim, $image, 0, 0, $pos_x, $pos_y, $newwidth, $newheight, $target_width, $target_height);//重采样拷贝部分图像并调整大小
		}
	}
	else
	{
		if ($pmode)
		{
			$newim = imagecreate($maxwidth, $maxheight);
			$white = imagecolorallocate($newim,255,255,255);
			imagefilledrectangle($newim,0,0,$maxwidth,$maxheight,$white);
			imagecolortransparent($newim,$white);
			imagecopyresized($newim, $image, ($maxwidth-$newwidth)/2, ($maxheight-$newheight)/2, $pos_x, $pos_y, $newwidth, $newheight, $target_width, $target_height);
		}
		else
		{
			$newim = imagecreate($newwidth, $newheight);
			$white = imagecolorallocate($newim,255,255,255);
			imagefilledrectangle($newim,0,0,$newwidth,$newheight,$white);
			imagecolortransparent($newim,$white);
			imagecopyresized($newim, $image, 0, 0, $pos_x, $pos_y, $newwidth, $newheight, $target_width, $target_height);
		}
	}

	switch($pic_info)
	{
		case 1: imagegif($newim,$dstimg);break;		//GIF
		case 2: imagejpeg($newim,$dstimg,90);break;	//JPG
		case 3: imagepng($newim,$dstimg);break;		//PNG
		default:return false;
	}
	ImageDestroy($newim);
	return true;
}


function imageProduct($img,$maxwidth,$maxheight,$dstimg,$is_cut = false,$pmode=false)
{
	if(empty( $img ) || !file_exists( $img )) return false;
	//根据不同的格式读取源图片
	list($width, $height, $pic_info) = @getimagesize($img);
	switch($pic_info)
	{
		case 1: $image = imagecreatefromgif($img);break;//GIF
		case 2: $image = imagecreatefromjpeg($img);break;//JPG
		case 3: $image = imagecreatefrompng($img);imagesavealpha($image, true);break;//PNG
		case 6: $image = imagecreatefromwbmp($img);break;//BMP
		default:return false;
	}

	//计算成比例的宽高
	if($maxwidth && $width >= $maxwidth)
	{
		$widthratio = $maxwidth/$width;
		$RESIZEWIDTH=true;
	}
	else 
	{
		$RESIZEWIDTH=false;
	}
	if($maxheight && $height >= $maxheight)
	{
		$heightratio = $maxheight/$height;
		$RESIZEHEIGHT=true;
	}
	else 
	{
		$RESIZEHEIGHT=false;
	}

	$newwidth = $width;		// 新图片的宽度
	$newheight = $height;	// 新图片的高度
	$ratio = 1;				// 缩放比例
	$cut_side = 0;			// 剪裁的边，0：不需要；1：宽；2：高；
	$pos_x = 0;				// 偏移X
	$pos_y = 0;				// 偏移Y
	$target_width = $width;		// 原图的目标宽度
	$target_height = $height;	// 原图的目标高度

	if ($is_cut == false)
	{	// 不剪裁，保持原图的完整性
		if($RESIZEWIDTH && $RESIZEHEIGHT)
		{
			$ratio = min($widthratio, $heightratio);
		}
		elseif($RESIZEWIDTH)
		{
			$ratio = $widthratio;
		}
		elseif($RESIZEHEIGHT)
		{
			$ratio = $heightratio;
		}
		else
		{
			$ratio = 1;
		}
		$newwidth = $width * $ratio;
		$newheight = $height * $ratio;
	}
	else
	{	// 剪裁原图，保证目标图片尺寸的完整性
		if($RESIZEWIDTH && $RESIZEHEIGHT)
		{
			if ($widthratio > $heightratio)
			{
				$ratio = $widthratio;
				$newwidth = $width * $widthratio;
				$newheight = $maxheight;
				$cut_side = 2;
			}
			else
			{
				$ratio = $heightratio;
				$newwidth = $maxwidth;
				$newheight = $height * $heightratio;
				$cut_side = 1;
			}
		}
		elseif($RESIZEWIDTH)
		{
			$newheight = $maxheight;
			$cut_side = 2;
		}
		elseif($RESIZEHEIGHT)
		{
			$newwidth = $maxwidth;
			$cut_side = 1;
		}

		if ($cut_side == 1)
		{	// 剪切图片的宽
			$target_width = $newwidth / $ratio;
			$pos_x = ($width - $target_width) / 2;
		}
		else if ($cut_side == 2)
		{	// 剪切图片的高
			$target_height = $newheight / $ratio;
			$pos_y = ($height - $target_height) / 2;
		}
	}

	if(function_exists("imagecopyresampled"))
	{
		if ($pmode)
		{
			if($width < 500)
			{
				$newim = imagecreatetruecolor($width, $width*0.75);//新建一个真彩色图像[黑色图像]
				$white = imagecolorallocate($newim,255,255,255);
				imagefilledrectangle($newim,0,0,$width,$width*0.75,$white);
				imagecolortransparent($newim,$white);
				imagecopyresampled($newim, $image, 0, 0, 0, ($height-$width*0.75)/2, $width, $width*0.75, $width, $width*0.75);//重采样拷贝部分图像并调整大小
			}
			else if($height < 375)
			{
				$newim = imagecreatetruecolor($height*4/3, $height);//新建一个真彩色图像[黑色图像]
				$white = imagecolorallocate($newim,255,255,255);
				imagefilledrectangle($newim,0,0,$width*4/3,$height,$white);
				imagecolortransparent($newim,$white);
				imagecopyresampled($newim, $image, 0, 0, ($width-$height*4/3)/2, 0, $height*4/3, $height, $height*4/3, $height);//重采样拷贝部分图像并调整大小
			}
			else 
			{
				$newim = imagecreatetruecolor($maxwidth, $maxheight);//新建一个真彩色图像[黑色图像]
				$white = imagecolorallocate($newim,255,255,255);
				imagefilledrectangle($newim,0,0,$maxwidth,$maxheight,$white);
				imagecolortransparent($newim,$white);
				imagecopyresampled($newim, $image, ($maxwidth-$newwidth)/2, ($maxheight-$newheight)/2, $pos_x, $pos_y, $newwidth, $newheight, $target_width, $target_height);//重采样拷贝部分图像并调整大小
			}
		}
		else
		{
			$newim = imagecreatetruecolor($newwidth, $newheight);//新建一个真彩色图像[黑色图像]
			$white = imagecolorallocate($newim,255,255,255);
			imagefilledrectangle($newim,0,0,$newwidth,$newheight,$white);
			imagecolortransparent($newim,$white);
			imagecopyresampled($newim, $image, 0, 0, $pos_x, $pos_y, $newwidth, $newheight, $target_width, $target_height);//重采样拷贝部分图像并调整大小
		}
	}
	else
	{
		if ($pmode)
		{
			$newim = imagecreate($maxwidth, $maxheight);
			$white = imagecolorallocate($newim,255,255,255);
			imagefilledrectangle($newim,0,0,$maxwidth,$maxheight,$white);
			imagecolortransparent($newim,$white);
			imagecopyresized($newim, $image, ($maxwidth-$newwidth)/2, ($maxheight-$newheight)/2, $pos_x, $pos_y, $newwidth, $newheight, $target_width, $target_height);
		}
		else
		{
			$newim = imagecreate($newwidth, $newheight);
			$white = imagecolorallocate($newim,255,255,255);
			imagefilledrectangle($newim,0,0,$newwidth,$newheight,$white);
			imagecolortransparent($newim,$white);
			imagecopyresized($newim, $image, 0, 0, $pos_x, $pos_y, $newwidth, $newheight, $target_width, $target_height);
		}
	}

	switch($pic_info)
	{
		case 1: imagegif($newim,$dstimg);break;		//GIF
		case 2: imagejpeg($newim,$dstimg,90);break;	//JPG
		case 3: imagepng($newim,$dstimg);break;		//PNG
		default:return false;
	}
	ImageDestroy($newim);
	return true;
}


function user_image($image=null) {
	global $INI;
	$image = $image ? $image : 'img/user-no-avatar.gif';
	return "/static/{$image}";
}

function team_image($image=null, $index=false , $all=false , $width=0, $height=0) {
	global $INI;
	if (!$image) return null;
	if ($index) {
		$path = WWW_ROOT . '/static/' . $image;
		if( $all )
		{
			$image = preg_replace('#(\d+)\.(\w+)$#', "\\1_all.\\2", $image); 
			$dest = WWW_ROOT . '/static/' . $image;
			if (!file_exists($dest) && file_exists($path) ) {
				Image::Convert($path, $dest, 315, 190, Image::MODE_SCALE);
			}
		}
		else 
		{
			if( $width > 0 && $height > 0 ){
				$image = preg_replace('#(\d+)\.(\w+)$#', "\\1_order.\\2", $image); 
				$dest = WWW_ROOT . '/static/' . $image;
				if (!file_exists($dest) && file_exists($path) ) {
					Image::Convert($path, $dest, $width, $height, Image::MODE_SCALE);
				}
			}else{
				$image = preg_replace('#(\d+)\.(\w+)$#', "\\1_index.\\2", $image); 
				$dest = WWW_ROOT . '/static/' . $image;
				if (!file_exists($dest) && file_exists($path) ) {
					Image::Convert($path, $dest, 200, 120, Image::MODE_SCALE);
				}
			}
		}
	}
	return "{$INI['system']['imgprefix']}/static/{$image}";
}

function userreview($content) {
	$line = preg_split("/[\n\r]+/", $content, -1, PREG_SPLIT_NO_EMPTY);
	$r = '<ul>';
	foreach($line AS $one) {
		$c = explode('|', htmlspecialchars($one));
		$c[2] = $c[2] ? $c[2] : '/';
		$r .= "<li style='list-style-type:none;'>{$c[0]}<span>－－<a href=\"{$c[2]}\" target=\"_blank\">{$c[1]}</a>";
		$r .= ($c[3] ? "（{$c[3]}）":'') . "</span></li>\n";
	}
	return $r.'</ul>';
}

function invite_state($invite) {
	if ('Y' == $invite['pay']) return '已返利';
	if ('C' == $invite['pay']) return '审核未通过';
	if ('N' == $invite['pay'] && $invite['buy_time']) return '待返利';
	if (time()-$invite['create_time']>7*86400) return '已过期';
	return '未购买';
}

function team_state(&$team) {
	if ( $team['now_number'] >= $team['min_number'] ) {
		if ($team['max_number']>0) {
			if ( $team['now_number']>=$team['max_number'] ){
				if ($team['close_time']==0) {
					$team['close_time'] = $team['end_time'];
				}
				return $team['state'] = 'soldout';
			}
		}
		if ( $team['end_time'] <= time() ) {
			$team['close_time'] = $team['end_time'];
		}
		return $team['state'] = 'success';
	} else {
		if ( $team['end_time'] <= time() ) {
			$team['close_time'] = $team['end_time'];
			return $team['state'] = 'failure';
		}
	}
	return $team['state'] = 'none';
}

function current_team($city_id=0) {
	$today = strtotime(date('Y-m-d'));
	$cond = array(
			'team_status' => '1',//审核通过的
			'team_type' => 'normal',
			"begin_time <= {$today}",
			"end_time > {$today}",
			);
	/* 数据库匹配多个城市订单,前者按照多城市搜索,后者兼容旧字段city_id搜索 */
	$cond[] = "((city_ids like '%@{$city_id}@%' or city_ids like '%@0@%') or city_id in(0,{$city_id}))";
	$order = 'ORDER BY sort_order DESC, begin_time DESC, id DESC';
	/* normal team */
	$team = DB::LimitQuery('team', array(
				'condition' => $cond,
				'one' => true,
				'order' => $order,
				));
	if ($team) return $team;

	/* seconds team */
	$cond['team_type'] = 'seconds';
	unset($cond['begin_time']);	
	$order = 'ORDER BY sort_order DESC, begin_time ASC, id DESC';
	$team = DB::LimitQuery('team', array(
				'condition' => $cond,
				'one' => true,
				'order' => $order,
				));

	return $team;
}

function state_explain($team, $error='false') {
	$state = team_state($team);
	$state = strtolower($state);
	switch($state) {
		case 'none': return '正在进行中';
		case 'soldout': return '已售光';
		case 'failure': if($error) return '团购失败';
		case 'success': return '团购成功';
		default: return '已结束';
	}
}

function get_zones($zone=null) {
	$zones = array(
			'city' => '城市列表',
			'partner' => '商户分类',
			'group' => '项目分类',
			'express' => '快递公司',
			'grade' => '用户等级',
			//'public' => '讨论区分类',
			);
	if ( !$zone ) return $zones;
	if (!in_array($zone, array_keys($zones))) {
		$zone = 'city';
	}
	return array($zone, $zones[$zone]);
}

function down_xls($data, $keynames, $name='dataxls') {
	$xls[] = "<html><meta http-equiv=content-type content=\"text/html; charset=UTF-8\"><body><table border='1'>";
	$xls[] = "<tr><td>ID</td><td>" . implode("</td><td>", array_values($keynames)) . '</td></tr>';
	foreach($data As $o) {
		$line = array(++$index);
		foreach($keynames AS $k=>$v) {
			$line[] = $o[$k];
		}
		$xls[] = '<tr><td>'. implode("</td><td  style='vnd.ms-excel.numberformat:@'>", $line) . '</td></tr>';
	}
	$xls[] = '</table></body></html>';
	$xls = join("\r\n", $xls);
	header('Content-Disposition: attachment; filename="'.$name.'.xls"');
	die(mb_convert_encoding($xls,'UTF-8','UTF-8'));
}

function option_hotcategory($zone='city', $force=false, $all=false) {
	$cates = option_category($zone, $force, true);
	$r = array();
	foreach($cates AS $id=>$one) {
		if ('Y'==strtoupper($one['display'])) $r[$id] = $one;
	}
	return $all ? $r: Utility::OptionArray($r, 'id', 'name');
}

function option_category($zone='city', $force=false, $all=false) {
	$cache = $force ? 0 : 86400*30;
	$cates = DB::LimitQuery('category', array(
		'condition' => array( 'zone' => $zone, ),
		'order' => 'ORDER BY sort_order DESC, id DESC',
		'cache' => $cache,
	));
	$cates = Utility::AssColumn($cates, 'id');
	return $all ? $cates : Utility::OptionArray($cates, 'id', 'name');
}

function option_yes($n, $default=false) {
	global $INI;
	if (false==isset($INI['option'][$n])) return $default;
	$flag = trim(strval($INI['option'][$n]));
	return abs(intval($flag)) || strtoupper($flag) == 'Y';
}

function option_yesv($n, $default='N') {
	return option_yes($n, $default=='Y') ? 'Y' : 'N';
}

function magic_gpc($string) {
	if(SYS_MAGICGPC) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = magic_gpc($val);
			}
		} else {
			$string = stripslashes($string);
		}
	}
	return $string;
}

function team_discount($team, $save=false) {
	if ($team['market_price']<0 || $team['team_price']<0 ) {
		return '?';
	}
	return moneyit((10*$team['team_price']/$team['market_price']));
}

function team_origin($team, $quantity=0, $express_price = 0) {
	$origin = $quantity * $team['team_price'];
	if ($team['delivery'] == 'express'
			&& ($team['farefree']==0 || $quantity < $team['farefree'])
		) {
			$origin += $express_price;
		}
	return $origin;
}

function index_get_team($city_id) {
	global $INI;
	$multi = option_yes('indexmulti');
	$city_id = abs(intval($city_id));

	/* 是否首页多团,不是则返回当前城市 */
	if (!$multi) return current_team($city_id);
	
	$now = time();
	$size = abs(intval($INI['system']['sideteam']));
	/* 侧栏团购数小于1,则返回当前城市数据 */
	if ($size<=1) return current_team($city_id);

	$oc = array( 
			'team_status' => '1',//审核通过的
			'team_type' => 'normal',
			"begin_time < '{$now}'",
			"end_time > '{$now}'",
			);
	/* 数据库匹配多个城市订单,前者按照多城市搜索,后者兼容旧字段city_id搜索 */
	$oc[] = "(city_ids like '%@{$city_id}@%' or city_ids like '%@0@%') or (city_ids = '' and city_id in(0,{$city_id}))";
	$teams = DB::LimitQuery('team', array(
				'condition' => $oc,
				'order' => 'ORDER BY `sort_order` DESC, `id` DESC',
				'size' => $size,
				));
	if(count($teams) == 1) return array_pop($teams);
	return $teams;
}


function interface_post($url, $data)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSLVERSION, 3); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_POST, TRUE); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
    curl_setopt($ch, CURLOPT_URL, $url);
    $ret = curl_exec($ch);

    curl_close($ch);
    return $ret;
}


/*
*	功能：获取距离
*	参数：地理经度，地理纬度
*/
function getdistance($lon1 = '', $lat1 = '', $lon2 = '', $lat2 = '') {
	$lon1 = floatval(trim($lon1));
	$lat1 = floatval(trim($lat1));
	$lon2 = floatval(trim($lon2));
	$lat2 = floatval(trim($lat2));

	if(!$lon1 || !$lat1 || !$lon2 || !$lat2) {
		return '0';
	}
	if(!is_numeric($lon1) || !is_numeric($lat1) || !is_numeric($lon2) || !is_numeric($lat2)) {
		return '0';
	}

	$lon1 = $lon1 * pi() / 180;
	$lon2 = $lon2 * pi() / 180;
	$lon = $lon1 - $lon2;
	$lat = $lat1 * pi() / 180 - $lat2 * pi() / 180;

	$distance = 2 * asin(sqrt(pow(sin($lon / 2), 2) + cos($lon1) * cos($lon2) * pow(sin($lat / 2), 2))) * 6378.137 * 1000;

	$distance = transformdistance(round($distance));

	return $distance;
}

/*
*	功能：转化距离
*	参数：距离
*/
function transformdistance($dist = '0') {
	$dist = floatval(trim($dist));

	if(!is_numeric($dist)) {
		return '0m';
	}
//	if($dist < 1000) {
//		$dist = $dist . 'm';
//	} else {
//		$dist = ceil($dist / 1000) . 'Km';
//	}
	$dist = ceil($dist / 1000);

	return $dist;
}

function error_handler($errno, $errstr, $errfile, $errline) {
	switch ($errno) {
		case E_PARSE:
		case E_ERROR:
			echo "<b>Fatal ERROR</b> [$errno] $errstr<br />\n";
			echo "Fatal error on line $errline in file $errfile";
			echo "PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
			exit(1);
			break;
		default: break;
	}
	return true;
}
/* for obscureid */
function obscure_rep($u) {
	if(!option_yes('encodeid')) return $u;
	if(preg_match('#/manage/#', $_SERVER['REQUEST_URI'])) return $u;
	return preg_replace_callback('#(\?|&)id=(\d+)(\b)#i', obscure_cb, $u);
}
function obscure_did() {
	$gid = strval($_GET['id']);
	if ($gid && preg_match('/^ZT/', $gid)) {
		$id = base64_decode(substr($gid,2))>>2;
		if($id) $_GET['id'] = $id;
	}
}
function obscure_cb($m) {
	$eid = obscure_eid($m[2]);
	return "{$m[1]}id={$eid}{$m[3]}";
}
function obscure_eid($id) {
	if($id>100000000) return $id;
	return 'ZT'.base64_encode($id<<2);
}
obscure_did();
/* end */

/* for post trim */
function trimarray($o) {
	if (!is_array($o)) return trim($o);
	foreach($o AS $k=>$v) { $o[$k] = trimarray($v); }
	return $o;
}
$_POST = trimarray($_POST);
/* end */

/* verifycapctch */
function verify_captcha($reason='none', $rurl=null) {
	if (option_yes($reason, false)) {
		$v = strval($_REQUEST['vcaptcha']);
		if(!$v || !Utility::CaptchaCheck($v)) {
			Session::Set('error', '验证码不匹配，请重新输入');
			//redirect($rurl);
			return false;
		}
	}
	return true;
}

	//从微博接口获取登录用户的信息
	function get_userinfo()
	{
		require_once(WWW_ROOT.'/weibo/KafkaConfig.php');
		require_once(WWW_ROOT.'/weibo/saetv2.ex.class.php');
		
		$c = new SaeTClientV2( WB_AKEY , WB_SKEY , $_SESSION['token']['access_token'] );

		$uid_get = $c->get_uid();
		if(isset($uid_get['error_code']))
		{
			$url = "http://www.jxtuan.com";
			header("Location:$url");
		}
		else 
		{
			
			$uid = $uid_get['uid'];
			$user_message = $c->show_user_by_id($uid);//根据ID获取用户等基本信息
			
			//随机数
		    $rand = rand(99, 999);
			//该微博用户在用户表中的用户名
			$username = subtostring($user_message['screen_name'], 13).'_w'.$rand;
			$weibo_openid = $user_message['id'];
			
			//当用户已登录，此处是绑定，先判断第三方用户表中是否存在相应的第三方账号，如果存在，则绑定对应的第三方账号，否则直接将新浪微博的信息保存在jx_users_sns表中
			if($_SESSION['user_id'] > 0)
			{
				$condition = array( 'sns_id' => $user_message['id'], 'sns_type' => 'weibo');
				$aField = DB::LimitQuery('jx_users_sns', array(
					'condition' => $condition,
					'one' => true,
				));
				if(!empty($aField['sns_id']))
				{
					//绑定已经存在的第三方账号
					$table = new Table('jx_users_sns', $_POST);
					$table->pk_value = $aField['id'];
					$table->uid = $_SESSION['user_id'];
					$up_array = array('uid');
					$flag = $table->update( $up_array );
					if($flag)
					{
						$url = "http://www.jxtuan.com/account/bindingsns.php";
						header("Location:$url");
					}
					else 
					{
						$url = "http://www.jxtuan.com/account/bindingsns.php";
						header("Location:$url");
					}
				}
				else 
				{
					//保存第三方账号
					$u['uid'] = $_SESSION['user_id'];
					$u['sns_type'] = 'weibo';
					$u['sns_id'] = $weibo_openid;
					$u['sns_nickname'] = $username;
					$u['sns_url'] = 'http://weibo.com/'.$user_message['profile_url'];
					$u['sns_token'] = $_SESSION['token']['access_token'];
					$u['sns_headerurl'] = $user_message['profile_image_url'];
					$u['sns_description'] = $user_message['description'];
					$u['createtime'] = time();
					$u['id'] = DB::Insert('jx_users_sns', $u);
					if($u['id'])
					{
						$url = "http://www.jxtuan.com/account/bindingsns.php";
						header("Location:$url");
					}
					else 
					{
						$url = "http://www.jxtuan.com/account/bindingsns.php";
						header("Location:$url");
					}
				}
			}
			else 
			{
			
				$condition = array( 'sns_id' => $user_message['id'], 'sns_type' => 'weibo');
				$aField = DB::LimitQuery('jx_users_sns', array(
					'condition' => $condition,
					'one' => true,
				));
				//判断jx_users_sns表中第三方用户是否存在，如果存在，则直接登录，不在向表中插入新数据
				if(!empty($aField['sns_id']))
			    {
			    	//判断jx_users表中是否存在对应的用户
		    		if($aField['uid'] != '-1')
		    		{
	    				$c = array('id'=>$aField['uid']);
						$user = DB::LimitQuery('jx_users', array(
							'condition' => $c,
							'one' => true,
						));
						if(!empty($user['mobile']))
						{
							Session::Set('user_id', $user['id']);
				    		Session::Set('type', $user['type']);
							Session::Set('mobile', $user['mobile']);
							$url = 'http://www.jxtuan.com/account/productlist.php';
						}
						else 
						{
							$url = 'http://www.jxtuan.com/account/register.php?id='.$aField['id'];
						}
		    		}
		    		else 
		    		{
		    				Session::Set('user_id', $aField['uid']);
				    		Session::Set('type', 2);
							Session::Set('sns_type', $aField['sns_type']);
							Session::Set('mobile', $aField['sns_nickname']);
							$url = 'http://www.jxtuan.com/account/register.php?id='.$aField['id'];
		    		}		
			    }
			    else 
			    {
					//将第三方网站用户的信息保存到jx_users_sns表中
					$u['uid'] = '-1';
					$u['sns_type'] = 'weibo';
					$u['sns_id'] = $weibo_openid;
					$u['sns_nickname'] = $username;
					$u['sns_url'] = 'http://weibo.com/'.$user_message['profile_url'];
					$u['sns_token'] = $_SESSION['token']['access_token'];
					$u['sns_headerurl'] = $user_message['profile_image_url'];
					$u['sns_description'] = $user_message['description'];
					$u['createtime'] = time();
					$u['id'] = DB::Insert('jx_users_sns', $u);
//					将微博登录用户的信息保存在COOKIE中，保存12个小时
//					保存个人头像
//					setcookie('headerurl',$user_message['profile_image_url'], time()+3600*12);
//					保存昵称
//					setcookie('nickname',$username, time()+3600*12);
//					保存个人网址
//					setcookie('website','http://weibo.com/'.$user_message['profile_url'], time()+3600*12);
//					保存个人描述
//					setcookie('description',$user_message['description'], time()+3600*12);
//					保存微博ID
//					setcookie('weibo_id',$weibo_openid, time()+3600*12);
//					保存微博token
//					setcookie('access_token',$_SESSION['token']['access_token'], time()+3600*12);
//					保存微博名称
//					setcookie('sina',$user_message['screen_name'], time()+3600*12);
//					保存登录的SNS名称
//					setcookie('sns',$sns, time()+3600*12);
					if(!empty($u['id']))
					{
						Session::Set('user_id', $u['uid']);
			    		Session::Set('type', 2);
						Session::Set('sns_type', $u['sns_type']);
						Session::Set('mobile', $u['sns_nickname']);
						$url = 'http://www.jxtuan.com/account/register.php?id='.$u['id'];
					}
					else 
					{
						$url = 'http://www.jxtuan.com';
					}
			    }
				header("Location:$url");
			}
		}
	}
	
	function get_openid()
	{
		require_once(WWW_ROOT.'/comm/KafkaConfig.php');
	    $graph_url = "https://graph.qq.com/oauth2.0/me?access_token=" 
	        . $_SESSION['access_token'];
	
	    $str  = file_get_contents($graph_url);
	    if (strpos($str, "callback") !== false)
	    {
	        $lpos = strpos($str, "(");
	        $rpos = strrpos($str, ")");
	        $str  = substr($str, $lpos + 1, $rpos - $lpos -1);
	    }
	
	    $user = json_decode($str);
	    if (isset($user->error))
	    {
	        echo "<h3>error:</h3>" . $user->error;
	        echo "<h3>msg  :</h3>" . $user->error_description;
	        exit;
	    }
	
	    $_SESSION["rt_food_openid"] = $user->openid;
	    get_qquserinfo();
	}
	
	
	function get_qquserinfo()
	{
		require_once(WWW_ROOT.'/comm/KafkaConfig.php');
		//访问用户资料的接口
	    $get_user_info = "https://graph.qq.com/user/get_user_info?"
	        . "access_token=" . $_SESSION["access_token"]
	        . "&oauth_consumer_key=" . $_SESSION["appid"]
	        . "&openid=" . $_SESSION["rt_food_openid"]
	        . "&format=json";
	    $info = file_get_contents($get_user_info);
	    $arr = json_decode($info, true);
	    
	    //访问腾讯微博的接口
	    $get_info = "https://graph.qq.com/user/get_info?"
	        . "access_token=" . $_SESSION["access_token"]
	        . "&oauth_consumer_key=" . $_SESSION["appid"]
	        . "&openid=" . $_SESSION["rt_food_openid"]
	        . "&format=json";
	    $qqweiboinfo = file_get_contents($get_info);
	    $arrqqweibo = json_decode($qqweiboinfo, true);
	    
		//随机数
	    $rand = rand(99, 999);
		//该微博用户在用户表中的用户名
		$username = subtostring($arr['nickname'], 13).'_q'.$rand;
		$qq_openid = $_SESSION["rt_food_openid"];
    	//当用户已登录，此处是绑定，用户信息此前已经注册，此处是修改用户信息
		if($_SESSION['user_id'] > 0)
		{
			$u['uid'] = $_SESSION['user_id'];
			$u['sns_type'] = 'qq';
			$u['sns_id'] = $qq_openid;
			$u['sns_nickname'] = $username;
			$u['sns_url'] = 'http://t.qq.com/'.$arrqqweibo['data']['name'];
			$u['sns_token'] = $_SESSION["access_token"];
			$u['sns_headerurl'] = $arr['figureurl_1'];
			$u['createtime'] = time();
			$u['id'] = DB::Insert('jx_users_sns', $u);
			if($u['id'])
			{
				$url = "http://www.jxtuan.com/account/bindingsns.php";
				header("Location:$url");
			}
			else 
			{
				$url = "http://www.jxtuan.com/account/bindingsns.php";
				header("Location:$url");
			}
		}
		else 
		{
	    
		    $condition = array( 'sns_id' => $_SESSION["rt_food_openid"], 'sns_type' => 'qq');
			$aField = DB::LimitQuery('jx_users_sns', array(
				'condition' => $condition,
				'one' => true,
			));
			//判断jx_users_sns表中第三方用户是否存在，如果存在，则直接登录
			if(!empty($aField['sns_id']))
		    {
		    		//判断jx_users表中是否存在对应的用户
		    		if($aField['uid'] != '-1')
		    		{
	    				$c = array('id'=>$aField['uid']);
						$user = DB::LimitQuery('jx_users', array(
							'condition' => $c,
							'one' => true,
						));
						if(!empty($user))
						{
							Session::Set('user_id', $user['id']);
				    		Session::Set('type', $user['type']);
							Session::Set('mobile', $user['mobile']);
							$url = 'http://www.jxtuan.com/account/productlist.php';
						}
						else 
						{
							$url = 'http://www.jxtuan.com/account/register.php?id='.$aField['id'];
						}
		    		}
		    		else 
		    		{
		    				Session::Set('user_id', $aField['uid']);
				    		Session::Set('type', 2);
							Session::Set('sns_type', $aField['sns_type']);
							Session::Set('mobile', $aField['sns_nickname']);
							$url = 'http://www.jxtuan.com/account/register.php?id='.$aField['id'];
		    		}		
		    }
		    else 
		    {	
				//将第三方网站用户的信息保存到jx_users_sns表中
				$u['uid'] = '-1';
				$u['sns_type'] = 'qq';
				$u['sns_id'] = $qq_openid;
				$u['sns_nickname'] = $username;
				$u['sns_url'] = 'http://t.qq.com/'.$arrqqweibo['data']['name'];
				$u['sns_token'] = $_SESSION["access_token"];
				$u['sns_headerurl'] = $arr['figureurl_1'];
				$u['createtime'] = time();
				$u['id'] = DB::Insert('jx_users_sns', $u);
				
//				将QQ登录用户的信息保存在COOKIE中，保存12个小时
//				保存个人头像
//				setcookie('headerurl',$arr['figureurl_1'], time()+3600*12);
//				保存昵称
//				setcookie('nickname',$username, time()+3600*12);
//				保存个人网址
//				setcookie('website','http://t.qq.com/'.$arrqqweibo['data']['name'], time()+3600*12);
//				保存QQID
//				setcookie('qq_openid',$qq_openid, time()+3600*12);
//				保存QQ的access_token
//				setcookie('qq_access_token',$_SESSION["access_token"], time()+3600*12);
//				保存QQ名称
//				setcookie('qzone',$arr['nickname'], time()+3600*12);
//				保存登录的SNS名称
//				setcookie('sns',$sns, time()+3600*12);
				if(!empty($u['id']))
				{
					Session::Set('user_id', $u['uid']);
		    		Session::Set('type', 2);
					Session::Set('sns_type', $u['sns_type']);
					Session::Set('mobile', $u['sns_nickname']);
					$url = 'http://www.jxtuan.com/account/register.php?id='.$u['id'];
				}
				else 
				{
					$url = 'http://www.jxtuan.com';
				}
		    }
			header("Location:$url");
		}
	}

/**
 * 取IP
 */
function getLoginIP(){
	if(getenv('HTTP_CLIENT_IP')){
		$ip = getenv('HTTP_CLIENT_IP');
	}
	elseif(getenv('HTTP_X_FORWARDED_FOR')){
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	}
	elseif(getenv('HTTP_X_FORWARDED')){
		$ip = getenv('HTTP_X_FORWARDED');
	}
	elseif(getenv('HTTP_FORWARDED_FOR')){
		$ip = getenv('HTTP_FORWARDED_FOR');
	}
	elseif(getenv('HTTP_FORWARDED')){
		$ip = getenv('HTTP_FORWRDED');
	}
	else{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}
function setting_rights( $rights )
{
	$val = array(
		'sets'=>$rights,
		'rights'=>array()
	) ;
	$php = DIR_CONFIGURE . '/rights2role.php';
	if ( file_exists($php) ) {
		require_once($php);
	}
	if( in_array( 'kefu' , $rights ) )
	{
		$val['rights'] = array_merge( $val['rights'] , $kefu );
	}
	if( in_array( 'bianji' , $rights ) )
	{
		if( empty( $val['rights'] ) )
		{
			$val['rights'] = array_merge( $val['rights'] , $bianji );
		}
		else 
		{
			$val['rights'] = array_merge( $val['rights'] , $bianji );
		}
	}
	if( in_array( 'yunying' , $rights ) )
	{
		if( empty( $val['rights'] ) )
		{
			$val['rights'] = $yunying ;
		}
		else 
		{
			$val['rights'] = array_merge_recursive( $val['rights'] , $yunying );
		}
	}
	if( in_array( 'system' , $rights ) )
	{
		if( empty( $val['rights'] ) )
		{
			$val['rights'] = $system ;
		}
		else 
		{
			$val['rights'] = array_merge( $val['rights'] , $system );
		}
	}
	return $val ;
}
set_error_handler('error_handler');
