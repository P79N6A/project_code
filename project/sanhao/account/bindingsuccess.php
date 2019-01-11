<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$mobile = strval($_SESSION['mobile']);

$binding = DB::LimitQuery('jx_bindings', array(
	'condition' => array('mobile' => $mobile),
	'one'=>true
));
if(empty($binding))
{
	redirect(WEB_ROOT .'/error.php');
}

$pagetitle = '绑定成功';
include template('account_bingingsuccess');