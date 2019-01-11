<?php

namespace app\modules\backend\controllers;
use app\common\ApiClientCrypt;
use Yii;

class PayController  extends  \app\modules\backend\controllers\AdminController {

    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    public function actionIndex() {
        $aid = Yii::$app->request->get('aid');
        if(!$aid && !is_numeric($aid)){
            $aid = 0;
        }
        $this->setNowAid(intval($aid));
        $this->redirect(Yii::$app->urlManager->createUrl('backend/app'));
    }
    public function actionSetting() {
        return $this->render('index',[]);
    }
    public function actionChannel() {
        return $this->render('index',[]);
    }
     /*public function actionTest(){
         $test = new ApiClientCrypt();
         $test->xhApiDomain = "http://www.paysystem.com/api";
         $data = [
             'orderid'=>'R2017041011335738ppp',
             'identityid'=>'666',
             'bankname'=>'交通银行',
             'bankcode'=>'jt',
             'card_type'=>1,
             'cardno'=>'6222620910012782003',
             'idcard'=>'42900119911020421X',
             'username'=>'lc',
             'phone'=>'18501171706',
             'productcatalog'=>'7',
             'productname'=>"购买电子产品",
             'productdesc'=>"购买电子产品",
             'amount'=>1,
             'orderexpdate'=>600,
             'business_code'=>'HSMFWX',
             'userip'=>'127.0.0.1',
             'callbackurl'=>'http://peanutweb.com/dev/web/recharge/'
         ];
         $res =$test->sent('/payroute/pay',$data);
         var_dump($res);
     }*/
}
