<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$mobile = strval($_GET['mobile']);

$pagetitle = '找回密码';
include template('account_repass3');
