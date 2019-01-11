<?php

namespace app\modules\api\controllers\controllers310;

use app\commonapi\ErrorCode;
use app\models\news\User;
use app\models\service\UserloanService;
use app\modules\api\common\ApiController;
use Yii;

class GetloaninformationController extends ApiController
{

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $version = Yii::$app->request->post('version');
        $loan_id = Yii::$app->request->post('loan_id');
        if (empty($version) || empty($loan_id)) {
            $array = $this->returnBack('99994');
            echo $array;
            exit;
        }
        $userLoanService = new UserloanService();
        $info = $userLoanService->getLoanDetaile($loan_id);
        if ($info['rsp_code'] != '0000') {
            $array = $this->returnBack($info['rsp_code']);
            echo $array;
            exit;
        }

        $data = [
            'rsp_msg' => '成功',
            'alipay_name' => '萍乡海桐技术服务外包有限公司',//现下还款账户
            'alipay_account' => 'pxht@xianhuahua.com',//现下还款账户
        ];
        $array = array_merge($info, $data);
        exit($this->returnBack('0000', $array));

    }
    
}
