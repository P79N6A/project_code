<?php
namespace app\modules\api\controllers\controllers312;

use Yii;
use app\models\news\User;
use app\models\news\Coupon_list;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\modules\api\common\ApiController;

class CouponlistController extends ApiController
{

    public $enableCsrfValidation = false;

    public function actionIndex()
    {

        $version = Yii::$app->request->post('version');
        $user_id = Yii::$app->request->post('user_id');
        $type = Yii::$app->request->post('type');//1 2 3 4 借款券 5还款券
        $status = Yii::$app->request->post('status'); //使用状态 非必填
        $coupon_id = Yii::$app->request->post('coupon_id'); //勾选状态 非必填

        if (empty($version) || empty($user_id) || empty($type)) {
            exit($this->returnBack('99994'));
        }

        if ($type == 1) {
            $type = [1, 2, 3, 4];
        } elseif ($type == 2) {
            $type = 5;
        }

        $userinfo = (new User())->getUserinfoByUserId($user_id);
        if (!$userinfo) {
            exit($this->returnBack('10001'));
        }

        //优惠券输入不正确
        if (!empty($coupon_id)) {
            $user_coupon = Coupon_list::find()->where(['mobile' => $userinfo->mobile, 'type' => $type, 'id' => $coupon_id])->one();

            if (empty($user_coupon)) {
                exit($this->returnBack('99996'));

            }
        }

        $now_time = date('Y-m-d H:i:s');
        $coupon_pull = new Coupon_list();
        //拉取面向全部用户类型的有效优惠券
        $couponlist_pull = $coupon_pull->pullCoupon($userinfo->mobile);

        if (empty($status)) {
            $status = 1; //未使用
        }

        $coupon_list = Coupon_list::find()->select('id,title,val,limit,start_date,end_date,status')->where(['mobile' => $userinfo->mobile, 'type' => $type, 'status' => $status])->andWhere("start_date <= '$now_time'")->andWhere("end_date > '$now_time'")->orderBy('end_date ASC,val DESC')->asArray()->all();

        if (!empty($coupon_list)) {
            foreach ($coupon_list as $key => $value) {
                $coupon_list[$key]['default_status'] = 0;
                $coupon_list[$key]['coupon_id'] = $value['id'];
                $endTime = $value['end_date'];
                $coupon_list[$key]['end_date'] = date('Y-m-d', strtotime($value['start_date'])) . '至' . date('Y-m-d', strtotime("$endTime-1 days"));
                unset($coupon_list[$key]['id']);
                unset($coupon_list[$key]['start_date']);

                if ($key == 0) {
                    $coupon_list[$key]['default_status'] = 1;
                }

                if (!empty($coupon_id) && ($coupon_id == $value['id']) && $key != 0) {
                    $coupon_list[$key]['default_status'] = 1;
                    $coupon_list[0]['default_status'] = 0;
                }

            }
        }

        $array = ['list' => $coupon_list];

        exit($this->returnBack('0000', $array));

    }


}