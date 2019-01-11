<?php

namespace app\modules\newdev\controllers;


use app\commonapi\ApiSign;
use app\commonapi\Logger;
use app\models\news\Renew_record;
use app\models\news\User_loan;
use Yii;
class RenewdelController extends NewdevController
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        return [];
    }

    /**
     * 下载地址
     * @return string
     */
    public function actionIndex()
    {
        $postData = $this->post('data');
        $sign = $this->post('_sign');
        Logger::dayLog('renewdel', $postData, $sign);
        if (empty($postData) || empty($sign)) {
            exit(json_encode(['rsp_code'=>'0001','msg'=>'缺少必要参数！']));
        }
        $apiSignModel = new ApiSign();
//        $postData = [
//            '223728152',
//            '223728154',
//            ];
//        echo json_encode($postData);
//        $postData = $apiSignModel->signData($postData);
//        var_dump($postData);die;
        $verify = $apiSignModel->verifyData($postData, $sign);
        if (!$verify) {
            exit(json_encode(['rsp_code'=>'0002','msg'=>'验签失败']));
        }
        $result = json_decode($postData, true);
        if (!$result || empty($result)) {
            exit(json_encode(['rsp_code'=>'0002','msg'=>'参数json解析错误']));
        }
//        var_dump($result);die;
        if(empty($result)){
            exit(json_encode(['rsp_code'=>'0001','msg'=>'缺少必要参数！']));
        }
        foreach($result as $key=>$value){
            $where = [
                'AND',
                ['<>',Renew_record::tableName() . '.authorize',6],
                [User_loan::tableName() . '.loan_id' => $value],
            ];
            $data = Renew_record::find()->joinWith('userloan',TRUE,'LEFT JOIN')->where($where)->one();
            if(empty($data) || $data->userloan->status!=4){
                continue;
            }

            $userloan=$data->userloan;
            $data->delete();
            $userloan->delete();
        }

        exit(json_encode(['rsp_code'=>'0000','msg'=>'成功']));
    }

}