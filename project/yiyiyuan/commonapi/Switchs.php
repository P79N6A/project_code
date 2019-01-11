<?php

namespace app\commonapi;

class Switchs {
    
   
    public static function getSwitchVal() {
        return [
            'normalNum' => 3000,
            'warnNum' => 2000,
            'normalAmount' => 3000000,
            'warnAmount' => 2000000,
            'warnStartTime' => date('Y-m-d').' 09:30',
            'warnEndTime' => date('Y-m-d').' 22:00',
        ];
    }

}
