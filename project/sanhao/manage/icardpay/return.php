<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/app.php');

sleep(3);

redirect(WEB_ROOT . "/manage/cardslist.php?action=purchase");
?>