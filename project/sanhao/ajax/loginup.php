<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$html = render('ajax_dialog_login');
json($html, 'dialog');

?>