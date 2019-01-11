<?php 
require_once(dirname(dirname(__FILE__)) . '/app.php');

$id = $_GET['id'];
$sql = "update `jx_products` set pageview=pageview+1 where id=$id";
DB::Query($sql);

exit;