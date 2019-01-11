<?php
namespace app\modules\api\controllers\controllers312;

use Yii;
use app\models\news\User;
use app\models\news\Selection;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\modules\api\common\ApiController;

class SelectionController extends ApiController
{
    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type');
        $source = Yii::$app->request->post('_source');

        if (empty($version) || empty($user_id) || empty($type) || empty($source)) {
            exit($this->returnBack('99994'));
        }

        $selectionObj = (new Selection())->getByUserIdAndTpey($user_id, $type);
        if (!empty($selectionObj)) {
            if ($selectionObj->process_code == '10002') {
                exit($this->returnBack('10221'));
            }
            $isVal = $selectionObj->getValidity();
            if (!empty($isVal)) {
                exit($this->returnBack('10219'));
            }
        }

        $url = Yii::$app->request->hostInfo . '/new/selection/middle';
        $res = (new Http())->selection_choice($user_id, $type, $url);
        if ( ($res['res_code'] != '105002' &&  $res['res_code'] != '0') || empty($res['res_url'])) {
            Logger::dayLog('app/selection', '认证接口请求失败' . $user_id, $res);
            if (isset($res['res_data']['reason']) && !empty($res['res_data']['reason']) && in_array($res['res_code'], ['105003', '105007'])) {
                exit($this->returnBack($res['res_code'], [], $res['res_data']['reason']));
            }
            exit($this->returnBack('10220'));
        }

        if (empty($selectionObj)) {
            $condition = [
                'user_id' => $user_id,
                'type' => $type,    //认证类型
                'source' => $source,  //认证来源
            ];
            $save_result = (new Selection())->addRecord($condition);
        } else {
            $condition = [
                'source' => $source,  //认证来源
                'process_code' => ''
            ];
            $save_result = $selectionObj->updateRecord($condition);
        }
        if (empty($save_result)) {
            Logger::dayLog('app/selection', '请求认证之后存储数据yi_selection失败' . $user_id, $save_result);
            exit($this->returnBack('99987'));
        }

        $array = [
            'url' => $res['res_url']
        ];
        exit($this->returnBack('0000', $array));
    }
}