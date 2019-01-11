<?php
/**
 * 手续费户前置手续费账单
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/14
 * Time: 10:16
 */

namespace app\modules\balance\controllers;


use app\modules\balance\models\yyy\CgRemit;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ServiceController extends AdminController
{
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];
    /*
     * 存管-前置服务费
       SELECT a.`order_id`,
       `real_amount`-`settle_amount` txFee,
       `last_modify_time`
  FROM `yi_cg_remit` a
 WHERE `remit_status` IN('SUCCESS')
   and `real_amount`> `settle_amount`
   and a.`last_modify_time`>= '2018-02-24'
   and a.`last_modify_time`< '2018-03-05'
 GROUP BY a.`order_id` ;
     *
     *
     */
    public function actionList()
    {
        $getData  = $this -> get();
        $where_config = [
            'start_time'            => date("Y-m-d 00:00:00", strtotime(ArrayHelper::getValue($getData, 'start_time', date("Y-m-d")))),
            'end_time'              => date("Y-m-d 23:59:59", strtotime(ArrayHelper::getValue($getData, 'end_time', date("Y-m-d")))),
        ];

        $oCgRemit = new CgRemit();
        $total = $oCgRemit->getServiceTotal($where_config);

        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => self::PAGE_SIZE,
        ]);
        $result = $oCgRemit->getServiceData($pages, $where_config);

        //总笔数
        $all_total = $oCgRemit->getServerAllTotal($where_config);
        //总金额
        $all_money = $oCgRemit->getServerAllMoney($where_config);

        return $this->render('list', [
            'getData'           => $getData,
            'pages'             => $pages,
            'result'            => $result,
            'all_total'         => $all_total,
            'all_money'         => $all_money,
        ]);
    }

    public function actionDown()
    {
        $getData  = $this -> get();
        $oCgRemit = new CgRemit();
        $data_set = $oCgRemit->serverDown(ArrayHelper::getValue($getData, 'bill_date'));
        $this->serverDown($data_set);
    }
}