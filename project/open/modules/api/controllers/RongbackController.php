<?php
/**
 * 融360接口回调
 * @author zhangfei
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\RongController;
use app\models\JxlRequestModel;
use app\modules\api\common\rong\RongApi;
use app\models\JxlStat;
use Yii;


class RongbackController extends ApiController {

    private $backData;
    private $rapi;

    public function init() {
        //parent::init(); 千万不要执行父类的验证方法
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        $this->rapi = new RongApi($env);
        $this->backData = $this->post();
    }

    public function actionIndex() {
        
    }
    public function actionCallback(){
        $data = $this->backData;
        if (empty($data)) {
            Logger::dayLog('rongback/notify', 'receive fails !');
            echo 'fail';exit;
        }
        
        $account = isset($data['account'])?$data['account']:'';//区分详情回调还是报告回调
        Logger::dayLog('rongback/notify', $this->backData);
        if($account == ''){//详情回调
            if($data['status'] == 2){//抓取成功
                $request = new JxlRequestModel();
                $request = $request->getById($data['user_id']);
                $this->rapi->getUserdata($data['user_id']);//获取详情
                $bizData = array(
                    'phone' => $request->phone,
                    'name' => $request->name,
                    'idNumber' => $request->idcard,
                    'userId' => $request->id,
                    'outUniqueId' => time()
                );
                Logger::dayLog('rongback/notify',$bizData);
                $res = $this->rapi->getReport($bizData);//提取报告
                Logger::dayLog('rongback/notify',$res);
                echo 'success';exit;
            }else{
                echo 'fail';exit;
            }
        }else{//报告回调
            $state = $data['state']?$data['state']:'';
            if($state == 'report'){//成功生成报告
                $method = 'tianji.api.tianjireport.detail';
                $bizData = array(
                    'userId' => $data['userId'],
                    'outUniqueId' => $data['outUniqueId'],
                    'reportType' => 'html'

                );
                $res = $this->rapi->operatorSend($bizData, $method);
                if (!is_array($res) || $res['error'] != 200) {
                    Logger::dayLog('rongback/notify','callback报告异常');
                    echo 'fail';exit;
                }else {
                    $url = $this->rapi->writeLog($data['userId'], json_encode($res));//存储报告json
                    $oJxlStat = new JxlStat();
                    $request = new JxlRequestModel();
                    $request = $request->getById($data['userId']);
                    $postData = [
                        'aid' => $request->aid,
                        'requestid' => $request->id,
                        'name' => $request->name,
                        'idcard' => $request->idcard,
                        'phone' => $request->phone,
                        'website' => $request->website,
                        'is_valid' => 3,
                        'url' => $url,
                        'source' => $request->source
                    ];

                    //5 保存到DB中
                    $result = $oJxlStat->saveStat($postData);
                    if (!$result) {
                        return $this->dayLog('rong', 'saveStat', '保存失败', $postData);
                    }

                    $request->result_status = 1;
                    $request->process_code = 10008;
                    $request->save();
                    echo 'success';exit;
                }

            }else{
                Logger::dayLog('rongback/notify', 'Report fails !');
                echo 'fail';exit;
            }
        }
    }


}
