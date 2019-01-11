<?php
/**
 *  修复缺失邀请码
 *  linux : /data/wwwroot/yiyiyuan/yii disposable/repairinvitecode
 *  window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii disposable/repairinvitecode
 */
namespace app\commands\disposable;

use app\commonapi\Logger;
use app\models\news\User;
use app\commands\BaseController;
use Yii;

// 这个在计划任务里面避免超时或者内容不够
set_time_limit(0);
ini_set('memory_limit', '-1');

class RepairinvitecodeController extends BaseController
{
    private $limit = 200;

    public function actionIndex()
    {
        $countNum = 0;
        $successNum = 0;
        $where = [
            'AND',
            ['invite_code' => NULL],
            ['>', 'create_time', '2018-03-14 00:00:00']
        ];
        $sql = User::find()->where($where);
        $total = $sql->count();
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $data = $sql->limit($this->limit)->all();
            if (empty($data)) {
                break;
            }
            $count = count($data);
            $countNum += $count;
            $result = $this->setCode($data);
            if ($result) {
                $successNum += $result;
            }
        }
        Logger::dayLog('disposable/repairinvitecode', '需推送总数：' . $countNum, '成功：' . $successNum);
        exit('success:' . $successNum . ';count:' . $countNum);
    }

    private function setCode($userObj)
    {
        if (empty($userObj) && !is_object($userObj)) {
            return 0;
        }
        $num = 0;
        foreach ($userObj as $item) {
            if (!empty($item->invite_code)) {
                continue;
            }
            $condition['invite_code'] = $this->getCode();
            $result = $item->update_user($condition);
            if (empty($result)) {
                Logger::dayLog('disposable/repairinvitecode', '设置邀请码失败：' . $item->user_id);
                continue;
            }
            $num++;
        }
        return $num;
    }

    private function getCode()
    {
        $code = $this->makeCode(8, 1);
        $user = new User();
        $isone = $user->getUserinfoByInvitecode($code);
        if (isset($isone->user_id)) {
            return $this->getCode();
        } else {
            return $code;
        }
    }

    private function makeCode($length = 32, $mode = 0)
    {
        switch ($mode) {
            case '1':
                $str = '1234567890';
                break;
            case '2':
                $str = 'abcdefghijklmnopqrstuvwxyz';
                break;
            case '3':
                $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                $str = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $result = '';
        $l = strlen($str) - 1;
        $num = 0;

        for ($i = 0; $i < $length; $i++) {
            $num = rand(0, $l);
            $a = $str[$num];
            $result = $result . $a;
        }
        return $result;
    }
}
