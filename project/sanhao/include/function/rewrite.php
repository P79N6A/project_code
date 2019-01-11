<?php
function rewrite_hook($c){
	//后台管理页面不做伪静态转换
	if ( ! preg_match('#/manage/#i', $_SERVER['REQUEST_URI']) ){
//		$c = rewrite_dir( $c ) ;
//		$c = rewrite_dir_id( $c ) ;
//		$c = rewrite_dir_r( $c ) ;
//		$c = rewrite_root( $c ) ;
		
		
//		$c = rewrite_city($c);
//		$c = rewrite_team($c);
//		$c = rewrite_order($c);
//		$c = rewrite_coupon($c);
//		$c = rewrite_team_dir($c);
//		$c = rewrite_account($c);
//		$c = rewrite_about($c);
//		$c = rewrite_help($c);
//		$c = rewrite_partner($c);
	}
	return $c;
}
function rewrite_dir( $c ){
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/(\w+)/(\w+)\.php\"#i";
	$r = "{$pre}/$1/$2.html";
	return preg_replace($p, $r, $c); 
}
function rewrite_root( $c ){
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/(\w+)\.php\?id=(\d+)\"#i";
	$r = "{$pre}/$1/$2.html";
	return preg_replace($p, $r, $c); 
}
function rewrite_dir_id( $c ){
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/(\w+)/(\w+)\.php\?id=(\d+)\"#i";
	$r = "{$pre}/$1/$2/$3.html";
	return preg_replace($p, $r, $c); 
}
function rewrite_dir_r( $c ){
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/(\w+)/(\w+)\.php\?r=(\w+)\"#i";
	$r = "{$pre}/$1/$2/$3.html";
	return preg_replace($p, $r, $c); 
}
function rewrite_city($c) {
	if (!option_yes('rewritecity')) return $c;
	if (preg_match('#/manage/#i', $_SERVER['REQUEST_URI'])) return $c;
	global $city, $INI;
	$pre = strval($INI['webroot']);
	$c = preg_replace('#city\.php\?ename=(\w+)#i', "$1", $c);
	if ($city['ename']) {
		//index
		$p = "#\"{$pre}/index.php\"#";
		$r = "{$pre}/{$city['ename']}";
		$c = preg_replace($p, $r, $c);
		//deals
		$p = "#{$pre}/team/index.php#";
		$r = "{$pre}/{$city['ename']}/deals";
		$c = preg_replace($p, $r, $c);
		//seconds
		$p = "#{$pre}/team/seconds.php#";
		$r = "{$pre}/{$city['ename']}/seconds";
		$c = preg_replace($p, $r, $c);
		//goods
		$p = "#{$pre}/team/goods.php#";
		$r = "{$pre}/{$city['ename']}/goods";
		$c = preg_replace($p, $r, $c);
		//goods
		$p = "#{$pre}/team/goods.php#";
		$r = "{$pre}/{$city['ename']}/goods";
		$c = preg_replace($p, $r, $c);
		//partners
		$p = "#{$pre}/partner/index.php#";
		$r = "{$pre}/{$city['ename']}/partners";
		$c = preg_replace($p, $r, $c);
	}
	return $c; 
}
//all team page
function rewrite_team($c) {
	//if (!option_yes('rewriteteam')) return $c;
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/team\.php\?id=(\d+)\"#i";
	$r = "{$pre}/team/$1.html";
	return preg_replace($p, $r, $c); 
}
//team detail page
function rewrite_team_dir($c) {
	//if (!option_yes('rewriteteam')) return $c;
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/team/(\w+)\.php\?id=(\d+)\"#i";
	$r = "{$pre}/team/$1/$2.html";
	return preg_replace($p, $r, $c); 
}
//order page
function rewrite_order($c) {
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/order/(\w+)\.php\"#i";
	$r = "{$pre}/order/$1.html";
	$c= preg_replace($p, $r, $c); 
	
	$p = "#\"{$pre}/order/(\w+)\.php\?id=(\d+)\"#i";
	$r = "{$pre}/order/$1/$2.html";
	$c= preg_replace($p, $r, $c); 
	
	return $c ;
}
//credit page
function rewrite_credit($c) {
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/credit/(\w+)\.php\"#i";
	$r = "{$pre}/credit/$1.html";
	$c= preg_replace($p, $r, $c); 
		
	return $c ;
}
//conpon page
function rewrite_coupon($c) {
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/coupon/(\w+)\.php\"#i";
	$r = "{$pre}/coupon/$1.html";
	$c= preg_replace($p, $r, $c); 
		
	return $c ;
}
function rewrite_account( $c ){
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/account/(\w+)\.php\"#i";
	$r = "{$pre}/account/$1.html";
	return preg_replace($p, $r, $c); 
}
function rewrite_about( $c ){
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/about/(\w+)\.php\"#i";
	$r = "{$pre}/about/$1.html";
	return preg_replace($p, $r, $c); 
}
function rewrite_help( $c ){
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/help/(\w+)\.php\"#i";
	$r = "{$pre}/help/$1.html";
	$c = preg_replace($p, $r, $c); 
	$p = "#\"{$pre}/help/(\w+)\.php\?r=(\w+)\"#i";
	$r = "{$pre}/help/$1/$2.html";
	$c = preg_replace($p, $r, $c); 
	return $c;
}
function rewrite_partner($c) {
	if (!option_yes('rewritepartner')) return $c;
	global $INI; $pre = strval($INI['webroot']);
	$p = "#\"{$pre}/partner\.php\?id=(\d+)\"#i";
	$r = "{$pre}/partner/$1.html";
	return preg_replace($p, $r, $c); 
}
