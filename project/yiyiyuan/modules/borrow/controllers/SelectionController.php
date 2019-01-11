<?php
namespace app\modules\borrow\controllers;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Selection;
use app\models\news\Selection_bankflow;
use Yii;

class SelectionController extends BorrowController{
    public $enableCsrfValidation = false;

    public function behaviors(){
        return [];
    }

    /*
     * 选填信息认证
     */
    public function actionIndex(){
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type');
        $page_type = Yii::$app->request->post('page_type',0); //0：选填资料页 1：个人资料页来的

        if (empty($user_id) || empty($type)) {
            exit(json_encode(['code' => '2', 'msg' => '非法请求']));
        }

        $selectionObj = (new Selection())->getByUserIdAndTpey($user_id, $type);
        if (!empty($selectionObj)) {
            if ($selectionObj->process_code == '10002') {
                exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('10221')]));
            }
            $isVal = $selectionObj->getValidity();
            if (!empty($isVal)) {
                exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('10219')]));
            }
        }

        $url = Yii::$app->request->hostInfo . '/new/selection/middle?page_type='.$page_type.'&';
        $res = (new Http())->selection_choice($user_id, $type, $url);
        if (( $res['res_code'] != '105002' && $res['res_code'] != '0' ) || empty($res['res_url'])  ) {
            Logger::dayLog('borrow/selection', '认证接口请求失败' . $user_id, $res);
            if (isset($res['res_data']['reason']) && !empty($res['res_data']['reason']) && in_array($res['res_code'], ['105003', '105007'])) {
                exit(json_encode(['code' => '2', 'msg' => $res['res_data']['reason']]));
            }
            exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('10220')]));
        }

        if (empty($selectionObj)) {
            $condition = [
                'user_id' => $user_id,
                'type' => $type,//认证类型
                'source' => 1,//认证来源
            ];
            $save_result = (new Selection())->addRecord($condition);
        } else {
            $condition = [
                'source' => 1,//认证来源
                'process_code' => ''
            ];
            $save_result = $selectionObj->updateRecord($condition);
        }
        if (!$save_result) {
            Logger::dayLog('borrow/selection', '请求认证之后存储数据yi_selection失败' . $user_id, $save_result);
            exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('99987')]));
        }
        exit(json_encode(['code' => '1', 'data' => $res['res_url'], 'msg' => '成功']));
    }

    /*
     * 银行流水
     */
    public function actionBankflow(){
        $user_id = Yii::$app->request->post('user_id');
        $page_type = Yii::$app->request->post('page_type',0); //0：选填资料页 1：个人资料页来的
        if (empty($user_id)) {
            exit(json_encode(['code' => '2', 'msg' => '非法请求']));
        }

        $selectionObj = (new Selection_bankflow())->getByUserId($user_id);
        if (!empty($selectionObj)) {
            if ($selectionObj->process_code == '10002') {
                exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('10221')]));
            }
            $isVal = $selectionObj->getValidity();
            if (!empty($isVal)) {
                exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('10219')]));
            }
        }

        $show_url = Yii::$app->request->hostInfo . '/borrow/selection/bankflowmiddle/'.$page_type;
        $callback_url = Yii::$app->request->hostInfo . '/borrow/notifyselection';
        $res = (new Http())->bank_flow($user_id, $show_url, $callback_url);
        if ($res['res_code'] != '0') {
            Logger::dayLog('borrow/bankflow', '认证接口请求失败' . $user_id, $res);
            exit(json_encode(['code' => '2', 'msg' => $res['res_data']]));
        }
        if (empty($selectionObj)) {
            $condition = [
                'user_id' => $user_id,
                'source' => 1,//认证来源
                'requestid' => $res['res_data']['requestid'],
                'org_biz_no' => $res['res_data']['org_biz_no']
            ];
            $save_result = (new Selection_bankflow())->addRecord($condition);
        } else {
            $condition = [
                'source' => 1,//认证来源
                'process_code' => '',
                'requestid' => $res['res_data']['requestid'],
                'org_biz_no' => $res['res_data']['org_biz_no']
            ];
            $save_result = $selectionObj->updateRecord($condition);
        }
        if (!$save_result) {
            Logger::dayLog('borrow/bankflow', '请求认证之后存储数据yi_bankflow失败' . $user_id, $save_result);
            exit(json_encode(['code' => '2', 'msg' => $this->getErrorMsg('99987')]));
        }
        $jump_url = stripslashes($res['res_data']['jump_url']);
        exit(json_encode(['code' => '1', 'data' => $jump_url, 'msg' => '成功']));
    }


    public function actionBankflowmiddle($id = '') {
        $this->layout = 'depos/index';
        $org_biz_no = $this->get('orgBizNo');//开放平台请求流水号
        $biz_no = $this->get('bizNo');//数立平台生成流水号
        Logger::dayLog('borrow/bankflow/middle', '参数',$org_biz_no, $biz_no, $id);
        if (empty($biz_no) || empty($org_biz_no)) {
            exit('parameter error');
        }
        $selectionObj = (new Selection_bankflow())->getByOrgBizNo($org_biz_no);
        if (empty($selectionObj)) {
            exit('record error');
        }
        $apiResult = (new Http())->bank_flow_save($selectionObj['requestid'], $org_biz_no, $biz_no);
        if ($apiResult['res_code'] != '0') {
            Logger::dayLog('borrow/bankflow/middle', 'save api错误：' . $selectionObj['user_id'], $apiResult);
            $meg = 'save api error';
            if (isset($apiResult['res_data']['reason']) && !empty($apiResult['res_data']['reason'])) {
                $meg = [
                    'res_code' => $apiResult['res_code'],
                    'res_msg' => $apiResult['res_data']['reason'],
                ];
            }
            exit(json_encode($meg));
        }
        $newResult = $selectionObj->saveGetting();
        if (empty($newResult)) {
            Logger::dayLog('borrow/bankflow/middle', '中间状态更新失败,id：' . $selectionObj->id, $newResult);
            exit('record update error');
        }
        $from = 2;//h5
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'YYY_ANDROID') || strpos($_SERVER['HTTP_USER_AGENT'], 'yyyIOS')) {
            $from = 1;//app
        }
        return $this->render('middle', [
            'from' => $from,
            'page_type' => $id
        ]);
    }
}
