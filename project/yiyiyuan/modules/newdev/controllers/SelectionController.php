<?php
namespace app\modules\newdev\controllers;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Selection;
use Yii;

class SelectionController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    public function actionMiddle()
    {
        $this->layout = 'depos/index';
        $userId = $this->get('user_id');
        $type = $this->get('source');//1学信网 2社保 3公积金 6淘宝
        $taskId = $this->get('task_id');
        $requestId = $this->get('request_id');
        $page_type = $this->get('page_type');
        Logger::dayLog('newdev/selection/middle', '参数',$page_type, $userId, $type, $taskId, $requestId);
        if (empty($userId) || empty($type) || empty($taskId) || empty($requestId)) {
            exit('parameter error');
        }
        $selectionObj = (new Selection())->getByUserIdAndTpey($userId, $type);
        $condition = [
            'user_id' => $userId,
            'type' => $type,
            'task_id' => $taskId,
            'requestid' => $requestId
        ];
        if (!empty($selectionObj)) {
            $result = $selectionObj->updateRecord($condition);
        } else {
            $result = (new Selection())->addRecord($condition);
        }
        if (empty($result)) {
            Logger::dayLog('newdev/selection/middle', '记录失败user_id：' . $userId, $condition, $result);
            exit('record error');
        }
        $cbUrl = Yii::$app->request->hostInfo . '/new/notifyselection';
        $apiResult = (new Http())->selection_save($userId, $type, $cbUrl, $taskId, $requestId);
        if ($apiResult['res_code'] != '105002'  &&  $apiResult['res_code'] !=0) {
            Logger::dayLog('newdev/selection/middle', 'save api错误：' . $userId, $apiResult);
            $meg = 'save api error';
            if (isset($apiResult['res_data']['reason']) && !empty($apiResult['res_data']['reason'])) {
                $meg = [
                    'res_code' => $apiResult['res_code'],
                    'res_msg' => $apiResult['res_data']['reason'],
                ];
            }
            exit(json_encode($meg));
        }
        $newObj = (new Selection())->getById($selectionObj->id);
        $newResult = $newObj->saveGetting();
        if (empty($newResult)) {
            Logger::dayLog('newdev/selection/middle', '中间状态更新失败,id：' . $selectionObj->id, $newResult);
            exit('record update error');
        }
        $from = 2;//h5
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $from = 1;//app
        }
        return $this->render('middle', [
            'from' => $from,
            'page_type' => $page_type
        ]);
    }
}
