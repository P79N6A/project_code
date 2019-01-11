<?php

/**
 * 拉取运营商归属地及运营商数据
 * D:\software\amp\php\php.exe D:\workspace\open\yii mobileinfo index
 */

// #定时
// */5 * * * * /usr/local/php-5.4.40/bin/php  /data/wwwroot/open/yii mobileinfo index 1>/dev/null 2>&1

namespace app\commands;
use app\common\Logger;
use app\models\JxlRequestModel;
use app\models\MobileOperator;

/**
 * 拉取运营商归属地及运营商数据
 */
class MobileinfoController extends BaseController {

    private $jxlModel;
    private $errinfo;// 出错信息

    /**
     * 拉取手机号归属地以及运营商
     * 每五分钟执行一次
     */
    public function actionIndex(){
        $this->jxlModel = new JxlRequestModel();
        $queryTime = time() - 300;
        $mobiles = JxlRequestModel::find()->select(['phone'])->distinct()->where(['>=', 'create_time', $queryTime])->all();

        foreach ($mobiles as $key => $mobile){
            $dataRes = $this->saveMobileInfo($mobile['phone']);
            if(!$dataRes){
                Logger::dayLog(
                    'getMobileInfo',
                    '手机号', $mobile['phone'],
                    '失败原因', '拉取手机号运营商及归属地失败'
                );
            }
        }
    }

    /**
     * 拉取运营商信息接口
     * @param $mobile
     * @return bool
     */
    private function saveMobileInfo($mobile){
        //参数校验
        if(!$mobile){
            return $this->returnError(false, '手机号不能为空');
        }

        //使用历史数据
        $mobile_info = new MobileOperator();
        $history_info =$mobile_info->getInfoByMobile($mobile);

        if(!$history_info){
            $data = $this->getMobileInfo($mobile);
            //存到运营商信息表中
            $location = isset($data['province'])?$data['province']:''; //手机号归属地
            $operator = isset($data['catName'])?$data['catName']:''; //号码运营商
            if((!$location)||(!$operator)){
                return $this->returnError(false, '手机号有误');
            }
            $result = [
                'mobile'=>$mobile,
                'location'=>$location,
                'operator'=>$operator,
            ];
            $data_res = $mobile_info->saveMobileInfo($result);
            if(!$data_res){
                Logger::dayLog(
                    'getMobileInfo',
                    'saveDetailrequest:入库失败',
                    '提交数据', $result,
                    '失败原因', $mobile_info->errors
                );
                return $this->returnError(false, '数据保存失败');
            }
        }
        return true;
    }

    /**
     *获取运营商信息
     * @param $mobile
     * @return array
     */
    private function getMobileInfo($mobile) {
        $url = "http://tcc.taobao.com/cc/json/mobile_tel_segment.htm";
        $curlPost = 'tel=' . $mobile;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);
        curl_close($ch);
        $data = iconv('GB2312', "UTF-8", $data);
        preg_match_all("/(\w+):'([^']+)/", $data, $m);
        $arr = array_combine($m[1], $m[2]);
        return $arr;
    }

    /**
     * 返回错误信息
     * @param  false | null $result 错误信息
     * @param  str $errinfo 错误信息
     * @return false | null 同参数$result
     */
    public function returnError($result, $errinfo){
        $this->errinfo = $errinfo;
        return $result;
    }
}
