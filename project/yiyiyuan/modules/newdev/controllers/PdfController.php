<?php

namespace app\modules\newdev\controllers;

use app\commonapi\ApiSign;
use app\commonapi\Common;
use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\dev\User;
use app\models\dev\User_bank;
use app\models\dev\Userwx;
use app\models\news\User_loan;
use Yii;
use yii\web\Controller;

class PdfController extends Controller {

    public $layout = 'pdf';
    public $enableCsrfValidation = false;

    public function actionSetcontract() {
        $this->getView()->title = '先花一亿元居间服务及借款协议（三方）';
        $loan_id = Yii::$app->request->get('loan_id');
        $this->layout = 'agreement';
        $data = [
            [
                'loan_id' => $loan_id
            ]
        ];
        $signData = (new ApiSign)->signData($data);
        $signData['_sign'] = base64_encode($signData['_sign']);
        //线上开放平台
        $url = "http://10.139.35.146:8085/api/match/queryinvest";
        //测试开放平台
//        $url = "http://182.92.80.211:8009/api/match/queryinvest";
        $result = Http::interface_post($url, $signData);
        $nameInfo = [
            ['realname' => '王玉华', 'identity' => '420500196003111842', 'money' => 0],
            ['realname' => '苏昌茂', 'identity' => '350426198210110016', 'money' => 0],
            ['realname' => '舒梅', 'identity' => '512301196910200266', 'money' => 0],
            ['realname' => '李晓霞', 'identity' => '13030219411010182x', 'money' => 0],
            ['realname' => '王芳', 'identity' => '440111196605200042', 'money' => 0],
            ['realname' => '吴簪花', 'identity' => '330522198008075727', 'money' => 0],
            ['realname' => '沙铮', 'identity' => '320622194702154236', 'money' => 0],
            ['realname' => '李幸幸', 'identity' => '410304198301231529', 'money' => 0],
            ['realname' => '王立伟', 'identity' => '330206195404171710', 'money' => 0],
            ['realname' => '周海', 'identity' => '320219197905156550', 'money' => 0],
            ['realname' => '周宁', 'identity' => '310110196108141613', 'money' => 0],
            ['realname' => '彭文琍', 'identity' => '43010419700111306X', 'money' => 0],
            ['realname' => '樊漫', 'identity' => '510107197601210022', 'money' => 0],
            ['realname' => '汪晓康', 'identity' => '330324197811244179', 'money' => 0],
            ['realname' => '卢瑞', 'identity' => '370602198401011815', 'money' => 0],
            ['realname' => '杨敏谦', 'identity' => '110102198012092396', 'money' => 0],
            ['realname' => '张锐峰', 'identity' => '152324198305220011', 'money' => 0],
            ['realname' => '刘庆文', 'identity' => '340222194511280039', 'money' => 0],
            ['realname' => '唐蝶琼', 'identity' => '330921198701292020', 'money' => 0],
            ['realname' => '陈帅', 'identity' => '410184199107011237', 'money' => 0],
            ['realname' => '王晓越', 'identity' => '510107198408132186', 'money' => 0],
            ['realname' => '于敏', 'identity' => '340603196005281022', 'money' => 0],
            ['realname' => '艾文君', 'identity' => '420503198405261825', 'money' => 0],
            ['realname' => '汪保庆', 'identity' => '371521197209020615', 'money' => 0],
            ['realname' => '王德正', 'identity' => '320303197411222014', 'money' => 0],
            ['realname' => '胡凛', 'identity' => '430921197201151319', 'money' => 0],
            ['realname' => '黄鸣婕', 'identity' => '310106198111271683', 'money' => 0],
            ['realname' => '赵秀娥', 'identity' => '152624196306024888', 'money' => 0],
            ['realname' => '顾秀琴', 'identity' => '310225195309161026', 'money' => 0],
            ['realname' => '殷正琦', 'identity' => '510203193910090820', 'money' => 0],
            ['realname' => '曾翠萍', 'identity' => '420800196602126022', 'money' => 0],
            ['realname' => '贾立人', 'identity' => '310109194805090415', 'money' => 0],
            ['realname' => '刘莹', 'identity' => '420106198407064443', 'money' => 0],
            ['realname' => '张海山', 'identity' => '11010819690612371X', 'money' => 0],
            ['realname' => '杨明芳', 'identity' => '41302619521005816X', 'money' => 0],
            ['realname' => '苗贵芬', 'identity' => '133001197306050044', 'money' => 0],
            ['realname' => '黎长胜', 'identity' => '440102197405194838', 'money' => 0],
            ['realname' => '生莉妍', 'identity' => '152301197309081523', 'money' => 0],
            ['realname' => '刘美瑜', 'identity' => '320402194806271043', 'money' => 0],
            ['realname' => '徐靖', 'identity' => '440301197010257018', 'money' => 0],
            ['realname' => '陈元林', 'identity' => '132440197510280635', 'money' => 0],
            ['realname' => '王义茂', 'identity' => '230103194108211615', 'money' => 0],
            ['realname' => '王朝举', 'identity' => '371428197010168013', 'money' => 0],
            ['realname' => '杨国民', 'identity' => '320104196604090418', 'money' => 0],
            ['realname' => '高羽丹', 'identity' => '350203197805234018', 'money' => 0],
            ['realname' => '马炳贞', 'identity' => '110106195208014287', 'money' => 0],
            ['realname' => '余汶骏', 'identity' => '510130197610220023', 'money' => 0],
            ['realname' => '李云飞', 'identity' => '21138119780130321x', 'money' => 0],
            ['realname' => '涂超强', 'identity' => '360426198303020011', 'money' => 0],
            ['realname' => '席艳', 'identity' => '510103197010014227', 'money' => 0]
        ];
        if ($result) {
            $investdata = json_decode($result, true);
            $datas = json_decode($investdata['data'], true);
            if ($datas && isset($datas['resData']) && $datas['resData']) {
//                $contributordata = json_decode($investdata['data'], true);
                $contributorarr = $datas['resData'][$loan_id];
            } else {
                Logger::errorLog($loan_id, 'makecon', 'crontab');
                $contributorarr = array($nameInfo[rand(0, 49)]);
            }
        } else {
            Logger::errorLog("$loan_id 生成失败" . "---" . print_r($result, true), 'contract', 'crontab');
            $contributorarr = array($nameInfo[rand(0, 49)]);
        }
        //$sql_loan = "select l.loan_id,l.days,l.end_date,u.realname,u.identity,w.nickname,b.* from ".User_loan::tableName()." as l,".User::tableName()." as u,".Userwx::tableName()." as w,".User_bank::tableName()." as b where l.user_id=u.user_id and u.user_id=b.user_id and u.openid=w.openid and l.loan_id=$loan_id";
        $sql_loan = "select l.*,u.realname,u.identity,w.nickname,b.bank_name,b.card from " . User_loan::tableName() . " as l left join " . User_bank::tableName() . " as b on b.id=l.bank_id left join " . User::tableName() . " as u on b.user_id=u.user_id left join " . Userwx::tableName() . " as w on u.openid=w.openid where l.loan_id=$loan_id";
        $loaninfo = Yii::$app->db->createCommand($sql_loan)->queryOne();
        //借款本金
        $loan_amount = $loaninfo['amount'];
        $loan = User_loan::findOne($loan_id);
        $endamount = $loan->getMoneyByCalculation();
        $loan_amount = number_format($loan_amount, 2, '.', '');
        $daxie_loan_amount = Common::get_amount($loan_amount);
        $daxie_endamount = Common::get_amount($endamount);
        $daxie_loan_amount_num = Common::get_amount_num($loan_amount);
        $daxie_endamount_num = Common::get_amount_num($endamount);
        $huankuandate = date('Y-m-d', strtotime($loan['end_date']));
        return $this->render('setcontract', [
                    'loaninfo' => $loaninfo,
                    'daxie_loan_amount' => $daxie_loan_amount,
                    'daxie_endamount' => $daxie_endamount,
                    'daxie_loan_amount_num' => $daxie_loan_amount_num,
                    'daxie_endamount_num' => $daxie_endamount_num,
                    'huankuandate' => $huankuandate,
                    'contributorarr' => $contributorarr
        ]);
    }

}
