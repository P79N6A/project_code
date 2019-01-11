<?php 
$ainfo = DB::GetTableRow('jx_rights', array( "uid=".$login_user['id']));
$models = unserialize($ainfo['rights']);
$condition = array('pid = 0');
$aHome = DB::LimitQuery('jx_models', array(
		'condition' => $condition,
		'order' => 'ORDER BY sort DESC',
));
$con = array('pid > 0');
$sMenu = DB::LimitQuery('jx_models', array(
		'condition' => $con,
		'order' => 'ORDER BY sort DESC',
));
if( $models ){
	//超级管理员有所有权限,其他管理员根据权限过滤
	if( $_SESSION['admin_id'] != 1){
		foreach ( $aHome as $key => $val ){
			if(!in_array($val['id'],$models)){
				unset($aHome[$key]);
			}
		}
		foreach($sMenu as $skey =>$sval){
			if(!in_array($sval['id'],$models) || ($sval['name'] == '模块管理')){
				unset($sMenu[$skey]);
			}
		}
	}
}
?>