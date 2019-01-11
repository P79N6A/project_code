<?php

/**
 * 发送续期借款所有借款
 */
namespace app\commands;

use app\models\news\User_loan;
use Yii;
use yii\console\Controller;
use app\commonapi\Logger;
use app\common\Curl;
use app\models\news\Renewal_payment_record;
set_time_limit(0);
ini_set('memory_limit', '-1');
class SendrenewinfoController extends Controller {

    protected $renew = 0.2;
    public function actionIndex()
    {
        $time_in = date("Y-m-d", strtotime("-1 day"));
        $where = [
            'AND',
            [">", Renewal_payment_record::tableName() . ".create_time", $time_in],//todo create_time是否有索引
            [Renewal_payment_record::tableName().'.status'=> 1],
            [User_loan::tableName().'.status'=>[8,9]]
        ];
        $res = Renewal_payment_record::find()->joinWith('loanparent',true,'LEFT JOIN')->select([User_loan::tableName().'.loan_id',User_loan::tableName().'.parent_loan_id'])->distinct()->where($where)->orderBy('loan_id DESC')->all();
        $result = [];
        foreach ($res as $key => $val) {
            $pid = $val['parent_loan_id'];
            $result[$pid][] = $val['loan_id'];
        }
        $data['data'] = json_encode($result);
        $data['sign'] = $this->encrySign($data);
        //调用贷后接口
        $url = Yii::$app->params['daihou_api_url'] . "/api/loan/renewalnum";
        //$url   = "http://www.xianhuahua.com/api/loan/renewalnum";
        $result  = (new Curl())->post($url, $data);
        $resultArr  = json_decode($result, true);
        var_dump($resultArr);
    }

    /**
     * 加密数据
     */
    public function encrySign($data) {
        if (empty($data) || !is_array($data)) {
            return '';
        }
        foreach($data as &$val) {
            $val = strval($val);
        }
        ksort($data);
        $signstr = http_build_query($data);
        $key     = Yii::$app->params['app_key'];
        $sign    = md5($signstr . $key);
        return $sign;
    }
}

