<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

if(isset($_SESSION['user_id'])) {
	unset($_SESSION['user_id']);
	unset($_SESSION['type']);
	unset($_SESSION['sns_type']);
	unset($_SESSION['mobile']);
	ZLogin::NoRemember();
	ZUser::SynLogout();
}
if(isset($_SESSION['ali_token'])) {
	unset($_SESSION['ali_token']);
}
if(isset($_SESSION['ali_add'])) {
	unset($_SESSION['ali_add']);
}

redirect( WEB_ROOT . '/');
