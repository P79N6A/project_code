<?php
/**
 * 运营商聚合路由
 * @author zhangfei
 */

namespace app\modules\api\controllers;

use Yii;
use app\models\JxlRequestModel;
use app\models\JxlStat;
use app\modules\api\common\ApiController;
use app\modules\api\common\juxinli\Clientjxl714;
use app\modules\api\common\rong\RongApi;
use app\modules\api\common\yidun\ClientYd714;
use app\common\Logger;

class OperatorController extends ApiController {
    private $env;
    private $jxlModel;
    protected $server_id = 8;

    public function init() {
        parent::init();
        $this->env = YII_ENV_DEV ? 'dev' : 'prod';
    }
    /**
     * 业务端请求入口
     * $source  1、2 代表聚信立通道   3：融360  4:蚁盾 6:数据魔盒
     */
    public function actionReq(){
        $data = $this->reqData;
        $sourceList = [ 4, 6 ]; //目前2家运营商
        Logger::dayLog('operator', $data);
        //参数校验
        $idcard = $data['idcard'];
        $callbackurl = isset($data['callbackurl']) ? $data['callbackurl'] : '';
        $source = (int)isset($data['source']) ? $data['source'] : 0;
        if (!$idcard) {
            return $this->resp('25001', '身份证不能为空');
        }
        if (!$callbackurl) {
            return $this->resp('25002', '回调地址不能为空');
        }
        if (!$data['phone']) {
            return $this->resp('25003', '手机号不能为空');
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
        $oHistory = $oJxlStat->getHistoryNew($data['phone'],'',$this->appData['id']);
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

        $data['account'] = isset($data['account']) ? $data['account'] : $data['phone'];
        $oSameJxl = $this->jxlModel->getRecentSameNew($data['account'], $data['password']);
        if (!empty($oSameJxl)) {
            $data['user_id'] = $oSameJxl->id;
            $data['aid'] = $oSameJxl->aid;
            $data['source'] = $oSameJxl->source;
        } else {
            if(in_array( $source, $sourceList )  ){ //走风控决策结果 
                $data['source'] = $source;
            }else{
                $data['source'] = $this->getSourceWeight(); //聚信立、上数权重
            }

//            if ($data['source'] == 1 || $data['source'] == 2) {
//                //聚信立走权重
//                $data['source'] = $this->jxlModel->getAutoSourceNew($data['phone'], $data['source']);
//            }
            //4 保存数据到db中
            $data['create_time'] = time();
            $data['aid'] = $this->appData['id'];
            //$data['aid'] = 1;
            $data['type'] = isset($data['type']) ? $data['type'] : '';
            $id = $this->jxlModel->saveJxlresquest($data); //保存数据
            if (!$id) {
                $this->resp('25026', '请求数据保存失败');
            }
            $data['user_id'] = $id;
        }

    //返回输入服务密码页面
        $crawlers = new Clientjxl714();
        $requestid = $crawlers->opEncrypt($data['user_id']);//加密
        $url = Yii::$app->request->hostInfo.'/grab/register?id='.urlencode($requestid);
        $rdata['requestid'] = $data['user_id'];
        $rdata['status'] = 0;//初始
        $rdata['url'] = $url;
//         var_dump($rdata);die;
        return $this->resp(0, $rdata);

    }


    //source  2 聚信立 4 上数 6数据通  $int:聚信立跟上数的比重
    private function getSourceWeight() {
        $arr = [
            4 => 50,
            6 => 50
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
