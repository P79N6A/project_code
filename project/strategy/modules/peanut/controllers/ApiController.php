<?php
namespace app\modules\peanut\controllers;

use app\common\ApiSign;
use app\common\Crypt3Des;
use app\common\Func;
use app\models\App;
use app\models\Log;
use app\models\WhiteIp;
use Yii;
use yii\web\Controller;
use app\common\Logger;
use yii\helpers\ArrayHelper;
/**
 * @desc api入口文件方法
 */
class ApiController extends Controller
{
    /**
     * api接口不需要 token 验证
     */
    public $enableCsrfValidation = false;

    public $postdata = [];
    /**
     * 服务端加密处理
     */
    protected $apiServerCrypt;

    /**
     * 初始化操作
     */
    public function init()
    {
        $datas = $this->post();
        Logger::dayLog('init', 'postdata', $datas);
        if (!is_array($datas) || !isset($datas['data']) || !isset($datas['_sign'])) {
            return $this->resp(3, '数据异常！');
        }
        $isVerify = (new ApiSign)->verifyData($datas['data'], $datas['_sign']);
        if (!$isVerify) {
            return $this->resp(4, '验签失败！');
        }
        $data = json_decode($datas['data'], true);
        
        $this->postdata = $data;
    }

    protected function post($name = null, $defaultValue = null)
    {
        return Yii::$app->request->post($name, $defaultValue);
    }

    /**
     * 响应结果
     * @param $res_code 0: 无错误（即成功), 1...错误码
     * @param $res_data 输出结果：错误信息或者数据格式
     */
    public function resp($res_code, $res_data, $return = false)
    {
        $returnData = array(
            'res_code' => $res_code,
            'res_data' => $res_data,
        );
        if ($return) {
            return $returnData;
        }
        $result = (new ApiSign)->signData($returnData);
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function getParam($name, $defaultValue = null)
    {
        $value = $this->get($name, $defaultValue);
        if (is_null($value)) {
            return $this->post($name, $defaultValue);
        } else {
            return $value;
        }
    }

    /**
     * getpost 返回get,post的数据，简单封装下
     */
    protected function get($name = null, $defaultValue = null)
    {
        return Yii::$app->request->get($name, $defaultValue);
    }

    protected function error($rsp_code,$rsp_msg,$postdata,$res_status)
    {
        switch ($res_status) {
            case 1:
                $result = 1;
                $message = '通过';
                break;
            case 2:
                $result = 2;
                $message = '人工';
                break;
            case 3:
                $result = 3;
                $message = '驳回';
                break;
            default:
                $result = 3;
                $message = '驳回';
                break;
        }
        $returnData = [
            'rsp_code' => $rsp_code,
            'rsp_msg' => $rsp_msg,
            'user_id' => isset($postdata['user_id']) ? $postdata['user_id'] : 0,
            'order_id' => isset($postdata['order_id']) ? $postdata['order_id'] : 0,
            'result' => $result,
            'message' => $message,
        ];
        echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function success($postdata,$res_arr,$rsp_msg='success',$rsp_code='0000')
    {
        $res_status = ArrayHelper::getValue($res_arr,'result',3);
        switch ($res_status) {
            case 1:
                $result = 1;
                $message = '通过';
                break;
            case 2:
                $result = 2;
                $message = '人工';
                break;
            case 3:
                $result = 3;
                $message = '驳回';
                break;
            default:
                $result = 3;
                $message = '驳回';
                break;
        }
        $returnData = [
            'rsp_code' => isset($rsp_code) ? $rsp_code : '0000',
            'rsp_msg' => isset($rsp_msg) ? $rsp_msg : '',
            'user_id' => isset($postdata['user_id']) ? $postdata['user_id'] : 0,
            'order_id' => isset($postdata['order_id']) ? $postdata['order_id'] : '',
            'result' => $result,
            'message' => $message,
        ];
        echo json_encode($returnData, JSON_UNESCAPED_UNICODE);
        exit;
    }

}
