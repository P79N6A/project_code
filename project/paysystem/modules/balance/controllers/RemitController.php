<?php
/**
 * 放款统计
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/23
 * Time: 9:59
 */
namespace app\modules\balance\controllers;
use app\modules\balance\models\yyy\CgRemit;
use app\modules\balance\common\CRemit;
use Yii;
use \yii\helpers\ArrayHelper;
use yii\data\Pagination;

class RemitController extends  AdminController
{
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];
    /**
     * 放款统计
     * @return string
     */
    public function actionList()
    {
        $pageSize = 30;
        $bondType = CgRemit::getBondtype();
        //$wayOfPayment = $this->wayOfPayment();//放款方式
        $capitalSide = $this->capitalSide();//资金方
        $typesOfStages = $this->typesOfStages();//还款方式
       // var_dump($capitalSide);die;
        $get  = $this -> get();
        $filter_where = [
            'days'          => empty($bondType[ArrayHelper::getValue($get, 'type', 0)])?0:$bondType[ArrayHelper::getValue($get, 'type', 0)],
            'start_time'    => ArrayHelper::getValue($get, 'start_time',date("Y-m-d")),
            'end_time'      => ArrayHelper::getValue($get, 'end_time',date("Y-m-d")),
            'capitalSide'   => ArrayHelper::getValue($get, 'capitalSide'),//资金方
            'wayOfPayment'   => ArrayHelper::getValue($get, 'wayOfPayment'),//放款方式
        ];
        $model = new CgRemit();
        $pages = new Pagination([
            'totalCount' => $model->countRemitData($filter_where),
            'pageSize' => $pageSize,
        ]);
        $res = $model->getRemitData($pages, $filter_where);
//var_dump($filter_where);die;
        //总笔数
        $all_num = $model->countRemitData($filter_where);
        $getAlldata =  $model->getRemitDatas($filter_where);

        //应还本金累计
        $money = ArrayHelper::getValue($getAlldata, 'money')+0;
        //应还利息累计
        $fee = ArrayHelper::getValue($getAlldata, 'fee')+0;
        //应还本金累计
        $total = $money+$fee;
        return $this->render('list', [
            'get'        => $get,
            'res'        => $res,
            'pages'      => $pages,
            'bondType'   => $bondType,
            'all_num'    => $all_num,
            'money'      => $money,
            'fee'        => $fee,
            'total'      => $total,
            'filter_where' => $filter_where,
            //'wayOfPayment' =>$wayOfPayment,
            'capitalSide'  =>$capitalSide,
            'typesOfStages' =>$typesOfStages,
        ]);
    }
    /**
     * Undocumented function
     * 导出明细Excel
     * @return void
     */
    public function actionExport()
    {
        $getData = $this->get();
        $bill_date = ArrayHelper::getValue($getData, 'bill_date');
        $days = ArrayHelper::getValue($getData, 'days');
        $capitalSide = $this->capitalSide();//资金方
        if(empty($bill_date)) return false;
        if(empty($days)) return false;
        $res = (new CgRemit)->getExportData($bill_date, $days);
        //var_dump($res);die;
        (new  CRemit)->exportExcel($res,$capitalSide);
        return json_encode(["msg"=>"success"]);
    }
}