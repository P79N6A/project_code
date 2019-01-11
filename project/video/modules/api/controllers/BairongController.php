<?php
/**
 * 百融接口
 * @author lijin
 */
namespace app\modules\api\controllers;

use app\models\Bairong;
use app\modules\api\common\ApiController;
use app\modules\api\common\bairong\BairongApi;
use Yii;
use yii\helpers\ArrayHelper;
use app\common\Logger;

class BairongController extends ApiController {
    /**
     * 服务id号
     */
    protected $server_id = 21;

    /**
     * 百融api
     */
    private $oApi;

    public function init() {
        parent::init();
        $this->oApi = new BairongApi;
    }

    public function actionIndex() {
        //1 字段检测
        if (!isset($this->reqData['data'])) {
            return $this->resp(21001, "参数data必填");
        }
        $data = json_decode($this->reqData['data'], true);
        if (!is_array($data) || !is_array($data[0])) {
            return $this->resp(21002, "数据格式不合法");
        }
        Logger::dayLog('br/reqData', 'RES:'.$this->reqData['data']);
        $headerTitles = [
            // 'AccountChangeDer',
            'PayConsumptionDer'
        ];

        //2 获取已存在的数据,若不存在将保存到数据库中
        $models = $this->getDbData($data,$headerTitles);
        //3 调用接口
        $res = [
            'AccountChangeDer' => [],
            'PayConsumptionDer' => []
        ];
        foreach ($models as $key => $val) {
            if (!empty($val['notexist'])) {
                // 转换成百融api所需要的数据形式
                $res_map = $this->getApiData($val['notexist'],$key);
                // 更新状态和保存文件
                foreach ($models[$key]['notexist'] as $phone => $model) {
                    if (isset($res_map[$phone])) {
                        $result = $model->saveRspStatus($res_map[$phone]);
                    } else {
                        $result = $model->saveFailStatus();
                    }
                }
            }
            $all = array_merge($val['exist'], $val['notexist']);
            foreach ($all as $phone => $v) {
                $res[$key][$phone] = [
                    'url' => Yii::$app->request->hostInfo . $v['url'],
                    'status' => $v['status'],
                    'rsp_code' => $v['rsp_code'],
                    'rsp_status_text' => $v['rsp_status_text'],
                ];
            }
        }

        //4 返回响应结果   
        return $this->resp(0, [
            'total' => count($res),
            'data' => json_encode($res, JSON_UNESCAPED_UNICODE),
        ]);
    }
    /**
     * 获取存在的数据库，按照手机号区分
     * @param [] $data
     * @return []
     */
    private function getDbData($data,$apis) {
        // 转成kv形式, 以手机号为依据
        $map = ArrayHelper::index($data, 'phone');

        // status = 2 存在的
        $phones = array_keys($map);

        $model = [];
        foreach ($apis as $key => $api) {
            $model[$api] = $this->formatDbData($map,$phones,$api);
        }

        return $model;
        
    }

    private function formatDbData($map,$phones,$apis){
        $exist_map = [];
        $phones = isset($phones[0]) ? $phones[0] : null;
        $exists_models = (new Bairong)->getByPhones($phones,$apis);
        Logger::dayLog('br', 'payConsumptionDer:', $exists_models, 'phones:', $phones, 'apis:', $apis);
        if ($exists_models) {
            foreach ($exists_models as $model) {
                if (($model->apis == 'PayConsumptionDer' && $model->card != $map[$model->phone]['card'])) {
                    continue;
                }
                $exist_map[$model->phone] = $model;
                //从 $map 中删除同手机, 用以添加
                unset($map[$model->phone]);
            }
        }

        // status = 0 新加的
        $notexist_map = [];
        if ($apis == 'PayConsumptionDer') {
            $aid = $this->appData['id'];
            foreach ($map as $phone => $data) {
                $sdata = [
                    'aid' => $aid,
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'idcard' => $data['idcard'],
                    'card' => isset($data['card']) ? $data['card'] : '',
                    'apis' => $apis
                ];
                $model = new Bairong;
                $result = $model->saveData($sdata);
                if ($result) {
                    $notexist_map[$phone] = $model;
                }
            }
        } 
        return ['exist' => $exist_map, 'notexist' => $notexist_map];
    }



    /**
     * 获取接口中的数据
     * @param $data
     * @return []
     */
    private function getApiData($data,$headerTitle) {
        $headerTitle = $this->formatHeaderTitle($headerTitle);
        $targetList = $this->getTargetList($data);
        $res_list = $this->oApi->get($targetList,$headerTitle);
        $res_map = $this->formatRes($res_list);
        return $res_map;
    }


    private function formatHeaderTitle($headerTitle){
        $huaxiang = [
            "SpecialList_c", //信贷版特殊名单
            "ApplyLoan", // 多次申请核查
            "AccountChangeDer", // 收支等级小额信贷版评估
            "PayConsumption", // 支付消费评估
            "PayConsumptionDer" // 支付消费小额信贷版评估
        ];
        $headerTitles = [
            "haina" => [
                $headerTitle
            ]
        ];
        if (in_array($headerTitle, $huaxiang)) {
            $headerTitles = [
                'huaxiang' => [
                    $headerTitle
                ]
            ];
        }

        return $headerTitles;
        
    }



    // 转换成百融需要的形式
    private function getTargetList($data) {
        $targetList = [];
        foreach ($data as $v) {
            $arr = [
                "name" => $v['name'],
                "id" => $v['idcard'],
                "cell" => $v['phone'],
                "bank_id" => isset($v['card']) ? $v['card'] : ''
            ];
            $targetList[] = $arr;
        }
        return $targetList;
    }


    // 转换请求接口的格式，方便存入数据库
    private function getApis($headerTitle){
        if (is_array($headerTitle)) {
            $str = '';
            if(isset($headerTitle['huaxiang']) && is_array($headerTitle['huaxiang'])){
                foreach ($headerTitle['huaxiang'] as $key => $value) {
                    $str .= $value.',';
                }
            }
            if(isset($headerTitle['haina']) && is_array($headerTitle['haina'])){
                foreach ($headerTitle['haina'] as $key => $value) {
                    $str .= $value.',';
                }
            }
            return $str;
        }
        return false;
    }



    private function formatRes($res_list){
        $res = [];
        $res_map1 = [];
        $res_map2 = [];
        if (isset($res_list['huaxiang']) && is_array($res_list['huaxiang'])) {
            $res_map1 = ArrayHelper::index($res_list['huaxiang'], 'cell');
        }
        if (isset($res_list['haina']) && is_array($res_list['haina'])) {
            $res_map2 = ArrayHelper::index($res_list['haina'], 'cell');
        }
        if (!empty($res_map1)) {
            foreach ($res_map1 as $key => $val) {
                if (!empty($val)) {
                    foreach ($val as $k => $v) {
                        $res[$key][$k] = $v;
                    }
                }
            }
        }
        if (!empty($res_map2)) {
            foreach ($res_map2 as $key => $val) {
                if (!empty($val)) {
                    foreach ($val as $k => $v) {
                        $res[$key][$k] = $v;
                    }
                }
            }
        }
        return $res;

    }



}
