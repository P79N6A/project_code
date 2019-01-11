<?php
namespace app\commands;
use app\models\jiufu\JFRemit;
use Yii;

/**
 * 玖富监控邮件
 */
class JiufuMonitorController extends BaseController {
    /**
     * windows d:\xampp\php\php.exe D:\www\open\yii jiufu-monitor  sendMail
     * linux /data/wwwroot/open/yii jiufu-monitor  sendMail
     */
    public function sendMail($begin_time = null) {
        $end_time = date('Y-m-d H:i:s', time() - 3600 * 3);
        if (!$begin_time) {
            $begin_time = date('Y-m-d H:i:s', time() - 2 * 86400);
        }

        //2 获取统计数据
        $notSubmits = $this->notSubmits($begin_time, $end_time);
        $locks = $this->locks($begin_time, $end_time);
        $queryLimits = $this->queryLimits($begin_time, $end_time);

        if (empty($notSubmits) && empty($locks) && empty($queryLimits)) {
            return false;
        }

        //3 发送邮件
        $remit_status = (new JFRemit)->getStatus();
        $rsp_status = include \Yii::$app->basePath . '/modules/api/common/jiufu/config/jiufu_status.php';
        $mail = Yii::$app->mailer->compose('jiufumonitor/sendmail', [
            'begin_time' => $begin_time,
            'end_time' => $end_time,
            'notSubmits' => $notSubmits,
            'locks' => $locks,
            'queryLimits' => $queryLimits,
            'remit_status' => $remit_status,
            'rsp_status' => $rsp_status,
        ]);
        $mail->setTo([
            'hanyongguo@ihsmf.com',
            'gaolian@ihsmf.com',
            'lijin@ihsmf.com',
            'luozhihe@ihsmf.com',
            'wangchao@ihsmf.com',
            'songxiao@ihsmf.com',
        ]);
        $mail->setSubject("{$begin_time} / {$end_time}玖富出款监控");
        if ($mail->send()) {
            echo "success";
        } else {
            echo "fail";
        }
    }
    /**
     * 未提交的 remit_status (0,1)
     */
    private function notSubmits($begin_time, $end_time) {
        //1 查询条件
        $where = ['AND',
            ['>=', 'create_time', $begin_time],
            ['<', 'create_time', $end_time],
            ['remit_status' => [JFRemit::STATUS_INIT, JFRemit::STATUS_REQING_REMIT]],
        ];
        $notSubmits = JFRemit::find()->where($where)->limit(500)->all();
        return $notSubmits;
    }

    /**
     * 查询锁定状态 remit_status (4,8)
     */
    private function locks($begin_time, $end_time) {
        //1 查询条件
        $where = ['AND',
            ['>=', 'query_time', $begin_time],
            ['<', 'query_time', $end_time],
            ['remit_status' => [JFRemit::STATUS_REQING_QUERY, JFRemit::STATUS_REQING_PAY]],
        ];
        $locks = JFRemit::find()->where($where)->limit(500)->all();
        return $locks;
    }
    /**
     * 查询次数超限的订单
     */
    private function queryLimits($begin_time, $end_time) {
        $where = ['AND',
            ['>=', 'query_time', $begin_time],
            ['<', 'query_time', $end_time],
            ['remit_status' => [JFRemit::STATUS_QUERY_MAX]],
        ];
        $queryLimits = JFRemit::find()->where($where)->limit(500)->all();
        return $queryLimits;
    }
}