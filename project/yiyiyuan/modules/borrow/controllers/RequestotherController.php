<?php
/**
 * 第三方页面，小花猪
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/19
 * Time: 19:53
 * http://xianhuahua.com/borrow/requestother
 * http://yyytest2.xianhuahua.com/borrow/requestother
 */

namespace app\modules\borrow\controllers;

use app\common\Logger;
use app\models\news\RequestLog;
use yii\filters\AccessControl;

class RequestotherController extends BorrowController
{

    public function actionIndex()
    {
        //获取用户user_id
        $user_id = $this->getUser()->user_id;
        //查找是否存在记录
        $oRequestLog = new RequestLog();
        $get_data = $oRequestLog->getData($user_id);

        //安全串
        $salf = time();

        //生成key
        $key = md5(md5($user_id.$salf));

        //来源
        $source = 1;  //小花猪

        $save_data = [
            'user_id'   => $user_id,
            'salf'      => $salf,
            'key'       => $key,
            'source'    => $source,
        ];
        //存在就修改不存在保存
        if ($get_data){
            $result = $get_data->updateData($save_data);
        }else {
            $result = $oRequestLog->saveData($save_data);
        }
        $location_url  = SYSTEM_ENV == 'prod' ? "https://xhh.happycheer.com/?token=" : "http://xhh.test.happycheer.com/?token=";
        if ($result){
            echo "<script>location.href='{$location_url}{$key}'</script>";
        }
        Logger::dayLog("gettinginfo", "save_data:", json_encode($save_data)."\n");
        return false;
    }
}