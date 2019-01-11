<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

sleep(3);

redirect(WEB_ROOT . "/order/ordersuccess.php");
?>