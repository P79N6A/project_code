<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/6/26
 * Time: 9:56
 */
require_once(dirname(__FILE__) . '/../IOSNotification.php');

class IOSListcast extends IOSNotification {
    function  __construct() {
        parent::__construct();
        $this->data["type"] = "listcast";
        $this->data["filter"]  = NULL;
    }
}