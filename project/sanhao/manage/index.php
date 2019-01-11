<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');


need_manager();

global $INI , $login_user_id;
// var_dump('<pre	>',$INI);die;

// $authRight = $INI['authorization'][$login_user_id] ;//获取当前用户权限
// 	$a = array() ;
//  	if( ! $authRight['rights'] && $login_user_id != 1 && $login_user_id != "" )
// 	{
// 		Session::Set('error', '没有给当前管理员设置权限！' );
// 		redirect( WEB_ROOT . '/manage/login.php' );
// 	}
// 	$model = '' ;
// 	$current = '' ;
// 	if( is_array( $authRight['rights'] ) )
// 	{
// 		foreach ( $authRight['rights'] as $key => $val )
// 		{
// 			$model = $key ;
// 			if( in_array( "index" , $val ) )
// 			{
// 				$current = "index" ;
// 			}
// 			else 
// 			{
// 				$current = $val[0] ;	
// 			}
			
// 			break;
// 		}
// 	}
	
// 	$path = $INI['rights'][$model][$current] ;
	
// 	if ( empty( $path )) {
// 		$path = 'misc/index.php';
// 	}else{
// 		$path = str_replace( '/manage/' , "" , $path ) ;
// 	}

// require_once( $path );
include template('manage_index');