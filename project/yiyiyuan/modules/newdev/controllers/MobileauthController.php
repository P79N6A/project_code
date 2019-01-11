<?php
namespace app\modules\newdev\controllers;

use app\models\news\Favorite_contacts;
use app\models\news\User;
use app\commonapi\Apihttp;
use app\models\news\Common as Common2;
use app\models\news\Juxinli;
use Yii;

class MobileauthController extends NewdevController
{
    public $layout = 'reg';

    /*
     * 手机号认证
     */
    public function actionPhoneauth()
    {
        $this->layout = 'data';
        $this->getView()->title = "手机号认证";
        $orderInfo = $this->get('orderinfo');
        $user = $this->getUser();
        $userinfo = User::findOne($user->user_id);
        if (!$orderInfo) {
            if ($this->get('from') == 1) {
                $orderInfo = (new User())->getPerfectOrder($userinfo->user_id, 1, 1);
            } elseif ($this->get('from') == 2) {
                $orderInfo = (new User())->getPerfectOrder($userinfo->user_id, 1, 7);
            }
            $orderInfo = (new Common2())->create3Des(json_encode($orderInfo, true));
        }
        if (!$orderInfo) {
            exit('非法请求');
        }
        $this->setRedis($userinfo->user_id . '_orderInfo', $orderInfo);
        $jsinfo = $this->getWxParam();
        return $this->render('phoneauth', [
            'jsinfo' => $jsinfo,
            'mobile' => $userinfo->mobile,
            'last_mobile' => substr($userinfo->mobile, -4),
            'user' => $user,
            'csrf' => $this->getCsrf()
        ]);
    }

    /**
     *  手机验证
     * @return json [res_code:res_code, res_data:res_data]
     */
    public function actionPhoneajax()
    {
        $user = $this->getUser();
        $userinfo = User::findOne($user->user_id);
        //请求接口
        $result = $this->getApi($userinfo, 1);
        if ($result['res_code'] != 0) {
            return $this->showMessage(1, '数据获取失败请稍后再试');
        }
        //获取orderinfo
        $orderInfo = $this->getRedis($userinfo->user_id . '_orderInfo');
        if (!$orderInfo) {
            return $this->showMessage(1, '非法的请求');
        }
        $nextPages = $this->nextUrl($orderInfo, 7, 0);
        if ($result['res_code'] == 0) {
            $result['res_data']['nextUrl'] = $nextPages;
        }
        //跳转至开放平台，开始认证
        if ($result['res_data']['status'] == 0) {
            $condition = [
                'user_id' => $userinfo->user_id,
                'type' => 1,
            ];
            (new Juxinli())->save_juxinli($condition);
            return $this->showMessage($result['res_code'], $result['res_data']);
        }
        //开放平台直接返回结果，不跳出
        if (in_array($result['res_data']['status'], [1, 3, 4])) {
            $res = $this->addJxl($result, $userinfo);
            if ($res) {
                return $this->showMessage($result['res_code'], $result['res_data']);
            }
        }
        return $this->showMessage('99999', "认证失败");
    }

    /**
     *  密码验证
     * @param int $passwd
     * @return bool
     */
    private function chkPasswd($passwd)
    {
        $pass_pattern = "/^[0-9A-Za-z]{6,8}$/";
        if (!preg_match($pass_pattern, $passwd)) {
            return false;
        }
        return true;
    }

    /**
     * 获取csrf
     * @return string
     */
    private function getCsrf()
    {
        $csrf = Yii::$app->request->getCsrfToken();
        return $csrf;
    }

    /**
     * 调用开放平台运营商接口
     * @param $userinfo
     * @param $from
     * @return array
     */
    private function getApi($userinfo, $from)
    {
        $postData = [
            'phone' => $userinfo->mobile,
            'name' => $userinfo->realname,
            'idcard' => $userinfo->identity,
            'from' => $from,//web
            'callbackurl' => Yii::$app->params['webcallback'],
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

    private function addJxl($result, $userinfo)
    {
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
