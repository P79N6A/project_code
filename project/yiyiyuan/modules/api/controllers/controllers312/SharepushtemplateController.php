<?php

namespace app\modules\api\controllers\controllers312;

use app\models\news\User;
use app\modules\api\common\ApiController;
use Yii;

class SharepushtemplateController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {

        $version = Yii::$app->request->post('version');
        $loan_id = Yii::$app->request->post('loan_id');
        $types = Yii::$app->request->post('type');
        $type = !empty($types) ? $types : 1;

        if (empty($version) || empty($loan_id) || empty($type)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }

        $user = User::findOne($loan_id);
        if (empty($user)) {
            $array = $this->returnBack('99997');
            echo $array;
            exit;
        }

        $appid = Yii::$app->params['AppID'];
        if ($type == 1) {
            $array = $this->returnBack('99997');
            echo $array;
            exit;
        } else if ($type == 2) {
            if ($user->status != 3) {
                $array = $this->returnBack('10073');
                echo $array;
                exit;
            }
            $Url = urlencode("http://mp.yaoyuefu.com/dev/invitation/cash?userid=" . $user['user_id']);
            // $Url = urlencode(Yii::$app->request->hostInfo . "/dev/invitation/cash?wid=");
            $shareurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid ."&redirect_uri=". $Url."&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";
            $template['title'] = '有人@你拆红包！';
            $template['desc'] = '【一条未读信息】快来认证我，答题成功，可获得2—20元现金红包';
        } else if ($type == 3) {
            $Url = urlencode("http://mp.yaoyuefu.com/new/activity/septemberinfo?from_code=".$user['invite_code']);
            $shareurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid ."&redirect_uri=". $Url."&response_type=code&scope=snsapi_userinfo&state=xhh123#wechat_redirect";

            $template['title'] = '金秋九月，秋风送爽！';
            $template['desc'] = '先花一亿元送来88元免息券！ ';
        }else if($type == 4){
            $appid = 'wxdafb70997991766c';
            $Url = urlencode("http://www.youxinyouqian.com/dev/activity/auth");
            $shareurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid ."&redirect_uri=". $Url."&response_type=code&scope=snsapi_userinfo&state=wyouxxinl#wechat_redirect";
            $template['title'] = '关注“有信令”完成认证';
            $template['desc'] = '您可以成为该公众号的会员，享受相应的会员特权！';
        }else if($type == 5){
            //$appid = 'wxebb286d89943a38b';  
            $Url = urlencode("http://mp.yaoyuefu.com/new/fiveyearactivity/index?invite_qcode=".$user['invite_code']);
            $shareurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid ."&redirect_uri=". $Url."&response_type=code&scope=snsapi_userinfo&state=wyouxxinl#wechat_redirect";
            $template['title'] = '庆周年，拿好礼';
            $template['desc'] = '拼手气，抽最高888元，还有更多好礼等你来';
        }else if($type == 6){
            $url = Yii::$app->params['app_url'];
            //$appid = 'wxebb286d89943a38b';
            $Url = urlencode($url."/new/juneactivity/index");
            $shareurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid ."&redirect_uri=". $Url."&response_type=code&scope=snsapi_userinfo&state=wyouxxinl#wechat_redirect";
            $template['title'] = '扭蛋赢奖励，好礼赚不停';
            $template['desc'] = '夏至未至，年中大回馈,扭蛋赢奖励，好礼赚不停';
        }else if($type == 7){
            $url = Yii::$app->params['app_url'];
            //$appid = 'wxebb286d89943a38b';
            $Url = urlencode($url."/borrow/pressuretestactivity/index?comeFrom=5&invite_code=".$user->invite_code);
            $shareurl = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . $appid ."&redirect_uri=". $Url."&response_type=code&scope=snsapi_userinfo&state=wyouxxinl#wechat_redirect";
            $template['title'] = '您有588元现金大红包 速领';
            $template['desc'] = '588元现金大礼包！助我一臂之力吧！就差你一个啦，注册即可~';
        }
        if (!empty($user->openid)) {
            $userwx = $user->userwx;
            if (!empty($userwx)) {
                $imgurl = !empty($userwx->head) ? $userwx->head : "";
            } else {
                $imgurl = "";
            }
        } else {
            $imgurl = "";
        }
        if($type==7){
            $imgurl = 'http://mp.yaoyuefu.com/borrow/311/images/activity-logo.png';
        }
        $array['shareurl'] = $shareurl;
        $array['imgurl'] = $imgurl;
        $array['title'] = $template['title'];
        $array['desc'] = $template['desc'];
        $array = $this->returnBack('0000', $array);
        echo $array;
        exit;
    }
}
