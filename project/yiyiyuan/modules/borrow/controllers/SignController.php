<?php

namespace app\modules\borrow\controllers;

use app\commonapi\ApiSign;
use app\models\news\User;
use Yii;

class SignController extends BorrowController {
    public function behaviors() {
        return [];
    }

    //商城重定向入口
    public function actionIndex(){
        $user_token = $this->get('user_token', '');
        $sign = $this->get('sign', '');
        $url = $this->get('url', '');
        $sign_data = [
            'user_token' => $user_token,
            'url' => $url,
        ];
        $sign_json = json_encode($sign_data, JSON_UNESCAPED_UNICODE);
        $sign_result = (new ApiSign())->verifyData($sign_json, $sign);
        if (empty($sign_result)) {
            exit('验签失败');
        }

        if (empty($user_token) || empty($sign)) {
            exit('参数错误');
        }
        $o_user = (new User())->getUserinfoByMobile($user_token);
        if(empty($o_user)){
            exit('非法请求');
        }
        $arr = parse_url($url);
        $url = $arr['path'];
        if(isset($arr['query'])){
            $arr_query = $this->convertUrlQuery($arr['query']);
            if(isset($arr_query['user_id_store'])){
                $arr_query['user_id_store'] = urlencode($arr_query['user_id_store']);
            }
            $url = $arr['path'].'?'.$this->getUrlQuery($arr_query);
        }
        $url = !empty($url) ? $url : '';
        return $this->redirect($url);
    }

    private function convertUrlQuery($query)
    {
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param) {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }

    private function getUrlQuery($array_query)
    {
        $tmp = array();
        foreach($array_query as $k=>$param)
        {
            $tmp[] = $k.'='.$param;
        }
        $params = implode('&',$tmp);
        return $params;
    }
}
