<?php
/**
 * 逾期已收统计
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 9:53
 */
namespace app\modules\balance\controllers;
use app\modules\balance\common\COverdue;
use app\modules\balance\models\yyy\CgRemit;
use app\modules\balance\common\CRemit;
use app\modules\balance\models\yyy\LoanRepay;
use app\modules\balance\models\yyy\OverdueLoan;
use app\modules\balance\models\yyy\Renew_amount;
use app\modules\balance\models\yyy\RenewalPaymentRecord;
use app\modules\balance\models\yyy\UserLoan;
use app\modules\balance\models\yx\Yxious;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ZrysController extends  AdminController
{

   public function init(){
       $this->vvars['menu'] = 'zrys';
       $this->vvars['nav'] = 'zrys';
   }

    public function actionIndex()
    {
        return $this->render('index');
    }
    public function actionList()
    {
        $pageSize = 30;
        $bondType = CgRemit::getBondtype();
        $get  = $this -> get();
        //$post = $this->reqData;
        //var_dump($get);die;
        $getdata = [
            'order_pay_no'    => trim(ArrayHelper::getValue($get, 'order_pay_no'),' '),
            'paybill'         => trim(ArrayHelper::getValue($get, 'paybill'),' '),
            //'realname'        => trim(ArrayHelper::getValue($get, 'realname'),' '),
            //'mobile'          => trim(ArrayHelper::getValue($get, 'mobile'),' '),
            'channel_id'        => trim(ArrayHelper::getValue($get, 'channel_id'),' '),
            'is_end'          => trim(ArrayHelper::getValue($get, 'is_end'),' '),
            'status'          => trim(ArrayHelper::getValue($get, 'status'),' '),
            'repay_time'      => trim(ArrayHelper::getValue($get, 'repay_time',date('Y-m-d', strtotime('-7 day'))),' '),
            'end_time'        => trim(ArrayHelper::getValue($get, 'end_time',date('Y-m-d')),' '),
        ];
        $re = new Yxious();

        //总笔数
        $total = $re->getTotal($getdata);
        //总金额
        $moneySum = $re->getSum($getdata,'money');
        //延期总金额
        $chase_amount_money = $re->getSum($getdata,'chase_amount');

        $yan = Number_format(str_replace(',','',$chase_amount_money)-str_replace(',','',$moneySum));
        //优惠卷累计
        //$couponSum = $re->couponSum($getdata,'val');

        //实收累计
        $actualMoneySum = Number_format(str_replace(',','',$moneySum)+ str_replace(',','',$yan));

        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => 30,
        ]);

        $resultAllData = $re->getAllData($getdata,$pages);
        return $this->render('list',[

            'order_pay_no'       =>ArrayHelper::getValue($get, 'order_pay_no'),
            'paybill'            =>ArrayHelper::getValue($get, 'paybill'),
            //'realname'           =>ArrayHelper::getValue($get, 'realname'),
            //'mobile'             =>ArrayHelper::getValue($get, 'mobile'),
            'channel_id'           =>ArrayHelper::getValue($get, 'channel_id'),
            'is_end'             =>ArrayHelper::getValue($get, 'is_end'),
            'status'             =>ArrayHelper::getValue($get, 'status'),
            'repay_time'         =>ArrayHelper::getValue($getdata, 'repay_time'),
            'end_time'           =>ArrayHelper::getValue($getdata, 'end_time'),
            'resultAllData'      => $resultAllData,
            'total'              =>$total,
            'actualMoneySum'     =>$actualMoneySum,
            //'couponSum'          =>$couponSum,
            'moneySum'           =>$moneySum,
            'yan'                =>$yan,
            'chase_amount_money' =>$chase_amount_money,
            'pages'              => $pages



        ]);

        //var_dump($filter_where['mobile']);die;
       // return $this->render('list');
    }



}