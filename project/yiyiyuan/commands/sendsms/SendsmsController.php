<?php

namespace app\commands\sendsms;

/**
 *  运营短信
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii sendsms/sendsms
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii sendsms/sendsms
 */
use app\commands\BaseController;
use app\commonapi\Logger;
use app\models\haotian\SendUser;
use app\models\dev\Coupon_apply;
use app\models\news\Mobile;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class SendsmsController extends BaseController {
    /*
     * 将BI提供的数据导入yi_mobile表中
     * 每五分钟执行一次
     */

    public function actionIndex() {
        $errorNum   = $successNum = $ignoreNum  = 0;
        $limit      = 5000;
        $time       = date("Y-m-d H:i:00");
        $where      = [
            'AND',
            ['<=', 'send_time', $time],
            ['status' => 0],
            ['project' => 1],
        ];
        $sendInfos  = SendUser::find()->where($where)->indexBy('id')->orderBy('id asc')->limit($limit)->all();
        if (empty($sendInfos)) {
            exit(1);
        }
        $ids = array_keys($sendInfos);
        if (!is_array($ids) || empty($ids)) {
            exit(2);
        }
        //全部锁定
        $lockAll = (new SendUser())->lockAll($ids);
        //逐条锁定，并插入
        $sql     = 'INSERT INTO yi_mobile (`mobile`,`name`,`status`,`type`,`user_id`,`channel`,`send_time`,`sms_type`,`title`,`send_content`,`number`,`days`,`create_time`,`last_modify_time`) VALUES  ';
        foreach ($sendInfos as $key => $val) {
            $res = $val->lock();
            if (!$res) {
                Logger::dayLog('sendsms_sendsms_index', '乐观锁状态修改为1失败', $val);
            }

            //判断发送类型是否存在 若不存在 直接修改失败 跳过此条记录
            if (!isset($val->sendtype) || empty($val->sendtype) || empty($val->sendtype->send_content)) {
//                $fail = $val->fail();
                $ignoreNum++;
                continue;
            }
            //判断用户手机号 若不存在 直接修改失败 跳过此条记录
            if (!isset($val->user->mobile) || empty($val->user->mobile)) {
//                $fail = $val->fail();
                $ignoreNum++;
                continue;
            }
            $username = isset($val->user->realname) ? $val->user->realname : '未知';
            $smsType  = isset($val->sendtype->sms_type) && !empty($val->sendtype->sms_type) ? $val->sendtype->sms_type : '0';
            $title    = isset($val->sendtype->title) && !empty($val->sendtype->title) ? $val->sendtype->title : '';
            $snumber  = isset($val->sendtype->number) && !empty($val->sendtype->number) ? $val->sendtype->number : '0';
            $day      = isset($val->sendtype->days) && !empty($val->sendtype->days) ? $val->sendtype->days : '0';
            $sql      .= '("' . $val->user->mobile . '","' . $username . '","' . "0" . '","' . "13" . '","' . $val['user_id'] . '","' . $val['channel'] . '","' . $val['send_time'] . '","' . $smsType . '","' . $title . '","' . $val->sendtype->send_content . '","' . $snumber . '","' . $day . '",' . "NOW()" . ',' . "NOW()" . '),';
        }
        $sql = trim($sql, ',');

        $res = Yii::$app->db->createCommand($sql)->execute();
        if (!$res) {
            Logger::dayLog('sendsms_sendsms_index', '插入smssend表失败', $sendInfos);
        } else {
            $rows       = SendUser::updateAll(['status' => 2, 'last_modify_time' => date("Y-m-d H:i:s")], ['id' => $ids]);
            $successNum = count($sendInfos);
        }
        Logger::dayLog('sendsms_sendsms_index_result', '成功:', $successNum, '忽略：', $ignoreNum,date("Y-m-d H:i:s"));
        echo '成功:', $successNum, '忽略：', $ignoreNum,date("Y-m-d H:i:s");
    }

    /*
     * 将yi_mobile中数据执行发送
     * 每5分钟执行一次
     */

    public function actionDosend() {
        $errorNum   = $successNum = $ignoreNum  = 0;
        $limit      = 5000;
        $time       = date("Y-m-d H:i:00");
        $where      = [
            'AND',
            ['status' => 0],
        ];
        $sendInfos  = Mobile::find()->where($where)->indexBy('id')->orderBy('id asc')->limit($limit)->all();
        if (empty($sendInfos)) {
            exit(1);
        }
        $ids = array_keys($sendInfos);
        if (!is_array($ids) || empty($ids)) {
            exit(2);
        }
        //全部锁定
        $lockAll = (new Mobile())->lockAll($ids);

        $smsArr    = $couponArr = [];
        //逐条锁定，并插入
        foreach ($sendInfos as $key => $val) {
            $res = $val->lock();
            if (!$res) {
                Logger::dayLog('sendsms_sendsms_dosend', '乐观锁状态修改为1失败', $val);
            }
            //优惠券
            if (in_array($val['sms_type'], [2, 3])) {
                $couponArr[] = $val;
            }
//            //短信
            if (in_array($val['sms_type'], [1, 2])) {
                $smsArr[] = $val;
            }
        }

        if (!empty($couponArr)) {
            $total = count($couponArr);
            foreach ($couponArr as $k => $v) {
                $res = $this->insertCoupon($v, $total);
            }
        }
        if (!empty($smsArr)) {
            $res = $this->insertSmsSend($smsArr);
        }
    }

    //短信类型 直接插入smssend表
    private function insertSmsSend($data) {
        $sql  = "INSERT INTO yi_sms_send (`mobile`,`content`,`sms_type`,`status`,`channel`,`send_time`,`create_time`) VALUES  ";
        $str  = '';
        $pids = [];
        foreach ($data as $key => $val) {
            $pids[] = $val['id'];
            $sql    .= '("' . $val['mobile'] . '","' . $val['send_content'] . '","' . $val['type'] . '","' . "0" . '","' . $val['channel'] . '","' . $val['send_time'] . '",' . "NOW()" . '),';
        }
        $sql = trim($sql, ',');
        $res = Yii::$app->db->createCommand($sql)->execute();
        if (!$res) {
            Logger::dayLog('sendsms_sendsms_insertsmssend', '插入smssend表失败', $data);
        } else {
            $rows = Mobile::updateAll(['status' => 2, 'last_modify_time' => date("Y-m-d H:i:s")], ['id' => $pids]);
        }
        return $res;
    }

    //短信和优惠券类型
    private function insertCoupon($data, $number) {
        $res = (new Coupon_apply())->sendcoupon($data['user_id'], $data['title'], 1, $data['days'], $data['number'], $number);
        if (!$res) {
            Logger::dayLog('sendsms_sendsms_insertcoupon', '插入优惠券表失败', $data);
        } else {
            $data->coouponSuccess();
        }
        return $res;
    }

}
