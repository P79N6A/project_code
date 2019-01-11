<?php
namespace app\modules\api\controllers\controllers310;

use app\modules\api\common\ApiController;
use Yii;

class SystemController extends ApiController {
    public $enableCsrfValidation = false;

    /**
     * 系统参数，app启动时候只请求一次，返回值前缀system_
     * @author 王新龙
     * @date 2018/8/23 19:18
     */
    public function actionIndex() {
        $array['system_current_url'] = Yii::$app->request->hostInfo;
        exit($this->returnBack('0000', $array));
    }
}
