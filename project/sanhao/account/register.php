<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$id = strval($_GET['id']);
$condition = array( 'id' => $id);
$user = DB::LimitQuery('jx_users_sns', array(
	'condition' => $condition,
	'one' => true,
));
if(empty($user))
{
	redirect(WEB_ROOT .'/error.php');
}

$pagetitle = '注册';
include template('account_register');