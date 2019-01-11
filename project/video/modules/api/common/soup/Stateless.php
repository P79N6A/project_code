<?php
/**
 * 该API用于静默活体检测
 * 文档地址：https://v2-devcenter.visioncloudapi.com/#!/home/doc/liveness_silent_detection_stateless
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/17
 * Time: 9:58
 */
namespace app\modules\api\common\soup;

use app\common\Crypt3Des;
use app\common\Curl;
use app\common\Logger;
use app\models\SoupStateless;
use yii\helpers\ArrayHelper;
use yii;

class Stateless
{
    const VIDEO_FILE = "https://v2-auth-api.visioncloudapi.com/liveness/silent_detection/stateless";
    public function moveFile($params)
    {
        //1.获取视频地址
        $video_file = ArrayHelper::getValue($params, 'video_file');
        $yirequestid = ArrayHelper::getValue($params, 'requestid');
        if (empty($video_file)){
            return $this->returnMessage('3001');
        }
        $aid = ArrayHelper::getValue($params, "aid");

        //2.生成签名
        $oAutograph = new Autograph();
        $auth = $oAutograph->make($aid);
        //3.请求第三方
        //$filePath = 'C:/Users/face.mp4';  //视频路径
        $fileContent = new \CURLFile($video_file);
        //$fileContent = '@' . realpath($video_file);
        $post_data = array ('video_file' => $fileContent, 'return_image' => true);
        $result = $this->sendHttp($auth, $post_data);
        //$result = array ( 'code' => 1000, 'passed' => false, 'liveness_score' => 0.5, 'request_id' => '216069908eb147ed9e5e2b79bb767117', );
        $code = ArrayHelper::getValue($result, "code", 0);
        if ($code == 1000) {
            //保存图片
            $base64_image = ArrayHelper::getValue($result, 'base64_image');
            $base64_image = $this->savePic($base64_image);
            $result['base64_image'] = $base64_image;
            $result['yirequestid'] = $yirequestid;
            //保存数据
            $oSoupStateless = new SoupStateless();
            $save_bool = $this->saveData($oSoupStateless, $video_file, $result);
            if ($save_bool){
                //var_dump($result);
                return $this->successMessage($result);
            }
            $this->returnMessage("3002");
        }
        if (empty($result)){
            return $this->returnMessage('3003');
        }
        return $this->returnMessage($code);
    }

    private function successMessage($result)
    {
        return [
            'code'  => 0000,
            'msg'   => [
                'passed'                => ArrayHelper::getValue($result, 'passed'),
                'liveness_score'        => ArrayHelper::getValue($result, 'liveness_score'),
                'request_id'            => ArrayHelper::getValue($result, 'request_id'),
                'image_timestamp'       => ArrayHelper::getValue($result, 'image_timestamp'),
                'base64_image'          => ArrayHelper::getValue($result, 'base64_image'),
                'yirequestid'           => ArrayHelper::getValue($result, 'yirequestid'),
            ],
        ];
    }

    private function sendHttp($auth, $post_data)
    {
        $ch = curl_init();
        //请将AUTHORIZATION替换为根据API_KEY和API_SECRET得到的签名认证串
        $Authorization = $auth;
        //echo "Authorization:";
        $headr = array();
        $header[] = 'Authorization: '.$Authorization;
        Logger::dayLog("soup/stateless", "请求参数：", json_encode($post_data));
        Logger::dayLog("soup/stateless", "签名认证：", json_encode($header));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_URL, self::VIDEO_FILE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //打开SSL验证时，需要安装openssl库。也可以选择关闭，关闭会有风险。
        curl_setopt($ch, CURLOPT_POST,1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        Logger::dayLog("soup/stateless", "返回值：", $output);
        $output_array = json_decode($output,true);
        curl_close($ch);
        return $output_array;
    }

    private function saveData(SoupStateless $oSoupStateless, $video_file, $params)
    {
        if (empty($video_file) || empty($params)){
            return false;
        }
        $save_data = [
            'video_file'        => $video_file,
            'request_id'        => ArrayHelper::getValue($params, 'request_id'),
            'code'              => (string)ArrayHelper::getValue($params, 'code'),
            'passed'            => ArrayHelper::getValue($params, 'passed'),
            'liveness_score'    => ArrayHelper::getValue($params, 'liveness_score', 0.00),
            'image_timestamp'   => ArrayHelper::getValue($params, 'image_timestamp', 0.00),
            'base64_image'      => ArrayHelper::getValue($params, 'base64_image'),
            'yirequestid'       => ArrayHelper::getValue($params, 'yirequestid'),
            'message'           => ArrayHelper::getValue($params, 'message'),
        ];
        return $oSoupStateless -> saveData($save_data);
        
    }

    private function errorCode()
    {
        return [
            //系统响应码 code 说明
            '1100'			=>	'账号密码不匹配',
            '1200'			=>	'输入参数无效',
            '1101'			=>	'账号过期',
            '1103'			=>	'该接口没有权限',
            '1002'			=>	'使用频率超过限制',
            '2008'			=>	'无效的视频文件',
            '4007'			=>	'活体检测失败',
            //可能的 http 状态码
            '400'			=> 'BAD_REQUEST',
            '404'			=> 'NOT_FOUND',
            '411'			=> 'LENGTH_REQUIRED',
            '413'			=> 'PAYLOAD_TOO_LARGE',
            '500'			=> 'INTERNAL_ERROR',
            //自定义
            '3001'          => '待检测视频文件不能为空！',
            '3002'          => '视频记录失败',
            '3003'          => '无效的视频文件解析',

        ];
    }

    /**
     * 返回错误信息
     * @param $code
     * @return array
     */
    private function returnMessage($code)
    {
        $errorCode = $this->errorCode();
        return ['code'=>$code, 'msg'=>ArrayHelper::getValue($errorCode, $code, '未定义错误')];
    }

    /**
     * 保存图片
     * @param $base64_image
     * @return mixed
     */
    private function savePic($base64_image)
    {
        if (empty($base64_image)){
            return "";
        }
        $image_url = SYSTEM_PROD ? "http://upload.xianhuahua.com" : "http://upload.yaoyuefu.com";

        $jsonstr = [
                'uid'           => "",
                't'             => time(),
                'type'          => "soup",
                'project'       => "vodie",
        ];
        $encrypt = Crypt3Des::encrypt(json_encode($jsonstr), '013456GJLNVXZbdhijkmnprz');
        //$img_path = DIRECTORY_SEPARATOR."idcard".date("Y").DIRECTORY_SEPARATOR.date("m").DIRECTORY_SEPARATOR.date("d").DIRECTORY_SEPARATOR.time().".jpg";
        $img_path = "/vodie/soup/".date("Y").'/'.date("m").'/'.date("d").'/'.time().".jpg";
        $params = [
            'encrypt'       => $encrypt,
            'img[base64]'   => $base64_image,
            'img[url]'      => $img_path,
            'req_type'      => 'JSON',
        ];
        // 单独发送
        $oCurl = new Curl();
        $result = $oCurl->post($image_url."/upload", $params);
        $pic_data = json_decode($result, true);
        if (ArrayHelper::getValue($pic_data, 'res_code') == 0000){
            return ArrayHelper::getValue(ArrayHelper::getValue($pic_data, 'res_data'), 'img');
        }
        return '';
    }

    public function voidFile($fileName)
    {
        //$fileName='D:\1.mp4';
        // $fileName="a.png";

        $handle=fopen($fileName,"r");//使用打开模式为r
        //echo filesize($fileName);
        //echo '---------------<br><br>';
        $content=fread($handle,filesize($fileName));//读为二进制

        $video = base64_encode($content);

        $video_d = base64_decode($video);
        return $video_d;
        //file_put_contents('save.txt', $video_d); //写入文件并保存
    }
}