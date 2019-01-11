<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/22
 * Time: 10:33
 */
namespace app\modules\api\common\soup;

use app\common\Crypt3Des;
use app\common\Logger;
use app\models\App;
use app\models\SoupVideo;
use yii;
use yii\helpers\ArrayHelper;

class VideoFile
{

    public function file($params)
    {
        $file = $_FILES;
        //$file = json_decode('{"files":{"name":"0ab.mp4","type":"video\/mp4","tmp_name":"\/tmp\/phph2OUVO","error":0,"size":606594}}', true);
        Logger::dayLog("soup/video", "请求参数：", json_encode($params));
        Logger::dayLog("soup/video", "请求文件：", json_encode($file));
        $file_info = ArrayHelper::getValue($file, 'files');
        $aid = ArrayHelper::getValue($params, 'aid'); //应用编号
        $callbackurl = ArrayHelper::getValue($params, 'callbackurl'); //回调地址
        $requestid = ArrayHelper::getValue($params, 'requestid'); //一亿元id',
        //$requestid = "cg9cSQ3rChpyh7Bqq2fMTeNZ5SfXTjZX";
        if (!empty($requestid)) {
            $requestid = Crypt3Des::decrypt($requestid, "579BEFGINPQUVZehilprstxy");
        }
        //1.
        if (empty($aid) || empty($callbackurl) || empty($requestid)){
            return $this->returnMessage("3005");
        }

        //判断大小
        $size_bool = $this->checkSize(ArrayHelper::getValue($file_info, 'size'));
        if (ArrayHelper::getValue($size_bool, 'code')){
            return $size_bool;
        }
        //判断视频格式
        $type_bool = $this->checkExt(ArrayHelper::getValue($file_info, 'type'));
        if (ArrayHelper::getValue($type_bool, 'code')){
            return $type_bool;
        }
        $path = $this->mkdir();
        //保存视频
        $tmp_name = ArrayHelper::getValue($file_info, 'tmp_name');
        $name = ArrayHelper::getValue($file_info, 'name');

        $file_name = $this->saveVideo($tmp_name, $path, $name);
        if (ArrayHelper::getValue($file_info, "code")){
            return $file_name;
        }
        $params['video_file'] = $file_name;
        $params['requestid'] = $requestid;
        return $this->saveData($params);

    }


    private function saveData($params)
    {
        if (empty($params)){
            return false;
        }
        $save = [
                'aid'               => ArrayHelper::getValue($params, 'aid'), //应用编号
                'video_file'        => ArrayHelper::getValue($params, 'video_file'), //图片地址
                'callbackurl'       => ArrayHelper::getValue($params, 'callbackurl'), //回调地址
                'notify_status'     => '0', //通知状态:0:初始; 1:通知中; 2:通知成功; 3:重试; 11:通知失败; 13:通知超限'
                'requestid'         => ArrayHelper::getValue($params, 'requestid'), //一亿元id',
        ];
        $oSoupVideo = new SoupVideo();
        $video_bool = $oSoupVideo->saveData($save);
        if (!$video_bool){
            return $this->returnMessage("3004");
        }
        return true;
    }

    private function checkExt($ext)
    {
        if (!in_array(strtolower($ext), ['video/mp4', 'video/avi', 'video/flv', 'video/wmv', 'video/mov', 'video/rm', 'video/quicktime'])){
            return $this->returnMessage("3001");
        }
        return true;
    }

    private function checkSize($size=0)
    {
        if ($size >= 30000000)
        {
            return $this->returnMessage("3006");
        }
        return true;
    }

    private function saveVideo($des_video, $path, $name)
    {
        if (empty($des_video)){
            return $this->returnMessage("3002");
        }
        $path_name = $path."/".time().$name;
        $save = move_uploaded_file($des_video, $path_name);
        if ($save){
            return $path_name;
        }
        return $this->returnMessage("3003");
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
     * 错误码
     * @return array
     */
    private function errorCode()
    {
        return [
            //系统响应码 code 说明
            '3001'          => "视频文件格式有误",
            '3002'          => "视频文件不存在",
            '3003'          => "视频文件保存失败",
            '3004'          => "记录视频失败",
            '3005'          => "存在为空参数请检查",
            '3006'          => "视频文件太大",
        ];
    }




    private function mkdir()
    {
        //保存地址
        $path_dir = Yii::$app->basePath.'/web/ofiles/openapi/sta/';
        //判断文件是否存在
        if (!file_exists($path_dir)){
            //如果文件不存在阶梯创建目录和文件
            $dir       = str_replace("\\","/",$path_dir);
            substr($dir,-1)=="/"?$dir=substr($dir,0,-1):"";
            $dir_arr   = explode("/",$dir);$str = '';
            foreach($dir_arr as $k=>$a){
                $str   = $str.$a."/";
                if(!$str)continue;
                if(!file_exists($str))mkdir($str,0755);
            }
        }
        return $path_dir;
    }
}