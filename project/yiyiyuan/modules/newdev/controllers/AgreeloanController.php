<?php

namespace app\modules\newdev\controllers;

use app\commonapi\Common;
use app\models\news\User_loan;

class AgreeloanController extends NewdevController
{

    public function behaviors()
    {
        return [];
    }

    public function actionIndex()
    {
        $this->getView()->title = "先花一亿元居间服务及借款协议（五方）";
        $this->layout = 'agreement';
        return $this->render('index');
    }

    public function actionActivity()
    {
        $this->getView()->title = "活动规则";
        $this->layout = 'rule';
        return $this->render('activity');
    }

    public function actionSafety()
    {
        $this->getView()->title = "信息安全";
        $this->layout = 'rule';
        return $this->render('safety');
    }

    public function actionSesaauth()
    {
        $this->getView()->title = "芝麻信用授权协议";
        return $this->render('sesaauth');
    }

    //微信端
    public function actionWapactivity()
    {
        $this->getView()->title = "活动规则";
        return $this->render('wapactivity');
    }

    //微信端
    public function actionWapsafety()
    {
        $this->getView()->title = "信息安全";
        return $this->render('wapsafety');
    }

    //微信端
    public function actionWapsesaauth()
    {
        $this->getView()->title = "芝麻信用授权协议";
        return $this->render('wapsesaauth');
    }

    //微信端
    public function actionWsm()
    {
        $this->layout = 'agreement';
        $loan_id = $this->get('loan_id');
        $user_loan = User_loan::findOne($loan_id);
        $endamount = $user_loan->getRepaymentAmount($user_loan);
        $daxie_endamount_num = Common::get_amount_num($user_loan->amount);
        $endamount = Common::get_amount_num($endamount);
        $dateTime = [
            'y' => date('Y',strtotime($user_loan->end_date)),
            'm' => date('m',strtotime($user_loan->end_date)),
            'd' => date('d',strtotime($user_loan->end_date)),
        ];
        $this->getView()->title = "微神马协议";
        return $this->render('wsm',[
            'userloan'=>$user_loan,
            'daxie_endamount_num' => $daxie_endamount_num,
            'endamount' => $endamount,
            'dateTime' => $dateTime,
        ]);
    }

    public function actionContactlist(){
        $this->layout = 'new/agreeloan';
        $this->getView()->title = "协议";
        return $this->render('contactlist');
    }

    /**
     * 借款五方合同展示
     * @return string
     */
    public function actionAgreeloan() {
        $this->getView()->title = "先花一亿元居间服务及借款协议（五方）";
        $this->layout = 'agreement';
        $url = '/new/loan/second';
        return $this->render('agreeloan', [ 'url' => $url,]);
    }

    /**
     * 久富融资协议
     * @return string
     */
    public function actionJiufu() {
        $this->getView()->title = "融资文件";
        $this->layout = 'agreement';
        $url = '/new/loan/second';
        return $this->render('jiufu', [ 'url' => $url,]);
    }

    /**
     * 投保协议
     * @return string
     */
    public function actionToubao() {
        $this->getView()->title = "保险条款";
        $this->layout = 'agreement';
        $url = '/new/loan/second';
        return $this->render('toubao', [ 'url' => $url,]);
    }


}
