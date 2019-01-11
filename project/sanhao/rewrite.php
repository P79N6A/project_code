<?php
require_once(dirname(__FILE__) . '/app.php');
$uri = strval($_SERVER['REQUEST_URI']);
$pre = strval($_INI['webroot']);
if($pre&&0===strpos($uri, $pre)) $uri = substr($uri, 0+strlen($pre));
$u = parse_url($uri); $uri = $u['path'];

if( preg_match('#/(team)/(\d+).html#i', $uri, $m) ){
	$_GET['id'] = abs(intval($m[2]));
	$path = strtolower( strval( $m[1] ) ) ;
	die(require_once(dirname(__FILE__) . "/{$path}.php"));
}
if(preg_match('#/(coupon)/(\w+)/(\d+).html#i', $uri, $m)) {
	$_GET['id'] = strtolower(strval($m[3]));
	$path = strtolower( strval( $m[1] ) ) ;
	$type = strtolower(strval($m[2]));
	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
}
if(preg_match('#/(\w+)/(\w+)/(\d+).html#i', $uri, $m)) {
	$_GET['id'] = abs(intval($m[3]));
	$path = strtolower( strval( $m[1] ) ) ;
	$type = strtolower(strval($m[2]));
	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
}
if(preg_match('#/(\w+)/(whitemail)/(\w+).html#i', $uri, $m)) {
	$_GET['r'] = strtolower(strval($m[3]));
	$path = strtolower( strval( $m[1] ) ) ;
	$type = strtolower(strval($m[2]));
	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
}
if( preg_match('#/(\w+)/(\w+).html#i', $uri, $m) ){
	$path = strtolower( strval( $m[1] ) ) ;
	$type = strtolower(strval($m[2]));
	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
}

/* city */
//if(preg_match("#^/(\w+)$#i", $uri, $m)) {
//	$_GET['ename'] = $m[1]; 
//	die(require_once(dirname(__FILE__) . '/city.php'));
//}
//
///* team */
//if(preg_match('#/(team|partner)/(\d+).html#i', $uri, $m)) {
//	$_GET['id'] = abs(intval($m[2]));
//	$type = strtolower(strval($m[1]));
//	die(require_once(dirname(__FILE__) . "/{$type}.php"));
//}
//if(preg_match('#/(coupon)/(\w+).html#i', $uri, $m)) {
//	$path = strtolower( strval( $m[1] ) ) ;
//	$type = strtolower(strval($m[2]));
//	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
//}
//if(preg_match('#/(order)/(\w+).html#i', $uri, $m)) {
//	$path = strtolower( strval( $m[1] ) ) ;
//	$type = strtolower(strval($m[2]));
//	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
//}
//if(preg_match('#/(team)/(\w+)/(\d+).html#i', $uri, $m)) {
//	$_GET['id'] = abs(intval($m[3]));
//	$path = strtolower( strval( $m[1] ) ) ;
//	$type = strtolower(strval($m[2]));
//	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
//}
//if(preg_match('#/(order)/(\w+)/(\d+).html#i', $uri, $m)) {
//	$_GET['id'] = abs(intval($m[3]));
//	$path = strtolower( strval( $m[1] ) ) ;
//	$type = strtolower(strval($m[2]));
//	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
//}
///* list */
//if(preg_match('#^/(\w+)/(deals|seconds|goods|partners)$#i', $uri, $m)) {
//	$ename = strval($m[1]); $city = ename_city($ename);
//	$type = strtolower(strval($m[2]));
//	switch($type) {
//		case 'partners':
//			die(require_once(dirname(__FILE__) . "/partner/index.php"));
//		case 'deals':
//			die(require_once(dirname(__FILE__) . "/team/index.php"));
//		default:
//			die(require_once(dirname(__FILE__) . "/team/{$type}.php"));
//	}
//}
////credit
//if( preg_match('#/(credit)/(\w+).html#', $uri, $m)) {
//	$path = strtolower( strval( $m[1]) ) ;
//	$type = strtolower(strval($m[2]));
//	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
//}
////account
//if( preg_match('#/(account)/(\w+).html#', $uri, $m)) {
//	$path = strtolower( strval( $m[1]) ) ;
//	$type = strtolower(strval($m[2]));
//	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
//}
////about and help
//if( preg_match('#/(about|help)/((\w+)/)?(\w+)(.html)?#', $uri, $m)) {
//	if( "" == $m[2] && "" == $m[3] ){
//		$path = strtolower( strval( $m[1]) ) ;
//		$type = strtolower(strval($m[4]));
//	}else{
//		$_GET['r'] = $m[4] ;
//		$path = strtolower( strval( $m[1]) ) ;
//		$type = strtolower(strval($m[3]));
//	}
//	die(require_once(dirname(__FILE__) . "/{$path}/{$type}.php"));
//}