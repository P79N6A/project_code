<?php
namespace app\modules\borrow\controllers;

use app\commonapi\Logger;
use app\models\news\Favorite_contacts;
use app\commonapi\Apihttp;
use app\models\news\Juxinli;
use Yii;

class JuxinliauthController extends BorrowController{

    /**
     *  手机验证
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionPhoneajax(){
        $oUser = $this->getUser();
        //请求接口
        $result = $this->getApi($oUser, 1);
//        $result = [
//            'res_code' => 2,
//            'res_data' =>[
//                'requestid' => 2025,
//                'status' => 0,
//                'url' => 'http://182.92.80.211:8091/grab/register?id=KN4dLznrwWk%3D'
//            ]
//        ];
        if ($result['res_code'] != 0) {
            return $this->showMessage(1, '数据获取失败请稍后再试');
        }
        //跳转至开放平台，开始认证
        if ($result['res_data']['status'] == 0) {
            $condition = [
                'user_id' => $oUser->user_id,
                'type' => 1,
                'source' => (isset($result['res_data']['source']) && !empty($result['res_data']['source'])) ? $result['res_data']['source'] : ''
            ];
            (new Juxinli())->save_juxinli($condition);
            return $this->showMessage($result['res_code'], $result['res_data']);
        }
        //开放平台直接返回结果，不跳出
        if (in_array($result['res_data']['status'], [1, 3, 4])) {
            $res = $this->addJxl($result, $oUser);
            if ($res) {
                return $this->showMessage($result['res_code'], $result['res_data']);
            }
        }
        return $this->showMessage('99999', "认证失败");
    }

    /**
     * 调用开放平台运营商接口1
     * @param $userinfo
     * @param $from
     * @return array
     */
    private function getApi($userinfo, $from){
        //运营商风控
        $report_data = [
            'user_id' => $userinfo->user_id,
            'loan_id' => '',
            'mobile' => $userinfo->mobile,
            'aid' => 1,
        ];
        $report_result = (new Apihttp())->postReport($report_data);
        $report_result = json_decode($report_result, true);
        $source = '';
        if (!empty($report_result) && $report_result['rsp_code'] == '0000' && !empty($report_result['result'])) {
            Logger::dayLog('weixin/juxinliauth','运营商风控',$report_result['result']);
//            $source = $report_result['result'];
        }

        $postData = [
            'phone' => $userinfo->mobile,
            'name' => $userinfo->realname,
            'idcard' => $userinfo->identity,
            'from' => $from,//web
            'callbackurl' => Yii::$app->params['webcallback'],
            'source' => $source
        ];

        //组装联系人信息
        $fav = (new Favorite_contacts())->getFavoriteByUserId($userinfo->user_id);
        $contacts = '';
        if (!empty($fav)) {
            $contacts = json_encode(array(
                array(
                    'contact_tel' => $fav->phone,
                    'contact_name' => $fav->relatives_name,
                    'contact_type' => '0', //亲属
                ),
                array(
                    'contact_tel' => $fav->mobile,
                    'contact_name' => $fav->contacts_name,
                    'contact_type' => '6', //常用联系人
                )
            ));
        }
        if (!empty($contacts)) {
            $postData['contacts'] = $contacts;
        }

        $apiModel = new Apihttp();
        return $apiModel->postGrabRouteNew($postData);
    }

    private function addJxl($result, $userinfo){
        $condition = [
            'requestid' => $result['res_data']['requestid'],
            'user_id' => $userinfo->user_id,
            'source' => $result['res_data']['source'],
            'type' => 1,
        ];
        if ($result['res_data']['status'] == 1 || $result['res_data']['status'] == 4) {//采集成功 || 拉取中
            $condition['process_code'] = '10008';
            $condition['status'] = '1';
        } else {
            $condition['process_code'] = '30000';
        }
        $res = (new Juxinli())->save_juxinli($condition);
        return $res;
    }
}
