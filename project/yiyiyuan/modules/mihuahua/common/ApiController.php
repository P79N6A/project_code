<?php
namespace app\modules\mihuahua\common;

use app\commonapi\ApiSign;
use app\commonapi\ErrorCode;
use app\commonapi\Logger;
use Yii;

class ApiController extends \app\common\BaseController
{
    public $data;

    public function init()
    {
        parent::init();
        $this->verifyData();
    }

    public function errorreback($code)
    {
        $errorCode = new ErrorCode();
        $array['rsp_code'] = $code;
        $array['rsp_msg'] = $errorCode->geterrorcode($code);
        return $array;
    }

    //验签
    private function verifyData()
    {
        $data = $this->post('data');
        $_sign = $this->post('_sign');
        Logger::dayLog('mihuahua/api', $this->data, $_sign);
        if (empty($data) || empty($_sign)) {
            $array = $this->errorreback('99994');
            exit(json_encode($array));
        }
        $apiSignModel = new ApiSign();
//        echo $apiSignModel->signData(json_decode($data, true))['_sign'];die;
        $verify = $apiSignModel->verifyData($data, $_sign);
        if (!$verify) {
            $array = $this->errorreback('99998');
            exit(json_encode($array));
        }
        $this->data = json_decode($data, true);
        if (empty($this->data)) {
            $array = $this->errorreback('99994');
            exit(json_encode($array));
        }
        return $verify;
    }

    /*
     * 校验数据参数
     */
    public function BeforeVerify($required = [], $httpParams = [])
    {
        $qRes = $this->checkRequired($required, $httpParams);
        if (!$qRes) {
            $array = $this->errorreback('99994');
            exit(json_encode($array));
        }
    }

    //检测接口必传参数
    private function checkRequired($required, $httpParams = [])
    {
        if (!is_array($httpParams) || !is_array($required)) {
            return false;
        }
        foreach ($required as $key => $val) {
            if (!isset($httpParams[$val]) || $httpParams[$val] == '') {
                return false;
            }
        }
        return TRUE;
    }

    //校验sign值
    private function verificationSign($array_notify)
    {
        $httpSign = isset($array_notify['sign']) ? $array_notify['sign'] : '';
        if (empty($httpSign) || empty($array_notify)) {
            return false;
        }
        unset($array_notify['sign']);
        ksort($array_notify);
        $signstr = http_build_query($array_notify);
        //系统分配的密匙
        $key = Yii::$app->params['app_key'];
        //签名
        $sign = md5($signstr . $key);
        if (isset($array_notify['echo_sign']) && $array_notify['echo_sign'] == 1) {
            echo $sign;
        }
        return $sign == $httpSign;
    }

    /**
     * 加密数据
     */
    public function encrySign($data)
    {
        if (empty($data) || !is_array($data)) {
            return '';
        }
        foreach ($data as &$val) {
            $val = strval($val);
        }
        ksort($data);
        $signstr = http_build_query($data);
        //系统分配的密匙
        $key = Yii::$app->params['app_key'];
        //签名
        $sign = md5($signstr . $key);
        return $sign;
    }

    /**
     * @abstract 通过post方式获取参数
     *
     * */
    public function getParamArr()
    {
        $param = array();
        //通过post方式接收参数
        $poststr = file_get_contents("php://input", 'r');
        $poststr = trim($poststr, '&');
        if (!empty($poststr)) {
            //拆分参数
            $paramarr = explode('&', $poststr);
            if (!empty($paramarr)) {
                foreach ($paramarr as $val) {
                    $strtemp = explode('=', $val);
                    $param[$strtemp[0]] = $strtemp[1];
                }
            }
        }
        $array_gets = $_GET;
        $array_notify = array_merge($array_gets, $param);
        if (isset($array_notify['s'])) {
            unset($array_notify['s']);
        }
        return $array_notify;
    }

    /**
     * @abstract 获取指定二维数组指定列的值，并组成字符串格式，以逗号分隔
     * @param $array 数据数组
     *          $v 数据里指定的值
     *          $sign 是否需要对值加单引号
     *
     * @return 以逗号连接连接在一起的字符串
     * */
    function ArrayToString($array, $v = 'id', $sign = false, $sep = ',')
    {
        $output = "";
        if (is_array($array)) {
            foreach ($array as $value) {
                if (is_array($value)) {
                    foreach ($value as $key => $val) {
                        if ($key == $v) {
                            if ($sign) {
                                $output .= "'" . $val . "'" . $sep;
                            } else {
                                $output .= $val . $sep;
                            }
                        }
                    }
                }
            }
        }
        if ($output) {
            $output = trim($output, $sep);
        }
        return $output;
    }
}