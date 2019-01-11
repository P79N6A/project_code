<?php

/**
 * 给逾期用户和投资者发送推送消息
 */
/**
 * 1 注意这里引入文件必须是绝对路径。相对路径容易出错
 * 2 使用 
 *   linux : /data/wwwroot/yiyiyuan/yii getloanover > /data/wwwroot/yiyiyuan/log/income.log (修改根目录下yii文件的php的解析路径)
 *   window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii income
 */

namespace app\commands;

use yii;
//use app\models\dev\ApiSms;
use app\commonapi\ApiSms;
use app\models\dev\User;
use app\models\news\GoodsBill;
use app\models\news\SmsSend;
use app\models\news\User_id_temp;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

/**
 * 这个包含地址需要根据个人文件路径进行设置绝对路径
 */
class SendsmstouserController extends Controller {

    // 命令行入口文件
    public function actionIndex() {
        $condition = [
            'status' => 0,
        ];

        $total = User_id_temp::find()->where($condition)->count();

        $limit = 1000;
        $pages = ceil($total / $limit);
        $this->log("\n" . date('Y-m-d H:i:s') . "......................");
        $this->log("\n共{$total}条数据:每次处理{$limit},需要要处理{$pages}次\n");

        for ($i = 0; $i < $pages; $i++) {
            $user_loan = User_id_temp::find()->where($condition)->limit($limit)->all();
            if (empty($user_loan)) {
                break;
            }
            $userIds = ArrayHelper::getColumn($user_loan, 'user_id');
            $model   = new User_id_temp();
            $model->setAllStatus($userIds, 1);
            foreach ($user_loan as $key => $value) {
                $userinfo = User::findOne($value->user_id);
                $mobile   = $userinfo->mobile;
                $content  = '尊敬的用户，先花一亿元已开放担保借款业务，额度秒提500，下款转瞬即到，速来体验，退订回TD';
                $apiModel = new ApiSms();
                $result   = $apiModel->choiceChannel($mobile, $content, 40, '', 1);
                if ($result) {
                    $model->setStauts($value->user_id, 2);
                } else {
                    $model->setStauts($value->user_id, 3);
                }
            }
        }
    }

    // 纪录日志
    private function log($message) {
        echo $message . "\n";
    }

    /**
     * 发送即将逾期订单  
     * 根据执行的时间判断发送条件
     */
    public function actionSendupcoming() {
        $hour    = date('H');
        $where   = ["AND"];
        $where[] = ['bill_status' => 9];
        if ($hour < 12) {         //上午查找近3天到期的数据
            $middle = time() + 86400 * 3;
        } else {                  //下午查找近1天到期的数据
            $middle = time() + 86400;
        }

        $where[]  = ['>', 'end_time', date('Y-m-d')];
        $where[]  = ['<=', 'end_time', date('Y-m-d', $middle)];
        //查出将要发送的数据
        $addData  = [];
        $err_num  = $suc_num  = 0;
        $res_data = (new GoodsBill())->getPostData($where);
        foreach ($res_data as $val) {
            $content = $this->getContent($val);
            if (empty($content)) {
                continue;
            }
            $addData['mobile']    = $val->user['mobile'];
            $addData['content']   = $content;
            $addData['sms_type']  = 4;
            $addData['status']    = 0;
            $addData['channel']   = Yii::$app->params['sms_channel'];
            $addData['send_time'] = date('Y-m-d H:i:s');
            $sms_model            = new SmsSend();
            $res                  = $sms_model->addSmsSend($addData);
            if ($res) {
                $suc_num++;
            } else {
                $err_num++;
            }
        }
        $this->log("本次成功发送短信" . $suc_num . "条,失败" . $err_num . "条");
    }

    public function getContent($value) {
        $leftMoney = bcsub($value->current_amount, $value->repay_amount, 2);
        $nowtime   = strtotime(date('Y-m-d'));
        $leftdays  = ceil((strtotime($value['end_time']) - $nowtime) / 86400);
        if ($leftdays == 1) {
            $content = $value->user->realname . "先生/女士，今天是您在先花一亿元借款的最后还款日，还款金额" . $leftMoney . "元，请您按时前往先花一亿元微信公众号或先花一亿元APP进行还 款。请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        } else if ($leftdays == 2) {
            $content = $value->user->realname . "先生/女士，距您于" . $value->start_time . "产生的借款还有一天到最后还款日，还款金额" . $leftMoney . "元，请您按时前往先花一亿元微信公众号或先花一亿元APP进行还 款。请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        } else if ($leftdays == 3) {
            $content = $value->user->realname . "先生/女士，距您于" . $value->start_time . "产生的借款还有两天到最后还款日，还款金额" . $leftMoney . "元，请您按时前往先花一亿元微信公众号或先花一亿元APP进行还 款。请注意：先花一亿元从未授权任何第三方向你收取任何费用！";
        } else {
            $content = '';
        }
        return $content;
    }

}
