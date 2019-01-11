<?php
/**
 * 差错账管理
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/4
 * Time: 15:35
 */
namespace app\modules\balance\controllers;
use app\modules\balance\models\PaymentDetails;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class MistakeController extends  AdminController
{
   /* public $vvars = [
        'menu' => 'pay',
        'nav' =>'pay',
    ];*/
    public function init(){
        $this->vvars['menu'] = 'channelcount';
        $this->vvars['nav'] = 'channelcount';
    }
    /**
     * 差错账管理
     * @return string
     */
    public function actionList()
    {
        //回款通道
        $return_channel = $this->returnChannel();
        //差错类型
        $errorStatus = $this->errorStatus();
        //审核状态
        $auditingStatus = $this->auditingStatus();
        //项目名称
        $aid = $this->getAid();
        //条件
        $getData = $this->get();

        $filter_where = [
            'return_channel'            => ArrayHelper::getValue($getData, 'return_channel'),
            'client_id'                 => ArrayHelper::getValue($getData, 'client_id'),
            'name'                      => ArrayHelper::getValue($getData, 'name'),
            'error_types'               => ArrayHelper::getValue($getData, 'error_types'),
            'auditing_status'           => ArrayHelper::getValue($getData, 'auditing_status'),
            'start_time'                => ArrayHelper::getValue($getData, 'start_time'),
            'end_time'                  => ArrayHelper::getValue($getData, 'end_time'),
            'source'                    =>1,
        ];
        $oPaymentDetails = new PaymentDetails();
        //总笔数
        $total = $oPaymentDetails->getMistakeTotal($filter_where);
        //查找数据
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => 30,
        ]);
        $getFileData = $oPaymentDetails->getMistakeData($pages, $filter_where);

        //总金额
        $amount = $oPaymentDetails-> getMistakeAmount($filter_where);
        //总手续费
        $fee = $oPaymentDetails-> getMistakeFee($filter_where);
        //var_dump($getFileData);die;

        return $this->render('list', [
            'return_channel'                => $return_channel,
            'errorStatus'                   => $errorStatus,
            'auditingStatus'                => $auditingStatus,
            'total'                         => $total,
            'getFileData'                   => $getFileData,
            'filter_where'                  => $filter_where,
            'amount'                        => $amount,
            'fee'                           => $fee,
            'pages'                         => $pages,
            'aid'                           => $aid
        ]);
    }

    /**
     * 详情
     */
    public function actionDetails()
    {
        $getData = $this->get();
        //var_dump($getData);die;
        $id = ArrayHelper::getValue($getData, 'id');
        $oPaymentDetails = new PaymentDetails();
        $detail = $oPaymentDetails->getDetails($id);
        if (empty($detail)){
            echo "暂无数据";exit;
        }
        //回款通道
        $return_channel = $this->returnChannel();
        //差错类型
        $errorStatus = $this->errorStatus();
        //审核状态
        $auditingStatus = $this->auditingStatus();

        return $this->render('dateils', [
            'result'            => $detail,
            'return_channel'    => $return_channel,
            'errorStatus'       => $errorStatus,
            'auditingStatus'    => $auditingStatus,
        ]);
    }

    public function actionUpdatebill()
    {

        if ($this->isPost()) {
            $post_data = $this->post();
            //查找错误账单
            $oPaymentDetails = new PaymentDetails();
            $fail_bill_data = $oPaymentDetails->getDetails(ArrayHelper::getValue($post_data, 'id', 0));
            if (empty($fail_bill_data)){
                return $this->returnFileJson("订单不存在");
            }

            //修改账单
            $update_bill_data = [
                //'error_types' => "差错已处理", //差错类型',
                'loss'          => (int)ArrayHelper::getValue($post_data, 'loss', 2),
                'state'         => (int)ArrayHelper::getValue($post_data, 'state', 2),
                //'error_types'   => 1, //差错状态',
                'type'          => (int)PaymentDetails::TYPE_SUCCESS, //账单类型：1正常，2差错',
                'reason'        => (string)ArrayHelper::getValue($post_data, 'reason', ''), //原因',
                'uid'           => Yii::$app->admin->id,
            ];
            $state = $fail_bill_data->updateData($update_bill_data);

            if ($state){
                return $this->returnFileJson("对账成功");
            }else{
                return $this->returnFileJson("对账失败");
            }

        }else{
            $this->redirect('index');
        }

    }

}