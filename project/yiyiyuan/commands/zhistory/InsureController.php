<?php
/**
 *  导流投保(每天执行一次)
 *  linux : /data/wwwroot/yiyiyuan/yii insure
 *  window : d:\xampp\php\php.exe D:\www\yiyiyuan\yii insure
 */
namespace app\commands;

use app\commonapi\Http;
use app\commonapi\Logger;
use app\models\news\Guide;
use app\models\news\User;
use yii\console\Controller;
use yii\helpers\ArrayHelper;

class InsureController extends Controller
{
    private $limit = 300;
    private $channelCode = 'out_xianhuahua';
    private $productCode = 'loop_product';
    private $key = '274cf7f531b8570e15514a5df267eafa';
    private $url = 'http://www.newtank.cn/newtank/thp/insure.do';

    public function actionIndex()
    {
        $successNum = 0;
        $where = ['status' => 0];
        //@todo 条数限制。
        $total = Guide::find()->where($where)->count();
//        $total = Guide::find()->where($where)->limit(2000)->all();
//        $total = count($total);
        $pages = ceil($total / $this->limit);
        for ($i = 0; $i < $pages; $i++) {
            $guide = Guide::find()->where($where)->limit($this->limit)->all();
            $ids = ArrayHelper::getColumn($guide, 'id');
            Guide::updateAll(['status' => 3], ['status' => 0, 'id' => $ids]);
            foreach ($guide as $item) {
                $info = $this->sendInsure($item);
                if ($info) {
                    $successNum++;
                }
            }
        }
        Logger::dayLog('insure', '新旦投保', '总数 -> ' . $total, '发送成功数 -> ' . $successNum);
        //@todo 条数限制输出
//        exit('total:' . $total . ',successNum:' . $successNum);
    }

    //推送数据
    private function sendInsure($insureInfo)
    {
        $result = $insureInfo->addLock();
        if (!$result) {
            return false;
        }
        //投保年龄范围 25<=年龄<=50
        $ageStatue = $this->chkAgeStatus(substr($insureInfo->identity, 6, 8));
        if (!$ageStatue) {
            $insureInfo->updateNotAccord();
            return false;
        }
        //拉黑用户
        $userInfo = User::findOne($insureInfo->user_id);
        if ($userInfo && $userInfo->status != 5) {
            $insureInfo->updateNotAccord();
            return false;
        }
        $data = [
            'channelCode' => $this->channelCode,
            'productCode' => $this->productCode,
            'mobile' => $insureInfo->mobile,
            'name' => $insureInfo->realname,
            'idCard' => $insureInfo->identity,
            'birth' => $this->getBirth($insureInfo->identity),
            'sex' => $this->getSex($insureInfo->identity),
            'sign' => $this->getSign($insureInfo->mobile),
        ];
        $htt_resutl = Http::post_json($this->url, json_encode($data));
        Logger::dayLog('insure/resutl', 'insureId:' . $insureInfo->id, '返回值：' . print_r($htt_resutl, true));
        $res = $htt_resutl[1];
        if (!empty($res)) {
            return $this->updateInsure($insureInfo, $res);
        } else {
            return false;
        }
    }

    //更新结果
    private function updateInsure($insureInfo, $res)
    {
        if (empty($insureInfo) || empty($res)) return false;
        $res = json_decode($res, true);
        $data = [
            'status' => 1,
            'uid' => $res['uid'],
            'return_status' => $res['status'],
            'return_message' => $res['message']
        ];
        if ($res['status'] != 0) {
            $data['status'] = 2;
        }
        return $insureInfo->updateInsure($data);
    }

    //校验用户年龄规则
    private function chkAgeStatus($birthday)
    {
        $age = strtotime($birthday);
        if ($age === false) {
            return false;
        }
        list($y1, $m1, $d1) = explode("-", date("Y-m-d", $age));
        $now = strtotime("now");
        list($y2, $m2, $d2) = explode("-", date("Y-m-d", $now));
        $age = $y2 - $y1;
        if ((int)($m2 . $d2) < (int)($m1 . $d1)) {
            $age -= 1;
        }
        if ($age >= 25 && $age <= 50) {
            return true;
        } else {
            return false;
        }
    }

    //获取签名
    private function getSign($mobile)
    {
        return md5($this->channelCode . $this->key . $mobile);
    }

    //获取性别 0女 1男
    private function getSex($cardno)
    {
        return substr($cardno, (strlen($cardno) == 15 ? -2 : -1), 1) % 2 ? '1' : '0';
    }

    //获取出生年月日
    private function getBirth($cardno)
    {
        $str = strlen($cardno) == 15 ? ('19' . substr($cardno, 6, 6)) : substr($cardno, 6, 8);
        return date("Y-m-d",strtotime($str));
    }
}