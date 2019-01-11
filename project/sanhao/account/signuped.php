<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$mobile = strval($_GET['mobile']);

$pagetitle = '注册成功';
include template('account_signuped');
