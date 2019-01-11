<?php

namespace app\modules\newdev\controllers;


use app\commonapi\Logger;
use app\models\news\Prize;
use app\models\news\PrizeList;
use app\models\news\User;
use yii\data\Pagination;
use Yii;

class PrizeController extends NewdevController
{
    public $pageSize = 10;

//  	public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    //微信端-我的-我的奖品页面
    public function actionIndex()
    {
        $user = $this->getUser();
        $userId = !empty($user)?$user->user_id:$this->get('user_id',0);
        $csrf = $this->getCsrf();
        $now_time = date('Y-m-d H:i:s');
        $count = PrizeList::find()->where(['user_id' => $userId, 'status' => 6, 'use_status' => 1])->andWhere("start_time < '$now_time'")->andWhere("end_time > '$now_time'")->count();
        $max_page = ceil($count / $this->pageSize);
        //获取微信分享接口所需相关参数
        $jsinfo = $this->getWxParam();
        return $this->render('index', [
            'csrf' => $csrf,
            'user_id' => $userId,
            'count' => $max_page,
            'jsinfo' =>$jsinfo,
            'img_url' => Yii::$app->params['img_url'],
        ]);
    }

    public function actionPrizeajax()
    {
        $page = $this->post('page');
        $userId = $this->post('user_id');
        if (empty($userId)) {
            $array = ['res_code' => 2];
            return json_encode($array);
        }
        $now_time = date('Y-m-d H:i:s');
        $userinfo = User::findOne($userId);
        if(empty($userinfo)){
            $array = ['res_code' => 3];
            return json_encode($array);
        }
        $page = $page > 0 ? intval($page) : 1;
        $pageSize = $this->pageSize;
        $offset = ($page - 1) * $pageSize;
        $count = PrizeList::find()->where(['user_id' => $userinfo->user_id, 'status' => 6, 'use_status' => 1])->andWhere("start_time < '$now_time'")->andWhere("end_time > '$now_time'")->count();

        $max_page = ceil($count / $this->pageSize);
        $query = PrizeList::find()->where(['user_id' => $userinfo->user_id, 'status' => 6, 'use_status' => 1])->andWhere("start_time < '$now_time'")->andWhere("end_time > '$now_time'");
        $prize = $query->offset($offset)
            ->limit($pageSize)
            ->asArray()
            ->all();

        if ($prize) {
            foreach ($prize as $key => $value) {
                $prize_data = Prize::findone($value['prize_id']);
                $prize[$key]['prize_pic'] = empty($prize_data)?'':$prize_data->prize_pic;
                $prize[$key]['create_time'] = date('Y年m月d日', strtotime($value['create_time']));
            }
        }
     
        $array = ['res_code' => 1, 'res_msg' => '成功', 'total' => $count, 'pageSize' => $pageSize, 'totalPage' => $max_page, 'list' => $prize];
        return json_encode($array);
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

}