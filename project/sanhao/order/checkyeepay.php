<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$id = intval($_GET['order_id']);
$order = Table::Fetch('jx_orders', $id);

include template('order_checkyeepay');
