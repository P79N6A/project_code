<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if ( $_POST ) {
	$captcha = strtoupper(strval(trim($_POST['vcaptcha'])));
	if($captcha == $_SESSION['captcha'])
	{
		echo 'success';
	}
	else 
	{
		echo 'fail';
	}
	exit;
}
