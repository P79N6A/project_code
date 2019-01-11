<?php

/**
 * 
 *   linux : sudo -u www /data/wwwroot/yiyiyuan/yii remit/remit runByChannel
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii remit/remit runByChannel 1 #1新浪; 2:
 */

namespace app\commands\remit;


use app\commands\BaseController;
use app\models\remit\RemitDo;

class NewremitController extends BaseController {

    /**
     * 出款运行
     * @param int $channel
     * @return str
     */
    public function runByFundChannel($fund, $channel) {
        $remitDo = new RemitDo();
        $initRet = $remitDo->run($fund, $channel);
//        $initRet = (new RemitHandler)->runByChannel($channel);
        print_r($initRet);
    }


    /**
     * 执行一条出款纪录
     * @param int $id User_remit_list 的id
     * @return bool
     */
    public function runById($id) {
        $remitDo = new RemitDo();
        $initRet = $remitDo->runById($id);
        print_r($initRet);
    }

}
