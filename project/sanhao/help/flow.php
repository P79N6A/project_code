<?php
require_once(dirname(dirname(__FILE__)) . '/app.php');

$nav = 'flow';
$pagetitle = '玩转' . $INI['system']['abbreviation'];
include template('help_flow');
