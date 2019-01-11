<?php

namespace app\commands;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\commonapi\RSA;
use app\models\dev\Loan_status_notify;
use app\models\dev\User;
use app\models\dev\User_loan;
use app\models\dev\User_loan_flows;
use yii\console\Controller;

/**
 * 借款筹款大于6小时，定时更改借款状态
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii setloanstatus > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe E:\www\yiyiyuan\yii setloanstatus
 */
// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class MashangjinrongController extends Controller {

//    private $url = 'http://weixinapitest.msxf.com/open/router/rest'; //测试地址
    private $url = 'https://open.msxf.com/router/rest'; //生产地址
    private $status = [
        '5' => 'U', //正在处理   （审核中）
        '6' => 'A', //通过       （审核通过，待确认合同）
        '9' => 'N', //签署       （合同已签署）
    ];
    private $paystatus = [
        '9' => 0,
        '6' => 1,
    ];
    public $key = array(
        'pubKey' => '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDQxYQXrrpAjo0KxpFXqvZNzRLl
n0WNdqxvHf0Gm92pEiHLi6oFN4ZePTmbbnpM6LsujLvKLjsmIsvvDZtlinxeWHc4
Ktd88h86EKmd7ZIFve8XuQcIz/QQbjDIW5cHT0CrNkIWEbD7t+72x3BLnqlj7WhJ
ZycFqs7YkhIMb6d56wIDAQAB
-----END PUBLIC KEY-----
',
        'priKey' => '-----BEGIN PRIVATE KEY-----
MIICeQIBADANBgkqhkiG9w0BAQEFAASCAmMwggJfAgEAAoGBANDFhBeuukCOjQrG
kVeq9k3NEuWfRY12rG8d/Qab3akSIcuLqgU3hl49OZtuekzouy6Mu8ouOyYiy+8N
m2WKfF5Ydzgq13zyHzoQqZ3tkgW97xe5BwjP9BBuMMhblwdPQKs2QhYRsPu37vbH
cEueqWPtaElnJwWqztiSEgxvp3nrAgMBAAECgYEAxein+wdhevdnzzJD7/aavVBN
vK3K8nrwpfmoto4liDQvI1UH3SAw2b9yj90gRS5O26cAEn7XBaTw7HJawNySXel2
cddR9qImeUCtznLvI/LHZ6Dk4XfJZbxeKv+DWVmJw794QTqy8h4G0qzLsqYu7Ep5
OOYl0XVqK0/wQsHZ/+kCQQD+NfmOD3/+8Ne6Ol5QMVlDkjgoLMoUJzCa7JFiOczj
akBkC/ySePYqm9yT5ihyHGHTg5lxRkV1UuOwAykJb7J1AkEA0j2rt+qF4HC3O8pj
IIicao8RlJEzszSN8zfKJouHGg9RPVJtADzq7uJNaNRlmTRPfhIuZ0YOo6mAqll5
8uIu3wJBAN0xl+i9ofDyHbQET8ZVekdqdoS2nEs24nsbd1FR2+7RqB+lsmq85+2h
Wygx72WDPrft3VkL/SoeKtIfndiBIXUCQQCzEawM/kgBHoAzAPLlZvYzHKCQtgwe
+whcvIwFwJnV7G35mRcWOMyxozbjruTKVO/QJZQ4ikc1xs28SnHAGj5pAkEAkt73
0m2kfYUeYTmPKfCns36ZvzYILW+7fk2gqjK2Egwd6QlfNwuLh4Ysh2B+u5kpS2Fq
9PnM8ZtDPRa91RRk6A==
-----END PRIVATE KEY-----
',
        'public11' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEApWvyaLxpPaZ2Rvi7CPK5FjlOLr6yIZiyOaW1Gt4BTPkWSiSFeJLNrLs77bhmcSqepK3ibQuTW33ymObQDqTsdR3tNH3NoF593MMRyBhvfqUCm7alnwqlxHFEnG7gyny+IKWRtGzAMLzwQc/cLcmQczyLAhnG+Tu8zlfXDjjL+7zLhFEiJXkpnIqzNL5MVIVxBMHAM5w1LEs+LomcMr0kEf/a7JLsipvFOapYw1UdqAQ/UtHJVlx7g0ktffYxeS8nFrBjDzDQcNAc7HR5QYzjemjZrrXG2a2UeOjUncsD3MiA6yQojF/xApxsq+R8jh9xoOHrpjZcEyWSEOqpDt5rBwIDAQAB',
    );
    private $desc = [
        '1' => '购买原材料',
        '2' => '进货',
        '3' => '购买设备',
        '4' => '购买家具或家电',
        '5' => '学习',
        '6' => '个人或家庭消费',
        '7' => '资金周转',
        '8' => '租房',
        '9' => '物流运输',
        '10' => '其他',
        '11' => '个人或家庭消费资金周转',
    ];
    private $purpose = [
        '1' => 'PL18',
        '2' => 'PL18',
        '3' => 'PL18',
        '4' => 'PL01',
        '5' => 'PL03',
        '6' => 'PL18',
        '7' => 'PL18',
        '8' => 'PL08',
        '9' => 'PL18',
        '10' => 'PL18',
        '11' => 'PL18',
    ];

    // 命令行入口文件
    public function actionIndex() {
        $start_time = date('Y-m-d 00:00:00', strtotime('-1 days'));
        $end_time = date('Y-m-d 00:00:00');
        $total = User_loan::find()->joinWith('user', true, 'LEFT JOIN')->where(['IN', User::tableName() . '.come_from', [51, 52, 65]])->andFilterWhere(['>=', User_loan::tableName() . '.start_date', $start_time])->andFilterWhere(['<', User_loan::tableName() . '.start_date', $end_time])->count();
        $limit = 1000;
        $succ_loan = [];
        $error_loan = [];
        $pages = ceil($total / $limit);
        for ($i = 0; $i < $pages; $i++) {
            $loan = User_loan::find()->joinWith('user', true, 'LEFT JOIN')->where(['IN', User::tableName() . '.come_from', [51, 52, 65]])->andFilterWhere(['>=', User_loan::tableName() . '.start_date', $start_time])->andFilterWhere(['<', User_loan::tableName() . '.start_date', $end_time])->offset($i * $limit)->limit($limit)->all();
            if (!empty($loan)) {
                foreach ($loan as $key => $value) {
                    $loan_info = User_loan::find()->where(['user_id' => $value->user_id, 'status' => 8])->one();
                    if (!empty($loan_info) && $loan_info->loan_id != $value->loan_id) {
                        continue;
                    } else {
                        $loan_notify = Loan_status_notify::find()->where(['loan_id' => $value->loan_id, 'result' => 1])->one();
                        if (!empty($loan_notify)) {
                            continue;
                        }
                        if (array_search($value->desc, $this->desc) === FALSE) {
                            continue;
                        }
                        $flows = User_loan_flows::find()->where(['loan_id' => $value->loan_id, 'loan_status' => 6])->one();
                        $condition = [
                            'partnerApplyNo' => $value->loan_no,
                            'userName' => $value->user->realname,
                            'userPhone' => $value->user->mobile,
                            'userIdCard' => $value->user->identity,
                            'loanTerm' => $value->days,
                            'loanTermUnit' => 1,
                            'loanMoney' => $value->amount * 100,
                            'loanPurpose' => $this->purpose[array_search($value->desc, $this->desc)],
                            'annualRate' => 0.1825,
                            'repayType' => 4,
                            'applyStatus' => $this->status[$value->status],
                            'applyTime' => date('YmdHis', strtotime($value->create_time)),
                            'approveTime' => date('YmdHis', strtotime($flows->create_time)),
                            'signTime' => date('YmdHis', strtotime($value->create_time)),
                            'payStatus' => $value->status == 6 ? 1 : 0,
                            'payMoney' => $value->amount * 100,
                            'payTime' => date('YmdHis', strtotime($value->withdraw_time)),
                        ];
                        $param = [
                            'method' => 'msxf.youqian.loan.status.notify',
                            'version' => '2.2',
                            'appid' => '377',
                            'timestamp' => date('Y-m-d H:i:s'),
                        ];
                        $result = $this->createRSA($condition);
                        $param['sign'] = $result['sign'];
                        $param['data'] = $result['data'];
                        $res = Http::interface_post_json($this->url, $param);
//                        print_r($res);
                        $data = json_decode($res);
//                        exit;
                        if ($data->code == 0) {
                            $result = $this->getContent($data->data);
                            $succ_loan[] = $value->loan_id;
                        } else {
                            $result = array(
                                'code' => $data->code,
                                'data' => $data->data,
                                'message' => $data->message
                            );
                            $error_loan[] = $value->loan_id;
                        }
                        $noticeModel = new Loan_status_notify();
                        $notice_condition = array(
                            'loan_id' => $value->loan_id,
                            'loan_no' => $value->loan_no,
                            'channel' => 1,
                            'status' => $value->status,
                            'result' => $data->code == 0 ? 1 : 0,
                            'data' => serialize($result),
                        );
                        $noticeModel->addNotify($notice_condition);
                    }
                }
            } else {
                break;
            }
        }
        if (count($error_loan) > 0) {
            Logger::errorLog(print_r(array($error_loan), true), 'mashang_senderror', 'crontab');
        }
        if (count($succ_loan) > 0) {
            Logger::errorLog(print_r(array($succ_loan), true), 'mashang_sendsucc', 'crontab');
        }
    }

    public function createRSA($result) {
        $str = json_encode($result);
        $key = $this->key;
        $rsa = new RSA();
// 签名的使用
        $sign = $rsa->sign($str, $key['priKey'], 'base64', OPENSSL_ALGO_SHA256);
//        echo $sign;exit;
        $result['sign'] = $sign;
//        $st_a = $rsa->verify($str, $sign, $key['pubKey'],OPENSSL_ALGO_SHA256);
// 加解密的使用
//        $str = '{"partnerApplyNo":"20160823103341144609935","userName":null,"userPhone":"15093560261","userIdCard":null,"loanTerm":28,"loanTermUnit":1,"loanMoney":50000,"loanPurpose":"dddddddd","annualRate":0.1825,"repayType":4,"applyStatus":9,"applyTime":"20160921190738","signTime":"20160921190738","payStatus":0,"payMoney":50000,"payTime":"20160928190738"}';
        $crypt = $rsa->encrypt128ByPublic($str, $key['public11']);
        $result['data'] = $crypt;
        return $result;
    }

    public function getContent($res) {
        $rsa = new RSA();
        $key = $this->key;
        $pstr = base64_encode($rsa->base64url_decode($res));
//        print_r($res);
        $data = $rsa->decrypt128ByPrivate($pstr, $key['priKey']);
        return $data;
    }

//    private function 
    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

}
