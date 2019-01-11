<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/26
 * Time: 9:41
 */
require_once(dirname(__FILE__) . '/../AndroidNotification.php');

class AndroidListcast extends AndroidNotification {
    function  __construct() {
        parent::__construct();
        $this->data["type"] = "listcast";
        $this->data["filter"]  = NULL;
    }
}