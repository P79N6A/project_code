<?php

namespace app\modules\sysloanguide\controllers;

use app\commonapi\Jxldown;
use app\models\news\User;
use app\models\news\User_password;
use app\models\own\Address_list;
use app\modules\sysloan\common\ApiController;
use Yii;
use yii\helpers\ArrayHelper;

class JxlController extends ApiController {

    public $layout = false;
    public $enableCsrfValidation = false;

    public function actionReport_bak() {

        $required = ['phone'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify = $this->BeforeVerify($required, $httpParams);
        $phone = $httpParams['phone'];
        if (!isset($phone) || empty($phone)) {
            $array['rsp_code'] = '1000';
            $array['rsp_msg'] = '缺少手机号码';
            exit(json_encode($array));
        }
        $user = User::find()->where(['mobile' => $phone])->one();
        $userId = $user ? $user->id : 0;

        //1. 获取电商数据
        $obj = new Jxldown($phone);
        $res = $obj->getRes();
        if (!isset($res['source'])) {
            $array['rsp_code'] = '1001';
            $array['rsp_msg'] = '无报告或获取失败';
            exit(json_encode($array));
        }
        $report = '';
        $fav_contact = '';
        $oAddressModel = new Address_list();
        switch ($res['source']) {
            //聚信立
            case 1:
            case 2:
                $fav_contact = $oAddressModel->getAddressList($userId);
                $report = $obj->report();
                if (!$this->chkReport($report)) {
                    $array['rsp_code'] = '1002';
                    $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
                    exit(json_encode($array));
                }
                $report['JSON_INFO']['deliver_address'] = $this->getJingdong($phone); //获取京东数据
                $version = $report['JSON_INFO']['report']['version'];
                if ($version == '4.1') {
                    $view = 'report41';
                } elseif ($version == '4.2') {
                    $view = 'report42';
                } else {
                    $array['rsp_code'] = '1003';
                    $array['rsp_msg'] = '不支持解析';
                    exit(json_encode($array));
                }
                break;
            //上树
            case 4:
                $report = $obj->all($userId);
                if (!$this->chkReport($report)) {
                    $array['rsp_code'] = '1004';
                    $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
                    exit(json_encode($array));
                }
                $report['JSON_INFO']['deliver_address'] = $this->getJingdong($phone); //获取京东数据
                $report['JSON_INFO']['phone'] = $phone;
                $view = 'report_shangshu';
                break;
            //引流平台
            case 5:
                $report = $obj->drainageAll($userId);
                if (!$this->chkReport($report)) {
                    $array['rsp_code'] = '1005';
                    $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
                    exit(json_encode($array));
                }
                $report['JSON_INFO']['deliver_address'] = $this->getJingdong($phone);
                $report['JSON_INFO']['naturalPerson']['name'] = isset($user->realname) ? $user->realname : '';
                $report['JSON_INFO']['naturalPerson']['idCard'] = isset($user->identity) ? $user->identity : '';
                $report['JSON_INFO']['phone'] = $phone;
                $report['JSON_INFO']['title'] = $this->getFrom($obj->res_data['f_detail']);

                $view = $report['JSON_INFO']['from'];
                break;
            //开发平台
            case 6:
                $report = $obj->drainageAll($userId);
                if (!$this->chkReport($report)) {
                    $array['rsp_code'] = '1006';
                    $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
                    exit(json_encode($array));
                }
                $report['JSON_INFO']['deliver_address'] = $this->getJingdong($phone);
                $report['JSON_INFO']['naturalPerson']['name'] = isset($user->realname) ? $user->realname : '';
                $report['JSON_INFO']['naturalPerson']['idCard'] = isset($user->identity) ? $user->identity : '';
                $report['JSON_INFO']['phone'] = $phone;
                $report['JSON_INFO']['title'] = $this->getFrom($obj->res_data['f_detail']);

                $view = $report['JSON_INFO']['from'];
                break;
        }
        if (!$this->chkReport($report)) {
            $array['rsp_code'] = '1007';
            $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
            exit(json_encode($array));
        }

        $array['rsp_code'] = '0000';
        $array['rsp_msg'] = '成功';
        $array['data'] = [
            'view' => $view,
            'res' => $res,
            'report' => $report,
            'user' => $user,
            'fav_contact' => $fav_contact,
        ];
        exit(json_encode($array));
    }
	
	
	public function actionReport() {
        $required = ['phone'];  //必传参数
        $httpParams = $this->post();  //获取参数
        $verify = $this->BeforeVerify($required, $httpParams);
        $phone = $httpParams['phone'];
        if (!isset($phone) || empty($phone)) {
            $array['rsp_code'] = '1000';
            $array['rsp_msg'] = '缺少手机号码';
            exit(json_encode($array));
        }
        $user = User::find()->where(['mobile' => $phone])->one();
        $userId = $user ? $user->id : 0;

        //1. 获取电商数据
        $obj = new Jxldown($phone);
        $res = $obj->getRes();
        if (!isset($res['source'])) {
            $array['rsp_code'] = '1001';
            $array['rsp_msg'] = '无报告或获取失败';
            exit(json_encode($array));
        }
        $report = '';
        $fav_contact = '';
        $oAddressModel = new Address_list();
        switch ($res['source']) {
            //聚信立
            case 1:
            case 2:
                $fav_contact = $oAddressModel->getAddressList($userId);
                $report = $obj->report();
                if (!$this->chkReport($report)) {
                    $array['rsp_code'] = '1002';
                    $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
                    exit(json_encode($array));
                }
                $report['JSON_INFO']['deliver_address'] = $this->getJingdong($phone); //获取京东数据
                $version = $report['JSON_INFO']['report']['version'];
                if ($version == '4.1') {
                    $view = 'report41';
                } elseif ($version == '4.2') {
                    $view = 'report42';
                } else {
                    $array['rsp_code'] = '1003';
                    $array['rsp_msg'] = '不支持解析';
                    exit(json_encode($array));
                }
                break;
            //上树
            case 4:
                $report = $obj->all($userId);
                if (!$this->chkReport($report)) {
                    $array['rsp_code'] = '1004';
                    $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
                    exit(json_encode($array));
                }
                $report['JSON_INFO']['deliver_address'] = $this->getJingdong($phone); //获取京东数据
                $report['JSON_INFO']['phone'] = $phone;
                $view = 'report_shangshu';
                break;
            //引流平台
            case 5:
                $report = $obj->drainageAll($userId);
                if (!$this->chkReport($report)) {
                    $array['rsp_code'] = '1005';
                    $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
                    exit(json_encode($array));
                }
                $report['JSON_INFO']['deliver_address'] = $this->getJingdong($phone);
                $report['JSON_INFO']['naturalPerson']['name'] = isset($user->realname) ? $user->realname : '';
                $report['JSON_INFO']['naturalPerson']['idCard'] = isset($user->identity) ? $user->identity : '';
                $report['JSON_INFO']['phone'] = $phone;
                $report['JSON_INFO']['title'] = $this->getFrom($obj->res_data['f_detail']);

                $view = $report['JSON_INFO']['from'];
                break;
            //开发平台
            case 6:
                $report = $obj->drainageAll($userId);
                if (!$this->chkReport($report)) {
                    $array['rsp_code'] = '1006';
                    $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
                    exit(json_encode($array));
                }
                $report['JSON_INFO']['deliver_address'] = $this->getJingdong($phone);
                $report['JSON_INFO']['naturalPerson']['name'] = isset($user->realname) ? $user->realname : '';
                $report['JSON_INFO']['naturalPerson']['idCard'] = isset($user->identity) ? $user->identity : '';
                $report['JSON_INFO']['phone'] = $phone;
                $report['JSON_INFO']['title'] = $this->getFrom($obj->res_data['f_detail']);

                $view = $report['JSON_INFO']['from'];
                break;
        }
        if (!$this->chkReport($report)) {
            $array['rsp_code'] = '1007';
            $array['rsp_msg'] = '报告内容为空, 请尝试聚信立后台查询!';
            exit(json_encode($array));
        }
		
		$detail = $obj ->detail();
        $array['rsp_code'] = '0000';
        $array['rsp_msg'] = '成功';
        $array['data'] = [
            'view' => $view,
            'res' => $res,
            'report' => $report,
            'detail' => $detail,
            'user' => $user,
            'fav_contact' => $fav_contact,
        ];
        exit(json_encode($array));
    }

    /**
     * 检查报告合法性
     */
    private function chkReport($report) {
        if (!$report || !is_array($report) || !isset($report['JSON_INFO']) || empty($report['JSON_INFO'])) {
            return null;
        }
        return $report;
    }

    /**
     * 获取京东数据
     */
    private function getJingdong($phone) {
        $obj = new Jxldown($phone, 'jingdong');
        $report = $obj->report();
        if (!$this->chkReport($report)) {
            return null;
        }
        $deliver = ArrayHelper::getValue($report, 'JSON_INFO.deliver_address');
        return $deliver;
    }

    /**
     * [getFrom description]
     * @param  [type] $string [description]
     * @return [type]         [description]
     */
    private function getFrom($string) {
        if (strpos($string, 'jdq') !== false) {
            return "借点钱";
        } elseif (strpos($string, 'br') !== false) {
            return "百融";
        } elseif (strpos($string, 'jqy') !== false) {
            return "借钱用";
        } elseif (strpos($string, 'sjt') !== false) {
            return "数据魔盒";
        } else {
            return "";
        }
    }

    public function actionRisk($phone) {
        if (!isset($phone) || empty($phone)) {
            return '缺少手机号码!';
        }
        $user = User::find()->where(['mobile' => $phone])->one();
        if (empty($user)) {
            return '未找到用户信息';
        }
        $xingzuo = $this->getXingZuo($user->identity);
        $address = User_password::find()->where(['user_id' => $user->user_id])->one();
        return $this->render("risk", [
                    'user' => $user,
                    'xingzuo' => $xingzuo,
                    'address' => !empty($address) ? $address->iden_address : '',
        ]);
    }

    function getXingZuo($cid) {
        $bir = substr($cid, 10, 4);
        $month = (int) substr($bir, 0, 2);
        $day = (int) substr($bir, 2);
        $strValue = '';
        if (($month == 1 && $day <= 21) || ($month == 2 && $day <= 19)) {
            $strValue = "水瓶座";
        } else if (($month == 2 && $day > 20) || ($month == 3 && $day <= 20)) {
            $strValue = "双鱼座";
        } else if (($month == 3 && $day > 20) || ($month == 4 && $day <= 20)) {
            $strValue = "白羊座";
        } else if (($month == 4 && $day > 20) || ($month == 5 && $day <= 21)) {
            $strValue = "金牛座";
        } else if (($month == 5 && $day > 21) || ($month == 6 && $day <= 21)) {
            $strValue = "双子座";
        } else if (($month == 6 && $day > 21) || ($month == 7 && $day <= 22)) {
            $strValue = "巨蟹座";
        } else if (($month == 7 && $day > 22) || ($month == 8 && $day <= 23)) {
            $strValue = "狮子座";
        } else if (($month == 8 && $day > 23) || ($month == 9 && $day <= 23)) {
            $strValue = "处女座";
        } else if (($month == 9 && $day > 23) || ($month == 10 && $day <= 23)) {
            $strValue = "天秤座";
        } else if (($month == 10 && $day > 23) || ($month == 11 && $day <= 22)) {
            $strValue = "天蝎座";
        } else if (($month == 11 && $day > 22) || ($month == 12 && $day <= 21)) {
            $strValue = "射手座";
        } else if (($month == 12 && $day > 21) || ($month == 1 && $day <= 20)) {
            $strValue = "魔羯座";
        }
        return $strValue;
    }

}
