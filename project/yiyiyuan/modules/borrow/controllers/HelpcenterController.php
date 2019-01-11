<?php

namespace app\modules\borrow\controllers;

use app\models\news\HelpCenterPosition;
use app\models\news\HelpCenterList;
use app\models\news\HelpCenterRecord;
use app\commonapi\ErrorCode;
use Yii;

class HelpcenterController extends BorrowController {
    public function behaviors() {
        return [];
    }

    /**
     * 帮助中心首页页
     */
    public function actionIndex() {
        $this->getView()->title = "帮助中心";
        $this->layout = "userinfo/requireinfo";
        $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
        $condition = ['position' => 1];
        $order = 'sort asc';
        $list = (new HelpCenterPosition())->getHelpcenterposition($condition, $order);//问题列表
        $customer_service = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&robotFlag=1&partnerId='.$user_id; //客服链接
        $advise_url = 'http://mp.yaoyuefu.com/borrow/propose?user_id=' . $user_id;
        return $this->render('index', [
            'user_id' => $user_id,
            'list' => $list,
            'customer_service' => $customer_service,
            'advise_url' => $advise_url,
        ]);
    }

    /**
     * 帮助中心列表页
     */
    public function actionList() {
        $this->getView()->title = "帮助中心";
        $this->layout = "userinfo/requireinfo";
        $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
        $category = $this->get('category', '');
        $position = $this->get('position', '');
        $order = 'sort asc';
        $position_or_category = 0; //1:分类 2：位置
        if (empty($category) && empty($position)) {
            exit('参数不完整');
        }
        if (in_array($position, [1, 2])) {
            return $this->redirect('/borrow/helpcenter?user_id=' . $user_id);
        }
        if (!empty($category)) {
            $condition = ['type' => $category];
            $list = (new HelpCenterList())->getHelpcenterlist($condition, $order);//问题列表
            $position_or_category = 1;
        } elseif (!empty($position)) {
            $condition = ['position' => $position];
            $list = (new HelpCenterPosition())->getHelpcenterposition($condition, $order);//问题列表
            $position_or_category = 2;
        }
        
        if ( (count($list) == 1) && ($position_or_category == 2)) { //直接跳转问题详情页
            return $this->redirect('/borrow/helpcenter/detail?help_id=' . $list[0]->help_id);
        }elseif( (count($list) == 1) && ($position_or_category == 1)){
            return $this->redirect('/borrow/helpcenter/detail?help_id=' . $list[0]->id);
        }

        return $this->render('list', [
            'user_id' => $user_id,
            'list' => $list,
            'position_or_category' => $position_or_category,

        ]);
    }

    /**
     * 帮助中心详情页
     */
    public function actionDetail() {
        $this->getView()->title = "帮助中心";
        $this->layout = "userinfo/requireinfo";
        $user_id = empty($this->getUser()) ? $this->get('user_id') : $this->getUser()->user_id;
        $help_id = $this->get('help_id');
        $help_info = (new HelpCenterList())->getHelpcenterByHelpId($help_id);
        $customer_service = 'https://www.sobot.com/chat/h5/index.html?sysNum=f0af5952377b4331a3499999b77867c2&robotFlag=1&partnerId='.$user_id; //客服链接
        $condition = ['help_id' => $help_id, 'user_id' => $user_id];
        $oHelpCenterRecord = (new HelpCenterRecord())->getHelpCenterRecord($condition);
        $useful_or_useless = !empty($oHelpCenterRecord) ? $oHelpCenterRecord->status : 0; //0:初始 1:有用 2：无用
        return $this->render('detail', [
            'user_id' => $user_id,
            'help_info' => $help_info,
            'customer_service' => $customer_service,
            'csrf' => $this->getCsrf(),
            'useful_or_useless' => $useful_or_useless,
        ]);
    }

    /**
     * 有用无用点击
     */
    public function actionHelpusefulclick() {
        $user_id = $this->post('user_id');
        $useful_or_useless = $this->post('type'); //1:有用 2：无用
        $help_id = $this->post('help_id');
        if (empty($user_id) || empty($useful_or_useless) || empty($help_id)) {
            $array = $this->errorreback('99994');
            return json_encode($array);
        }
        $condition = ['help_id' => $help_id, 'user_id' => $user_id];
        $oHelpCenterRecord = (new HelpCenterRecord())->getHelpCenterRecord($condition);
        $oHelpCenterList = (new HelpCenterList())->getHelpcenterByHelpId($help_id);
        if (empty($oHelpCenterList)) {
            $array = $this->errorreback('1002', '未知错误');
            return json_encode($array);
        }
        if (!empty($oHelpCenterRecord)) {
            if (($useful_or_useless == $oHelpCenterRecord->status)) {
                $array = $this->errorreback('1001', '无效点击');
                return json_encode($array);
            }
            //更改有用无用状态
            $update_condition = ['status' => $useful_or_useless];
            $update_result = $oHelpCenterRecord->update_record($update_condition);

        } else {
            $save_condition = [
                'user_id' => $user_id,
                'help_id' => $help_id,
                'status' => $useful_or_useless,
            ];
            $update_result = (new HelpCenterRecord())->addHelpCenterList($save_condition);
        }
        if (!$update_result) {
            $array = $this->errorreback('1003', '无效点击');
            return json_encode($array);
        }
        //更新该问题的有用无用统计数量
        $oHelpCenterList->update_list_useful_useless($useful_or_useless);
        $oHelpCenterList->refresh();
        $array = $this->errorreback('0000');
        $array['useful_number'] = $oHelpCenterList->useful_number;
        $array['useless_number'] = $oHelpCenterList->useless_number;
        return json_encode($array);
    }

    private function errorreback($code, $msg = '') {
        $errorCode = new ErrorCode();
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = !empty($msg) ? $msg : $errorCode->geterrorcode($code);
        return $array;
    }


}

