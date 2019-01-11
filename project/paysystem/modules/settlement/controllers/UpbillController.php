<?php

namespace app\modules\settlement\controllers;

use app\common\Common;
use app\models\App;
use app\models\bill\BillDetails;
use app\models\bill\ChannelBills;
use app\models\bill\UpBillFile;
use app\models\bill\ComparativeBill;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class UpbillController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    /**
     * 上传账单列表
     * @return string
     */
    public function actionIndex() {
        $pageSize = 20;
        $getData = $this->get();
        $oUpBillFile = new UpBillFile();
        if (!empty($getData)){
            $pages = new Pagination([
                'totalCount' => $oUpBillFile->countFilterData($getData),
                'pageSize'   => $pageSize,
            ]);
            $billFileData = $oUpBillFile->getFilterData($pages, $getData);
        }else {
            $pages = new Pagination([
                'totalCount' => $oUpBillFile->countAllData(),
                'pageSize'   => $pageSize,
            ]);
            $billFileData = $oUpBillFile->getAllData($pages);
        }
        //出款通道名称
        $passageOfMoney = $this->passageOfMoney();
        return $this->render('index', [
            'pages'          => $pages,
            'passageOfMoney' => $passageOfMoney,
            'billFileData'   => $billFileData,
            'getData'        => $getData,
        ]);
    }

    /**
     * 上传文件
     * @return string
     */
    public function actionUpfile()
    {
        if ($this->isPost()){
            $post_data = $this->post();
            $file_data = $_FILES;
            if (empty($file_data)){
                return $this->returnFileJson("上传文件不能为空！");
            }
            $to_path = Yii::$app->basePath.DIRECTORY_SEPARATOR.'web'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'bill'.DIRECTORY_SEPARATOR; //上传文件的目标路径
            $limit_ext = ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            //文件上传
            $file_info = Common::Uploadfun($file_data['file_name'], $to_path, $limit_ext, 70000000); //调用单文件上传函数
            //$file_info = '201711142941--744.xlsx';
            if (strpos($file_info, 'xls') > 0){
                //记录数据表
                $oUpBillFile = new UpBillFile();
                $data_set = [
                    'channel_id'    => ArrayHelper::getValue($post_data, 'channel_id', 0), //出款通道id:0:未知,1:融宝,2:宝付,3:畅捷,4:玖富,5:微神马,6:新浪,7:小诺理财
                    'source'        => 1, //来源：0:初始,1:已上传,2:已下载
                    'bill_file'     => $to_path.$file_info, //上传文件名
                    'file_status'   => 0, //来源：0:初始, 1:锁定, 2:成功, 3:失败
                    'uid'           => Yii::$app->admin->id, //用户uid
                ];
                $saveBillFile = $oUpBillFile -> saveBillFile($data_set);
                if ($saveBillFile){
                    return $this->returnFileJson("上传成功");
                }
                return $this->returnFileJson("上传失败");
            }
            return $this->returnFileJson($file_info);
        }
        //出款通道名称
        $passageOfMoney = $this->passageOfMoney();
        return $this->render('upfile', [
            'passageOfMoney' => $passageOfMoney,
        ]);
    }

    /**
     * 下载模板
     */
    public function actionDownmodel()
    {
        $this->downlist_model_xls();
    }

    public function actionDatalist()
    {
        $getData = $this->get();
        if (empty($getData) || empty($getData['channel_id'])){
            $this->redirect('/settlement/upbill/index');
        }
        $billtime = $getData['billtime'];
        $oComparativeBill = new ComparativeBill();
        //计算上传文件订单总笔数
        $file_count = $oComparativeBill->getFileCount($billtime);
        //分页调用
        $pages = new Pagination([
            'totalCount' => $file_count,
            'pageSize'   => '20'
        ]);
        //获取上传文件订单数据
        $res   = $oComparativeBill->getFileData($pages, $billtime);
        //计算上传文件订单总金额
        $total_money = $oComparativeBill -> getFileMoney($billtime);
        //计算上传文件订单总手续费
        $total_settle = $oComparativeBill -> getFileSettle($billtime);
        
        //出款通道名称
        $passageOfMoney = $this->passageOfMoney();
        return $this->render('datalist', [
            'channel_name'  => ArrayHelper::getValue($passageOfMoney, $getData['channel_id'], ''),
            'result'        => $res,
            'file_count'    => $file_count,
            'total_money'   => $total_money,
            'total_settle'  => $total_settle,
            'pages'         => $pages,
        ]);
    }
}
