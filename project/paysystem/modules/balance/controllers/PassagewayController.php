<?php
/**
 * 通道账单
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/1/4
 * Time: 15:35
 */

namespace app\modules\balance\controllers;
use app\common\Common;
use app\modules\balance\models\PaymentDetails;
use app\modules\balance\models\UpPaymentFile;
use Yii;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;

class PassagewayController extends  AdminController
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
     * 上传文件显示
     * @return string
     */
    public function actionList()
    {
        //回款通道
        $return_channel = $this->returnChannel();
        
        $oUpPaymentFile = new UpPaymentFile();
        $getData = $this->get();
        $filter_where = [
            'start_time'        => ArrayHelper::getValue($getData, 'start_time', date("Y-m-d", time())),
            'end_time'          => ArrayHelper::getValue($getData, 'end_time', date("Y-m-d 23:59:59", time())),
            'channel_id'        => ArrayHelper::getValue($getData, 'channel_id', 0),
            'type'              => 1,//1:体外放款，2：体外回款
        ];
        $pages = new Pagination([
            'totalCount'    => $oUpPaymentFile->countPaymentData($filter_where),
            'pageSize'      => self::PAGE_SIZE,
        ]);
        $getAllData = $oUpPaymentFile->getAllData($pages, $filter_where);

        return $this->render('list', [
            'return_channel'            => $return_channel,
            'start_time'                => $filter_where['start_time'],
            'end_time'                  => date("Y-m-d", strtotime($filter_where['end_time'])),
            'getAllData'                => $getAllData,
            'pages'                     => $pages,
            'channel_id'                => ArrayHelper::getValue($filter_where, 'channel_id', 0),
        ]);
    }

    /**
     * 上传文件
     * @return string
     */
    public function actionUpbill()
    {
        //回款通道
        $return_channel = $this->returnChannel();
        //上传文件
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
                $oUpBillFile = new UpPaymentFile();
                $data_set = [
                    'channel_id'    => ArrayHelper::getValue($post_data, 'channel_id', 0),
                    'source'        => 1, //来源：0:初始,1:已上传,2:已下载
                    'bill_file'     => $to_path.$file_info, //上传文件名
                    'file_status'   => 0, //来源：0:初始, 1:锁定, 2:成功, 3:失败
                    'uid'           => Yii::$app->admin->id, //用户uid
                    'return_channel'=>$return_channel,//回款通道
                    'type'          => 1,//1:体外放款，2：体外回款
                ];
                $saveBillFile = $oUpBillFile -> savePassagewayFile($data_set);
                if ($saveBillFile){
                    return $this->returnFileJson("上传成功");
                }
                return $this->returnFileJson("上传失败");
            }
            return $this->returnFileJson($file_info);
        }

        return $this->render('upbill', [
            'return_channel'            => $return_channel,
        ]);

    }

    /**
     * 下载模板
     */
    public function actionDownmodel()
    {
        $this->downlist_model_xls();
    }

    /**
     * 文件数据
     */
    public function actionDateilslist()
    {
        $getData = $this->get();

        $filter_where = [
            'file_id'           => ArrayHelper::getValue($getData, 'id', ''),
            'client_id'        => ArrayHelper::getValue($getData, 'client_id', ''),
            'name'              => ArrayHelper::getValue($getData, 'name', ''),
        ];
        //var_dump($filter_where);die;
        $return_channel = ArrayHelper::getValue($getData, 'return_channel', '');
        //收款通道名称
        $return_channel_name = ArrayHelper::getValue($this->returnChannel(), $return_channel);
        $oPaymentDetails = new PaymentDetails();
        //总笔数
        $total = $oPaymentDetails->getFileIdTotal($filter_where);
        //var_dump($total);die;
        //总金额
        $money = $oPaymentDetails->getFileIdMoney($filter_where);
        //总手续费
        $fee = $oPaymentDetails->getFileIdFee($filter_where);
        //查找数据
        $pages = new Pagination([
            'totalCount' => $total,
            'pageSize' => 30,
        ]);
        $getFileData = $oPaymentDetails->getFileIdData($pages, $filter_where);

        return $this->render('dateilslist', [
            'id'                    => ArrayHelper::getValue($getData, 'id'),
            'getFileData'           => $getFileData,
            'pages'                 => $pages,
            'client_id'             => ArrayHelper::getValue($getData, 'client_id'),
            'name'                  => ArrayHelper::getValue($getData, 'name'),
            'total'                 => $total,
            'money'                 => $money,
            'fee'                   => $fee,
            'return_channel'        => $return_channel,
            'return_channel_name'   => $return_channel_name,
        ]);
    }
}