<?php
namespace app\modules\api\controllers\controllers310;

use app\models\news\WarnMessageList;
use app\modules\api\common\ApiController;
use Yii;

class PushreadController extends ApiController {
    public $enableCsrfValidation = false;

    public function actionIndex() {
        $version = Yii::$app->request->post('version');
        $warn_message_id = Yii::$app->request->post('warn_message_id');
        if (empty($version) || empty($warn_message_id)) {
            exit($this->returnBack('99994'));
        }
        $o_warn_wessage_list = (new WarnMessageList())->getById($warn_message_id);
        if (!empty($o_warn_wessage_list)) {
            $o_warn_wessage_list->updateReadSuccess();
        }
        exit($this->returnBack('0000'));
    }
}
