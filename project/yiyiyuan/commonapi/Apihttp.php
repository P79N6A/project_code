<?php

namespace app\commonapi;

use app\common\ApiClientCrypt;
use app\commonapi\Logger;
use app\common\Curl;
use ReflectionClass;
use Yii;

class Apihttp {

    /**
     * 接口请求方式
     * @param unknown $url
     * @param unknown $data
     * @return mixed
     */
    public function httpPost($url, $data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_URL, $url);
        $ret = curl_exec($ch);

        curl_close($ch);
        return $ret;
    }

    public function httpGet($url) {//get https的内容
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //不输出内容
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @abstract 银行卡号验证
     * @param [cardno]
     * @return [true,false]
     * */
    public function bankValid($params) {
        $param_map = ['cardno'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "yeepaytzt/bankcardcheck";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['cardno'] . "--" . print_r($result, true), 'bankValid');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @author yangjinlong
     * @abstract 银行卡快捷验证
     * @param [username,idcard,cardno,phone]
     * @return [true,false]
     * */
    public function bankInfoValids($params) {
        $param_map = ['username', 'idcard', 'cardno', 'phone', 'identityid'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "bankauth/bankauthroute/bind";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sents($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['cardno'] . "--" . print_r($result, true), 'bankInfoValid');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_data' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    public function weiShenma($params) {
        $url = "wsmremit";
        $openApi = new ApiClientCrypt;
        Logger::errorLog($params['sjh'] . "--" . print_r($params, true), 'weishenma_send');
        $res = $openApi->sent($url, $params, 2);
        Logger::errorLog($params['sjh'] . "--" . print_r($res, true), 'weishenma');
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['sjh'] . "--" . print_r($result, true), 'weishenma');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000'];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * 7-14是否存在借款接口
     * @param $params
     * @return array|bool
     */
    public function havingLoan($params) {
        $param_map = ['identity'];
        if (!$this->validParamMap($param_map, $params)) {
            return false;
        }
        $url = "userloan/index";
        $openApi = new ApiClientCrypt;
        $ret = $openApi->sent_loan($url, $params);
        $result = json_decode($ret, true);
        Logger::errorLog($params['identity'] . "--" . print_r($result, true), 'xhh_loan');
        if (empty($result) || $result['req_code'] == '0000') {
            return true;
        }
        return false;
    }

    /**
     * @abstract 银行卡快捷验证
     * @param [username,idcard,cardno,phone]
     * @return [true,false]
     * */
    public function bankInfoValid($params) {
        $param_map = ['username', 'idcard', 'cardno', 'phone'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "bankvalid";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);

        Logger::errorLog($params['cardno'] . "--" . print_r($result, true), 'bankInfoValid');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000'];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 银行卡快捷验证Rong360
     * @param [username,idcard,cardno,phone]
     * @return [true,false]
     * */
    public function bankInfoValidRong($params) {
        $param_map = ['identityid', 'username', 'idcard', 'cardno', 'phone'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "bankauthroute/bind";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sentRong($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['cardno'] . "--" . print_r($result, true), 'bankInfoValid');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000'];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 身份验证
     * @param [username,idcard,partner_trade_no]
     * @return [true,false]
     * */
    public function idValid($params) {
        $param_map = ['name', 'idcard', 'partner_trade_no'];
        $params['partner_trade_no'] = date('YmdHis') . rand(1000, 9999);
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "idcard";
        $ret = ['res_code' => '0000'];
        return $ret;
// 		$openApi = new ApiClientCrypt;
// 		$res = $openApi->sent($url, $params);
// 		$result = $openApi->parseResponse($res);
// 		Logger::errorLog(substr($params['idcard'],-4)."--".print_r($result, true), 'idValid');
// 		if( $result['res_code'] === 0 ){
// 			if( $result['res_data']['status'] == 2 ){
// 				$ret = ['res_code'=>'0000'];
// 			}else{
// 				$ret = ['res_code'=>'10001','res_msg'=>$result['res_data']];
// 			}
// 		}else{
// 			$ret = ['res_code'=>$result['res_code'],'res_msg'=>$result['res_data']];
// 		}
// 		return $ret;
    }

    /**
     * @abstract人脸识别验证
     * @param [identity,identity_url]
     * @return [true,false]
     */
    public function faceValid($params) {
        $param_map = ['identity', 'identity_url', 'pic_identity'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "facevalid";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['identity'] . "--" . print_r($result, true), 'faceValid');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * 百融接口
     */
    public function identityByBaiRong($params) {
        $targetList = json_encode($params, JSON_UNESCAPED_UNICODE);

        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('/data/bairong', ['data' => $targetList,], 2);

        $result = $openApi->parseResponse($res);
        Logger::errorLog(date('Y-m-d H:i:s') . "--" . print_r($result, true), 'identityByBaiRong');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * 手机流量充值
     */
    public function mobileRecharge($params) {
        $param_map = ['mobile', 'package', 'type'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "sms/vikemobilerecharge";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['mobile'] . "--" . print_r($result, true), 'mobileRecharge');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 同盾注册事件
     * @param [username,idcard,cardno,phone]
     * @return [true,false]
     * */
    public function riskRegValid($params) {
        $params_map = ['name',
            'idno',
            'mobile',
            'birth_year',
            'school',
            'edu',
            'school_year',
            'industry',
            'position',
            'company',
            'seq_id',
            'service_type',
            'event_type',
            'token_id',
            'version'
        ];
    }

    /**
     * @abstract 云信短信发送
     * @param[receive_mobile,content,sms_type]
     * @return[true,false]
     */
    public function sendSmsByYunxin($params) {
        $param_map = ['mobile', 'content', 'sms_type', 'id'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "sms/sendyunxin";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['mobile'] . "--" . print_r($result, true), 'sendSmsByYunxin');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 百分百短信通道
     * @param[receive_mobile,content,sms_type]
     * @return[true,false]
     */
    public function sendSmsByWxt100($params) {
        $param_map = ['mobile', 'content', 'sms_type', 'id'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "sms/sendwxt100";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        //print_r($result);exit;
        Logger::errorLog($params['mobile'] . "--" . print_r($result, true), 'sendSmsByWxt100');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     *
     * @abstract 安捷信短信通道
     * @param[aid,receive_mobile,content,sms_type]
     * @return[true,false]
     */
    public function sendSmsByAnjiexin($params) {
        $param_map = ['mobile', 'content', 'sms_type', 'aid'];
        if (!$this->validParamMap($param_map, $params))
            return false;
        $url = "sms/sendanjiexintouser";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        //print_r($result);exit;
        Logger::errorLog($params['mobile'] . "--" . print_r($result, true), 'sendSmsByAnjiexin');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     *
     * @abstract 创蓝短信通道
     * @param[aid,receive_mobile,content,sms_type]
     * @return[true,false]
     */
    public function sendSmsByChuanglan($params) {
//     	$param_map = ['mobile','content','sms_type','aid'];
//     	if( !$this->validParamMap($param_map, $params) )
//     		return false;
        $url = "sms/sendchuanglansmstouser";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        //print_r($result);exit;
        Logger::errorLog($params['mobile'] . "--" . print_r($result, true), 'sendSmsByChuanglan');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 同盾借款事件
     * @param [name,mobile,idno,service_type,event_type,birth_year,school,edu,school_year,industry,position,company,seq_id,token_id,version]
     * @return [true,false]
     * */
    public function riskLoanValid($params) {
        $param_map = ['account_name',
            'mobile',
            'id_number',
            'seq_id',
            'ip_address',
            'type',
            'token_id',
            'ext_school',
            'ext_diploma',
            'ext_start_year',
            'card_number',
            'pay_amount',
            'event_occur_time',
            'ext_birth_year',
            'organization',
            'ext_position'
        ];

        $url = "fraudmetrix";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = json_decode($res);
        Logger::errorLog($params['id_number'] . "--" . print_r($result, true), 'riskLoanValid');
        return $result;
    }

    /**
     * @abstract 请求绑定银行卡(易宝)
     * @param[requestid,identityid,cardno,idcardtype,idcardno,username,phone,userip]
     * @return[true,false]
     */
    public function invokebindbankcard($params) {
        $param_map = ['requestid',
            'identityid',
            'cardno',
            'idcardtype',
            'idcardno',
            'username',
            'phone',
            'userip'
        ];

        $url = "yeepaytzt/invokebindbankcard";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['idcardno'] . "--" . print_r($result, true), 'invokebindbankcard');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 确认绑卡(易宝)
     * @param[requestid,validatecode]
     * @return[true,false]
     */
    public function confirmbindbankcard($params) {
        $param_map = ['requestid', 'validatecode'];

        $url = "yeepaytzt/confirmbindbankcard";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['requestid'] . "--" . print_r($result, true), 'confirmbindbankcard');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 代扣(易宝)
     * @param[requestid,validatecode]
     * @return[true,false]
     */
    public function directbindpay($params) {
        $param_map = ['orderid',
            'transtime',
            'amount',
            'productname',
            'productdesc',
            'identityid',
            'identitytype',
            'card_top',
            'card_last',
            'orderexpdate',
            'callbackurl',
            'userip'
        ];

        $url = "yeepaytzt/directbindpay";
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['orderid'] . "--" . print_r($result, true), 'directbindpay');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 验证接口参数是否完整
     * @param [param_map,params]
     * @return [true,false]
     * */
    protected function validParamMap($param_map, $params) {
        $flag = true;
        if (empty($param_map) || empty($params))
            return false;
        foreach ($param_map as $k => $val) {
            if (!isset($params[$val]) || empty($params[$val])) {
                $flag = false;
                break;
            }
        }

        return $flag;
    }

    protected function paramSort($params) {
        $paramkey = array_keys($params);
        sort($paramkey);
        $signstr = '';
        foreach ($paramkey as $key => $val) {
            $signstr .= $params[$val];
        }
        return $signstr;
    }

    /**
     * @abstract 百荣银行卡账单
     * @param [param_map,params]
     * @return [json]
     * */
    public function bankOrder($params) {
        $url = 'bairong/index';
        $arr['data'] = json_encode($params);
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $arr);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params[0]['card'] . "--" . print_r($result, true), 'cardorderBybairong');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_msg' => $result['res_data']['data'], 'res_total' => $result['res_data']['total']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_msg' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 运营商数据获取
     * @param [param_map,params]
     * @return [json]
     * */
    public function grabroute($params) {
        $url = 'grabroute/serverpost';
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['phone'] . "--" . print_r($result, true), 'grabroute', 'Mobile');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => 0, 'res_data' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_data' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * 运营商数据获取(服务密码在开放平台)
     * @param $params
     * @return array
     */
    public function postGrabRouteNew($params) {
        $url = 'operator/req';
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['phone'] . "--" . print_r($result, true), 'postGrabRouteNew', 'Mobile');
        if ($result['res_code'] === 0) {
            if (isset($result['res_data']['url']) && !empty($result['res_data']['url'])) {
                $result['res_data']['url'] = str_replace('xianhuahua', 'yaoyuefu', $result['res_data']['url']);
            }
            $ret = ['res_code' => 0, 'res_data' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_data' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * @abstract 借点钱api运营商数据提交到开放平台保存
     * @param [param_map,params]
     * @return [json]
     * */
    public function postJdqdetail($params) {
        $url = 'basicoperator/detail';
        $arr['data'] = $params;
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $arr);
        $result = json_decode($res, true);
        Logger::errorLog(print_r($result, true), 'jdqdetail');
        return $result;
    }

    /**
     * @abstract 榕树api运营商数据提交到开放平台保存
     * @param [param_map,params]
     * @return [json]
     * */
    public function postJuxinli($params) {
        $url = 'yiyiyuanjdrequest';
        $arr['data'] = json_encode($params);
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $arr);
        $result = json_decode($res, true);

        Logger::errorLog(print_r($result, true), 'postJuxinli');
        return $result;
    }

    /**
     * @abstract 榕树api运营商数据提交到开放平台保存
     * @param [param_map,params]
     * @return [json]
     * */
    public function postRongJuxinli($params) {
        $url = 'rongoperator/rongdetail';
        $arr['data'] = json_encode($params);
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $arr);
        $result = json_decode($res, true);

        Logger::errorLog(print_r($result, true), 'postJuxinli');
        return $result;
    }

    /**
     * @abstract 运营商入网时间提交到开放平台
     * @param [param_map,params]
     * @return [json]
     * */
    public function postPls($params) {
        $url = 'basicoperator/report';
        $arr['data'] = json_encode($params);
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $arr);
        $result = json_decode($res, true);

        Logger::errorLog(print_r($result, true), 'postJuxinli');
        return $result;
    }

    /**
     * @abstract 融360api运营商数据提交到开放平台保存
     * @param [param_map,params]
     * @return [json]
     * */
    public function postRong($params) {
        $url = 'rongoperator/rongreport';
        $arr['data'] = json_encode($params);
        $openApi = new ApiClientCrypt;
        //print_r($arr);die;
        $res = $openApi->sent($url, $arr);
        $result = json_decode($res, true);

        Logger::errorLog(print_r($result, true), 'postRong');
        return $result;
    }

    /**
     * @abstract 榕树api身份证照片url提交到开放平台
     * @param [param_map,params]
     * @return [json]
     * */
    public function postImg($params) {
//        $url = 'http://up.xianhuahua.com/transfer/index';//此处是测试地址
        $url = 'http://upload.yaoyuefu.com/transfer/index';

        $key = '013456GJLNVXZbdhijkmnprz';
        $paramsJson = json_encode($params);
//        print_r($paramsJson);die;
        $encrypt['encrypt'] = Crypt3Des::encrypt($paramsJson, $key);
        $res = $this->httpPost($url, $encrypt);

        Logger::errorLog(print_r($res, true), 'postImg');
        return $res;
    }

    /**
     * @abstract 获取百度金融数据
     * @return [array]
     * */
    public function BaiduRiskApi($data) {
        $param_map = [
            'name',
            'idcard',
            'phone',
        ];
        $url = Yii::$app->params['bdrisk'];
        $curl = new Curl();
        $res = $curl->post($url, $data);
        $res = json_decode($res, true);
        return $res;
    }

    /**
     * 从开放平台获取card_bin信息
     * @param $params
     * @return mixed
     */
    public function gitCardBin($params) {
        $url = 'bankauthroute/cardbin';
        $openApi = new ApiClientCrypt;
        $res = $openApi->sentRong($url, $params);
        $result = json_decode($res, true);
        return $result;
    }

    public function daiKou($params) {
//        $params = array(
//            'orderid'        => 'B20171127095756213', // 请求唯一号
//            'identityid'     => $userInfo->user_id, // 用户标识
//            'bankname'       => $userInfo->bank->bank_name, //银行名称
//            'bankcode'       => $userInfo->bank->bank_abbr, //银行编码
//            'card_type'      => ($userInfo->bank->type == 0) ? 1 : 2, // 卡类型
//            'cardno'         => $userInfo->bank->card, // 银行卡号
//            'idcard'         => $userInfo->identity, // 身份证号
//            'username'       => $userInfo->realname, // 姓名
//            'phone'          => $userInfo->mobile, // 预留手机号
//            'productcatalog' => '7', // 商品类别码
//            'productname'    => '购买电子产品', // 商品名称
//            'productdesc'    => '购买电子产品', // 商品描述
//            'amount'         => 1, // 交易金额
//            'orderexpdate'   => 60,
//            'business_code'  => 'YYYTJYXDK',
//            'userip'         => $_SERVER["REMOTE_ADDR"],
//            'callbackurl'    => Yii::$app->request->hostInfo."/new/notify",
//        );
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent('payroute/pay', $params, 2);
        Logger::dayLog("api/daikou_result", $res, $params['identityid']);
        $result = $openApi->parseResponse($res);
        Logger::dayLog("api/daikou_result", $result, $params['identityid']);
        return $result;
    }

    /**
     * 众安投保
     * @param $params
     * @return array
     */
    public function postPolicy($params) {
        $url = 'policy/receive';
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params, $type = 2);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['req_id'] . "--" . print_r([$result, $res], true), 'postPolicy', 'Mobile');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => '0000', 'res_data' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_data' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * 众安投保
     * @param $params
     * @return array
     */
    public function policypay($params) {
        $url = 'policy/pay';
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params, $type = 2);
        $result = $openApi->parseResponse($res);
        Logger::errorLog($params['req_id'] . "--" . print_r([$result, $res], true), 'policypay', 'Mobile');
        if ($result['res_code'] === 0) {
            $ret = ['res_code' => 0, 'res_data' => $result['res_data']];
        } else {
            $ret = ['res_code' => $result['res_code'], 'res_data' => $result['res_data']];
        }
        return $ret;
    }

    /**
     * 有信令推送
     * @param $params
     * @return array
     */
    public function postSignal($params, $type) {
        $url = 'dev/notify/index?type=' . $type;
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        Logger::dayLog('signal/push', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 评测状态推送
     * @param $params
     * @return array
     */
    public function postCreditStatus($params) {
        $url = 'dev/notifyapply';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        Logger::dayLog('creditstatus/push', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取有信令信用信息
     * @param $params['mobile' => $user->mobile]
     * @return array['rsp_code','order_amount','user_credit_status','credit_invalid_time'失效时间]
     * user_credit_status:1:未测评;2已测评不可借;3:评测中;4:已测评未购买;5:已测评已购买;6:已过期;7:存在未支付的白条;8:存在处理中的退卡
     */
    public function getUserCredit($params) {
        $url = 'dev/getusercreditinfo';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        $result = json_decode($result, true);
        if (!isset($result['rsp_code']) || $result['rsp_code'] !== '0000') {
            return [];
        }
        Logger::dayLog('api/getUserCredit', $params, $result);
//        $result = [
//            "rsp_code"=> "0000",
//            "rsp_msg"=>"成功",
//            "order_amount"=>1000,
//            "user_credit_status"=>5
//        ];
        return $result;
    }

    /**
     * 根据借款loan_id获取智融钥匙测评支付结果
     * @param type $params
     * @return type
     */
    public function getYxlpayresult($params) {
        $url = '/dev/getpayinfo/payinfo';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        Logger::dayLog('api/getYxlpayresult', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }
     /**
     * 根据测评req_id获取智融钥匙测评支付结果
     * @param type $params
     * @return type
     */
    public function getYxlpayBycredit($params) {
        $url = '/dev/getpayinfo/payinfobyreqid';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        Logger::dayLog('api/getYxlpayBycredit', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }
    
    /**
     * 获取智融钥匙该用户是否已注册
     * @param type $params
     * @return type
     */
    public function getYxlisregister($params) {
        $url = '/dev/getuserinfo';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        Logger::dayLog('api/getYxlisregister', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取智融钥匙app的ios app stroe和安卓apk的下载地址
     * @param type $params
     * @return type
     */
    public function getYxldownurl($params) {
        $url = '/dev/getdownurl';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        Logger::dayLog('api/getYxlpayresult', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取有信令白条信息
     * @param $params   ['mobile' => $user->mobile]
     * @return array['rsp_code','rsp_data]，如果没有白条，rsp_data为空
     * rsp_data 注释
     *  ious_id：白条id
     *  order_id：服务id
     *  status：白条状态9初始 11 线下还款确认中 12 逾期
     *  amount：白条金额
     *  chase_amount：应支付金额
     *  invalid_time：过期时间
     *  end_time：最后支付时间
     *  chase_fee：延期费用
     *  is_repay_status：白条还款方式 0 没有还款记录 1线下 2线上
     */
    public function getUseriousinfo($params) {
        $url = 'dev/getuseriousinfo';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        Logger::dayLog('api/getUseriousinfo', $params, $result);
        $result = json_decode($result, true);
        if (!isset($result['rsp_code']) || $result['rsp_code'] !== '0000' || empty($result['rsp_data'])) {
            return [];
        }
        return $result['rsp_data'];
    }

    /**
     * 有信令-推送借款信息
     * @param $params
     * @return mixed
     */
    public function postYxLoanInfo($params) {
        $url = 'dev/syncloaninfo';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 4);
        Logger::dayLog('api/postSyncLoan', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 向开放平台发送视频二进制流
     * @param $params
     * @return mixed
     */
    public function sendVideoUrl($params) {
        $url = 'soup/stateless';
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        $result = $openApi->parseResponse($res);
//        $result = json_decode($res, true);
        return $result;
    }

    /**
     * 开放平台H5Ocr认证
     * @param $params
     * @return mixed
     */
    public function postOpenOcr($params) {
        $url = 'soup/idcard';
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params, 6);
        Logger::dayLog('soup/idcard', $params, $res);
        $result = $openApi->parseResponse($res);
        return $result;
    }

    /**
     * 评测接口
     * @param $params
     * @return bool|mixed
     * @author 王新龙
     * @date 2018/7/9 18:31
     */
    public function postCredit($params) {
        if (empty($params) || !is_array($params)) {
            return false;
        }
        $apiSignModel = new \app\common\ApiSign();
        $sign = $apiSignModel->signData($params);
        $curl = new Curl();
        $url = Yii::$app->params['strategy'] . 'strategy-req/yyy-credit';
        $ret = $curl->post($url, $sign);
        Logger::dayLog('api/credit', $params, $ret);
        $result = json_decode($ret, true);
        if (!$result) {
            return '{"res_code":"404","rsp_msg":"service error"}';
        }
        $isVerify = $apiSignModel->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"res_code":"200","rsp_msg":"sign error"}';
        }
        return $result['data'];
    }

    /**
     * 运营商风控
     * @param $params
     * @return bool|string
     * @author 王新龙
     * @date 2018/7/31 18:44
     */
    public function postReport($params) {
        if (empty($params) || !is_array($params)) {
            return false;
        }
        $apiSignModel = new \app\common\ApiSign();
        $sign = $apiSignModel->signData($params);
        $curl = new Curl();
        $url = Yii::$app->params['strategy_noapi'] . 'sfapi/cloud/report';
        $ret = $curl->post($url, $sign);
        Logger::dayLog('api/report', $params, $ret);
        $result = json_decode($ret, true);
        if (!$result) {
            return '{"rsp_code":"404","rsp_msg":"service error"}';
        }
        $isVerify = $apiSignModel->verifyData($result['data'], $result['_sign']);
        if (!$isVerify) {
            return '{"rsp_code":"200","rsp_msg":"sign error"}';
        }
        return $result['data'];
    }
    
    /**
     * 根据用户手机号查询先花商城的订单状态 1：待支付的订单  1：进行中的订单
     * @param type $params
     * @return type
     */
    public function getShoporder($params) {
        $url = 'new/apitoyyy/orderpayresult';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 8);
        Logger::dayLog('api/getShoporder', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * 判断先花商城的订单状态 决定一亿元是否可评测
     * @param type $params
     * @return type
     */
    public function getCancreditByShoporder($params) {
        $url = 'new/apitoyyy/orderresult';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 8);
        Logger::dayLog('api/getCancreditByShoporder', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 亿元同步商城分类
     * @param type $params
     * @return type
     */
    public function getSendCategory($params) {
        $url = 'new/apitoyyy/synccategory';
        $openApi = new ApiClientCrypt;
        $result = $openApi->sent($url, $params, 8);
        Logger::dayLog('api/getSyncCategory', $params, $result);
        $result = json_decode($result, true);
        return $result;
    }


    /**
     * @abstract 请求身份证米富是否存在接口
     * @param[receive_mobile,content,sms_type]
     * @return[true,false]
     */
    public function sendMifuIdentity($identity) {
        $url = 'yyygetdata/isregbyidnumber';
        $params=[
            'id_no'=> Crypt3Des::encrypt($identity,(new ApiClientCrypt())->getKey()),
        ];
        $signData = (new \app\commonapi\ApiSign)->signData($params);
        $signData['_sign'] = base64_encode($signData['_sign']);
        $url = Yii::$app->params['mifuDomain'] . $url;
        $result = Http::interface_post($url, $signData);
        Logger::dayLog('api/gemifuIdentity', $identity, $result);
        $result = json_decode($result, true);
        return $result;
    }
	/**
	* @abstract 提交短信列表到开放平台
     * @param [param_map,params]
     * @return [json]
     * */
    public function postSmsList($params) {
        $url = 'msgsave/index';
        $arr['data'] = json_encode($params);
        $openApi = new ApiClientCrypt;
        $res = $openApi->sent($url, $params);
        Logger::errorLog(print_r($params, true), 'postSmsList');
        $result = json_decode($res, true);
        Logger::errorLog(print_r($result, true), 'postSmsList');
        return $result;
    }
}
