<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

need_login();
need_checksns();

$pagetitle = '付款成功';
include template('order_success');