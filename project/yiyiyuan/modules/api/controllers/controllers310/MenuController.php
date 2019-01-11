<?php

namespace app\modules\api\controllers\controllers310;

use app\models\news\User;
use app\modules\api\common\ApiController;
use app\models\news\White_list;
use Yii;

class MenuController extends ApiController {

    public $enableCsrfValidation = false;

    public function actionIndex() {

        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id', 0);
        if (empty($version)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $menuArr = [
            //参数1：名称 2图片地址 3h5地址 4原生
            '0' => [0, 3, 4, 8], //未登录
            '1' => [0, 3, 4, 8], //登录白名单
            '2' => [0, 1, 2, 3, 4, 5, 6, 7, 10, 8, 9], //登录普通用户
        ];

        $allList = $this->getAll($user_id);
        if ($user_id == 0 || $user_id == 'empty') {
            $menu_key = 0;
        } else {
            $white_array = ['13439660605', '18500310315', '18500597522', '15910690412', '18610291548'];
            $user = User::findOne($user_id);
            if (!empty($user)) {
                $menu_key = in_array($user->mobile, $white_array) ? 1 : 2;
            }
        }
        $arr = [];
        foreach ($menuArr[$menu_key] as $key => $val) {
            $arr[] = $allList[$val];
        }
        $resInfoArr = $this->returnBack('0000', ['list' => $arr]);
        echo $resInfoArr;
        exit;
    }

    private function getAll($user_id = '') {
        $imgBaseUrl = Yii::$app->params['app_url'] . '/images/applist/';
        //图标数组
        $icoArrList = [
            '0' => "shop.png", //商城订单
            '1' => "list.png", //账单
            '2' => "loan.png", //借款记录
            '3' => "bank.png", //银行卡
            '4' => "invite.png", //邀请好友
            '5' => "coupon.png", //优惠券
            '6' => "prize.png", //我的奖品
            '7' => "help.png", //帮助中心
            '8' => "setting.png", //设置
            '9' => "weixin.png", //公众号
        ];
        return [
            0 => [
                'title' => '商城订单',
                'url' => '',
                'imgUrl' => $imgBaseUrl . $icoArrList[0],
                'type' => '0',
            ],
            1 => [
                'title' => '账单',
                'url' => '',
                'imgUrl' => $imgBaseUrl . $icoArrList[1],
                'type' => '1',
            ],
            2 => [
                'title' => '借款记录',
                'url' => '',
                'imgUrl' => $imgBaseUrl . $icoArrList[2],
                'type' => '2',
            ],
            3 => [
                'title' => '银行卡',
                'url' => '',
                'imgUrl' => $imgBaseUrl . $icoArrList[3],
                'type' => '3',
            ],
            4 => [
                'title' => '邀请好友',
                'url' => '',
                'imgUrl' => $imgBaseUrl . $icoArrList[4],
                'type' => '4',
            ],
            5 => [
                'title' => '优惠券',
                'url' => Yii::$app->request->hostInfo . '/new/coupon/couponlist?user_id=' . $user_id,
                'imgUrl' => $imgBaseUrl . $icoArrList[5],
                'type' => '5',
            ],
            6 => [
                'title' => '我的奖品',
                'url' => Yii::$app->request->hostInfo . '/new/prize/index?user_id=' . $user_id,
                'imgUrl' => $imgBaseUrl . $icoArrList[6],
                'type' => '6',
            ],
            7 => [
                'title' => '帮助中心',
                'url' => Yii::$app->request->hostInfo . '/borrow/helpcenter/list?position=1&user_id=' . $user_id,
                'imgUrl' => $imgBaseUrl . $icoArrList[7],
                'type' => '7',
            ],
            8 => [
                'title' => '设置',
                'url' => '',
                'imgUrl' => $imgBaseUrl . $icoArrList[8],
                'type' => '8',
            ],
            9 => [
                'title' => '公众号',
                'url' => '',
                'imgUrl' => $imgBaseUrl . $icoArrList[9],
                'type' => '9',
            ],
            10 => [
                'title' => '消费凭证上传',
                'url' => Yii::$app->request->hostInfo . '/borrow/tradinglist/list?source=2&user_id=' . $user_id,
                'imgUrl' => $imgBaseUrl . $icoArrList[2],
                'type' => '10',
            ]
        ];
    }

}
