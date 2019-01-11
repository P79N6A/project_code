<?php
/**
 *  差错账列表
 */
namespace app\modules\settlement\controllers;


use app\common\Logger;
use app\models\bill\ComparativeBill;
use app\models\Manager;
use app\models\yyy\YiUserRemitList;
use \app\modules\settlement\common\CbillRemit;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class ErrorlistController  extends  AdminController {
    public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];

    /**
     * 差错账列表
     * @return string
     */
    public function actionList()
    {
        $getData = $this->get();
        $oComparativeBill = new ComparativeBill();
        //总笔数
        $total = $oComparativeBill->getErrorLisCount($getData);
        //分页调用
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize'   => '20'
        ]);
        $res = $oComparativeBill->getErrorLisData($pages, $getData);
        //总金额
        $total_money = $oComparativeBill->getErrorLisMoney($getData);
        //总手续费
        $total_fee = $oComparativeBill->getErrorLisFee($getData);
        return $this->render('list', [
            'errorTypes'        => $this->errorTypes(),
            'passageOfMoney'    =>$this->passageOfMoney(),
            'result'            => $res,
            'getData'           => $getData,
            'pages'             => $pages,
            'total'             => $total,
            'total_money'       => $total_money,
            'total_fee'         => $total_fee,
            'url_params'       => http_build_query($getData),
        ]);
    }

    /**
     * 账单详情
     * @return string
     */
    public function actionDetails()
    {
        $getData = $this->get();

        if (empty($getData['id'])){
            $this->redirect('/settlement/reconciliation/list');
        }

        $oComparativeBill = new ComparativeBill();
        $result = $oComparativeBill->getBillData($getData['id']);
        //查找用户名
        $oManager = new Manager();
        $opt_user_info = $oManager->findIdentity(ArrayHelper::getValue($result, 'uid', 0));

        return $this->render('details', [
            'errorTypes'        => $this->errorTypes(),
            'passageOfMoney'    =>$this->passageOfMoney(),
            'result'            => $result,
            'opt_name'          => ArrayHelper::getValue($opt_user_info, 'realname', ''),
            'url_params'       => http_build_query($getData),
        ]);

    }
    
    public function actionUpdatedetails()
    {
        $postData = $this->post();
        //修改数据然后进行对账
        if (!empty($postData)){
            $error_status = ArrayHelper::getValue($postData, 'error_status', 0);
            //未处理
            if ($error_status == 2){
                $ret = $this->updateUntreated($postData);
            }elseif ($error_status == 3){
                $ret = $this->updateShutdown($postData);
            }else {
                $ret = $this->updateErrorBill($postData);
            }
            if ($ret === true){
                return json_encode(['msg'=>'更新成功']);
            }elseif($ret === false) {
                return json_encode(['msg' => '数据存在问题，请核对']);
            }else{
                return json_encode(['msg' => ArrayHelper::getValue($this->errorTypes(), $ret, '数据存在问题，请核对')]);
            }
        }

    }

    public function actionDown()
    {
        $postData = $this->get();
        $oComparativeBill = new ComparativeBill();
        $res = $oComparativeBill->getErrorLisDown($postData);
        $this->downlist_xls_uppper($res);
        return json_encode(["msg"=>"success"]);
    }

    /**
     * 修改差错账单
     * @param $data_set
     * @return bool
     */
    private function updateErrorBill($data_set)
    {
        //1.实例化支付类
        $oCbillRemit = new CbillRemit();
        $oPay = $oCbillRemit->createObject(ArrayHelper::getValue($data_set, 'channel_id', 0));
        if (empty($oPay)){
            return false;
        }
        //获取支付账单数据
        $pay_data = $oPay->getRemitOne(ArrayHelper::getValue($data_set, 'client_id', 0));
        //Logger::dayLog('bill/aa', 'content:',ArrayHelper::getValue($data_set, 'client_id', 0));
        //$pay_data = $oPay->getRemitOne('201705091659373732');
        if (empty($pay_data)){
            return 1;  //通道单边账
        }
        //支付系统状态
        $pay_remit_status = $oCbillRemit->payPassagewayStatus(ArrayHelper::getValue($pay_data, 'remit_status', 0));

        if (empty($pay_remit_status)){
            return 4;  //支付系统状态有误
        }
        //2.获取账单表中的数据
        $oComparativeBill= new ComparativeBill();
        $up_data = $oComparativeBill->getIdInfo(ArrayHelper::getValue($data_set, 'list_id', 0));
        if (empty($up_data)){
            return false;
        }
        //3.获取一亿元数据
        $oUserRemitList = new YiUserRemitList();
        $yi_data = $oUserRemitList->getDataByReqId(ArrayHelper::getValue($pay_data, 'req_id', 0));
        if (empty($yi_data)){
            return 2; //支付系统单边账'
        }
        $yi_remit_stats = $oCbillRemit->yiPassagewayStatus(ArrayHelper::getValue($yi_data, 'remit_status', ''));
        //Logger::dayLog('bill/aa', 'content:',json_encode($yi_remit_stats));
        if (empty($yi_remit_stats)){
            return 8; //业务系统状态有误
        }

        //4.金额对比
        $up_money   = ArrayHelper::getValue($up_data, 'settle_amount', '');
        $pay_money  = ArrayHelper::getValue($pay_data, 'settle_amount', '');
        $yi_money   = ArrayHelper::getValue($yi_data, 'settle_amount', '');
        if (bccomp($up_money, $pay_money) != 0){
            return 3; //支付系统有误
        }
        if (bccomp($up_money, $yi_money) != 0){
            return 7; //业务系统金额有误
        }
        return $this->updateSuccess($data_set, $yi_money);

    }

    /**
     * 未处理
     * @param $data_set
     * @return bool
     */
    private function updateUntreated($data_set)
    {
        $oComparativeBill= new ComparativeBill();
        $up_data = $oComparativeBill->getIdInfo(ArrayHelper::getValue($data_set, 'list_id', 0));
        if (empty($data_set['reason'])){
            return false;
        }
        $update_data = [
            'reason' => $data_set['reason'],
            'uid'    =>  Yii::$app->admin->id,
        ];
        return $up_data->updateBill($update_data);
        
    }

    /**
     * 关闭订单
     * @param $data_set
     * @return bool
     */
    private function updateShutdown($data_set)
    {
        $oComparativeBill= new ComparativeBill();

        $up_data = $oComparativeBill->getIdInfo(ArrayHelper::getValue($data_set, 'list_id', 0));
        if (empty($data_set['reason'])){
            return false;
        }
        $update_data = [
            'error_types'   => 9,
            'error_status'  => 3, //关闭订单
            'reason'        => $data_set['reason'],
            'type'          => 3, //处理错误
            'uid'           => Yii::$app->admin->id,
        ];
        return $up_data->updateBill($update_data);
    }

    private function updateSuccess($data_set, $yi_money)
    {
        $oComparativeBill= new ComparativeBill();

        $up_data = $oComparativeBill->getIdInfo(ArrayHelper::getValue($data_set, 'list_id', 0));
        if (empty($data_set['reason'])){
            return false;
        }
        $update_data = [
            'amount'        => $yi_money,
            'error_status'  => 1, //已处理
            'reason'        => ArrayHelper::getValue($data_set, 'reason', ''),
            'type'          => 3, //处理错误
            'channel_status'=> 7,
            'uid'           =>  Yii::$app->admin->id,
        ];
        return $up_data->updateBill($update_data);
    }
}
