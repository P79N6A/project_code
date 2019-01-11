<?php

namespace app\models\rbcredit;
class ClientNotify extends \app\models\remit\ClientNotify {
    public static function tableName() {
        return 'rb_credit_notify';
    }
}
