<?php
/**
 * 运营商聚合路由
 * @author zhangfei
 */

namespace app\modules\api\controllers;

use app\models\JxlRequestModel;
use app\models\JxlStat;
use app\modules\api\common\ApiController;
use app\modules\api\common\juxinli\Clientjxl;
//use app\modules\api\common\rong\RongApi;
use app\modules\api\common\yidun\ClientYd;
use app\common\Logger;

class GrabrouteController extends ApiController {
    private $env;
    private $jxlModel;
    protected $server_id = 8;

    public function init() {
        parent::init();
        $this->env = YII_ENV_DEV ? 'dev' : 'prod';
    }
    /**
     * 业务端请求入口
     * $source  1、2 代表聚信立通道   3：融360  4:蚁盾
     */
    public function actionServerpost() {
        $data = $this->reqData;
        //参数校验
        $idcard = $data['idcard'];
        $callbackurl = isset($data['callbackurl']) ? $data['callbackurl'] : '';
        if (!$idcard) {
            return $this->resp('25001', '身份证不能为空');
        }
        if (!$callbackurl) {
            return $this->resp('25002', '回调地址不能为空');
        }
        if (!$data['phone']) {
            return $this->resp('25003', '手机号不能为空');
        }
        if (!$data['password']) {
            return $this->resp('25004', '服务密码不能为空');
        }
        // website参数验证
        $data['website'] = isset($data['website']) ? $data['website'] : '';
        if (!in_array($data['website'], ['', 'jingdong'])) {
            return $this->resp('25005', '不支持此website');
        }
        // 若设置了website跳过运行商
        $data['skip_mobile'] = $data['website'] ? true : false;
        //2 检测年龄和区域
        $this->jxlModel = new JxlRequestModel();
        if (!$this->jxlModel->validBirth($idcard)) {
            return $this->resp('25006', '您的年龄不符合要求');
        }
        if (!$this->jxlModel->validArea($idcard)) {
            return $this->resp('25007', $this->jxlModel->errinfo);
        }


        //3 判断几个月时间内,使用历史数据
        $oJxlStat = new JxlStat;
        $oHistory = $oJxlStat->getHistoryNew($data['phone']);
        if ($oHistory) {
            return $this->resp(0, [
                'requestid' => $oHistory['requestid'],
                'phone' => $oHistory['phone'],
                'status' => 1,
                'from' => $data['from'],
                'source' => $oHistory['source'],
                'url' => '',
            ]);
        }
        //最近2分钟时相同的数据返回同样的结果
        // $data['source'] = isset($data['source']) ? $data['source'] : 2;

        $data['account'] = isset($data['account']) ? $data['account'] : $data['phone'];
        $oSameJxl = $this->jxlModel->getRecentSameNew($data['account'], $data['password']);
        if (!empty($oSameJxl)) {
            $data['user_id'] = $oSameJxl->id;
            $data['aid'] = $oSameJxl->aid;
            $data['source'] = $oSameJxl->source;
        } else {

            //source 权重
            $data['source'] = $this->getSourceWeight(); //聚信立、上数权重

            if ($data['source'] == 1 || $data['source'] == 2) {
                //聚信立走权重
                $data['source'] = $this->jxlModel->getAutoSourceNew($data['phone'], $data['source']);
            }
            //4 保存数据到db中
            $data['create_time'] = time();
            $data['aid'] = $this->appData['id'];
            $data['type'] = isset($data['type']) ? $data['type'] : '';
            $id = $this->jxlModel->saveJxlresquest($data); //保存数据
            if (!$id) {
                $this->resp('25026', '认证失败，请重新认证');
            }
            $data['user_id'] = $id;
        }
        $resData = $this->getAlleywayData($data); //结果返回业务端
        if (empty($resData)) {
            $this->resp(25027, '认证失败，请重新认证');
        }
        $this->resp($resData['res_code'], $resData['res_data']);
    }
    /**
     * 运营商各个通道请求
     * 融、聚信立
     * @return requestRes
     */
    private function getAlleywayData($data) {
        if (!is_array($data)) {
            return $this->resp('25026', '参数错误');
        }
        switch ($data['source']) {
        case '1':
        case '2':
            $crawler = new Clientjxl();
            break;
        case '3':
            $crawler = new RongApi($this->env);
            break;
        case '4':
            $crawler = new ClientYd($this->env);
            break;
        default:
            $crawler = null;
            break;
        }
        if (!$crawler) {
            return $this->resp('25025', 'source参数错误');
        }
        $returnData = $crawler->returnResdata($data);
        return $returnData;
    }

    //source  2 聚信立 4 上数  $int:聚信立跟上数的比重
    private function getSourceWeight() {
        $arr = [
            2 => 80,
            4 => 20,
        ];

        $chose = 2;
        $rand = rand(1, 100);
        $cur_total = 0;
        foreach ($arr as $source => $num) {
            $cur_total += $num;
            if ($rand <= $cur_total) {
                $chose = $source;
                break;
            }
        }
        return $chose;
    }
}