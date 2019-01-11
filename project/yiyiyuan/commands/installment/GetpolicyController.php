<?php
namespace app\commands\installment;

/**
 *  众安投保
 *  linux : sudo -u www /data/wwwroot/yiyiyuan/yii installment/policy
 *  windows D:phpStudy\php56n\php.exe D:WWW\yiyiyuanactive\yii installment/policy
 */
use app\commands\BaseController;
use app\commonapi\Apihttp;
use app\commonapi\Logger;
use app\models\news\Insure;
use app\models\news\RemitSuccessList;
use yii\helpers\ArrayHelper;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class GetpolicyController extends BaseController
{
    private $limit = 200;

    public function actionIndex()
    {
        $remitSuccessListModel = new RemitSuccessList();
        $start_date = date('Y-m-d H:i:00', strtotime('-60 minutes'));
        $end_date = date('Y-m-d H:i:00');
        $successNum = 0;

        $where = [
            'AND',
            ['>=', 'create_time', $start_date],
            ['<', 'create_time', $end_date],
            ['secure_status' => 'INIT'],
            ['remit_type' => 1]
        ];
        $sql = $remitSuccessListModel->find()->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);

        for ($i = 0; $i < $pages; $i++) {
            $successList = $sql->limit($this->limit)->all();
            if (empty($successList)) {
                break;
            }
            $ids = ArrayHelper::getColumn($successList, 'id');
            $remitSuccessListModel->updateAllLock($ids);
            foreach ($successList as $key => $value) {
                $result = $this->doPolicy($value);
                if (!$result) {
                    continue;
                }
                $successNum++;
            }
        }
        Logger::dayLog('installment/getpolicy', date('Y-m-d H:i:s'), '成功需处理总数：' . $total, '成功：' . $successNum);
        exit('success:' . $successNum . ';count:' . $total);
    }

    private function doPolicy($remitSuccessObj)
    {
        if (empty($remitSuccessObj) || !is_object($remitSuccessObj) || empty($remitSuccessObj->userloan) || empty($remitSuccessObj->user) || empty($remitSuccessObj->remit)) {
            return false;
        }
        $lockResult = $remitSuccessObj->updateLock();
        if (!$lockResult) {
            Logger::dayLog('installment/getpolicy', 'lock锁定失败', 'remit_success_list ID：' . $remitSuccessObj->id);
            return false;
        }
        $result = $this->postPolicy($remitSuccessObj);
        if (!$result) {
            $failResult = $remitSuccessObj->updateFail();
            if (!$failResult) {
                Logger::dayLog('installment/getpolicy', 'fail状态更新失败', 'remit_success_list ID：' . $remitSuccessObj->id);
            }
            return false;
        }
        $successResult = $remitSuccessObj->updateSuccess();
        if (!$successResult) {
            Logger::dayLog('installment/getpolicy', 'success状态更新失败', 'remit_success_list ID：' . $remitSuccessObj->id);
            return false;
        }
        return true;
    }

    private function postPolicy($remitSuccessObj)
    {
        $contacts = [
            'req_id' => $remitSuccessObj->order_id,//请求序号
            'premium' => $remitSuccessObj->userloan->withdraw_fee,//保费（砍头息）
            'identityid' => $remitSuccessObj->user->identity,//身份证
            'user_mobile' => $remitSuccessObj->user->mobile,//手机号
            'user_name' => $remitSuccessObj->user->realname,//姓名
            'benifitName' => $remitSuccessObj->user->realname,//受益人姓名
            'benifitCertiType' => 'I',//受益人证件类型
            'benifitCertiNo' => $remitSuccessObj->user->identity,//证件号码
            'policyDate' => $remitSuccessObj->userloan->days,//借款天数
            'fund' => $remitSuccessObj->remit->fund,//资金方
            'callbackurl' => Yii::$app->params['policy_notify_url'],//回调地址
        ];
        $api = new Apihttp();
        $result = $api->postPolicy($contacts);
        if ($result['res_code'] > 0) {
            Logger::dayLog('installment/getpolicy', '投保失败', 'remit_success_list ID：' . $remitSuccessObj->id, $contacts, $result);
            return false;
        }
        return true;
    }
}
