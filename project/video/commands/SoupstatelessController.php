<?php
/**
 * 活体定时
 * 请求商汤接口
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/21
 * Time: 14:34
 */
namespace app\commands;


use app\common\Common;
use app\common\Crypt3Des;
use app\common\Curl;
use app\common\Logger;
use app\models\App;
use app\models\SoupStateless;
use app\models\SoupVideo;
use app\modules\api\common\soup\Stateless;
use yii\helpers\ArrayHelper;

class SoupstatelessController extends BaseController
{
    public function runData()
    {

        //1.读取数据
        $oSoupVideo = new SoupVideo();
        $video_data = $oSoupVideo->getVideo();

        $num = 0;
        $oStateless = new Stateless();
        if (!empty($video_data)){
            $id_string = Common::ArrayToString($video_data, 'id');
            SoupVideo::updateAll(['notify_status' => 1], ['notify_status' => 0, 'id' => explode(',', $id_string)]);
            Logger::dayLog("soup/commands", "锁定id", $id_string);
            foreach($video_data as $video){
                $num ++;
                //中间状态
                //$video -> clockStatus();

                $video_file = ArrayHelper::getValue($video, 'video_file');
                $params = [
                    'video_file'    => $video_file,
                    'requestid'     => ArrayHelper::getValue($video, 'requestid'),
                    'aid'           => ArrayHelper::getValue($video, 'aid'),
                ];
                $result = $oStateless->moveFile($params);
                //通知
                $bool = $this->notify(ArrayHelper::getValue($video, 'aid'), ArrayHelper::getValue($video, 'callbackurl'), $result);
                if ($bool){
                    //修改成功
                    $video -> successStatus();
                }
            }
        }
        echo $num;

    }

    public function repayNotify()
    {
        //1.读取数据
        $oSoupVideo = new SoupVideo();
        $video_data = $oSoupVideo->getRepayData();
        $num = 0;
        $oStateless = new SoupStateless();
        if (!empty($video_data)){
            foreach($video_data as $video){
                $num ++;
                //中间状态
                $video -> clockStatus();
                //获取通知数据
                $data = $oStateless -> getOne(ArrayHelper::getValue($video, 'requestid'));
                $result = $this->successMsg($data);
                $result = [
                    'code' => 0,
                    'msg'  => $result,
                ];
                //var_dump($result);exit;
                //通知
                $bool = $this->notify(ArrayHelper::getValue($video, 'aid'), ArrayHelper::getValue($video, 'callbackurl'), $result);
                if ($bool){
                    //修改成功
                    $video -> successStatus();
                }
            }
        }
        echo $num;
    }

    private function successMsg($result)
    {
        return [
            'passed'                => ArrayHelper::getValue($result, 'passed'),
            'liveness_score'        => ArrayHelper::getValue($result, 'liveness_score'),
            'request_id'            => ArrayHelper::getValue($result, 'request_id'),
            'image_timestamp'       => ArrayHelper::getValue($result, 'image_timestamp'),
            'base64_image'          => ArrayHelper::getValue($result, 'base64_image'),
            'yirequestid'           => ArrayHelper::getValue($result, 'yirequestid'),
        ];
    }

    /**
     * 通知一亿元
     * @param $aid
     * @param $callbackurl
     * @param $result
     * @return bool
     */
    private function notify($aid, $callbackurl, $result)
    {
        $pic_code = ArrayHelper::getValue($result, 'code');
        $result = ArrayHelper::getValue($result, 'msg');
        Logger::dayLog("soup/commands", '通知数据：', json_encode($result));
        //1 加密
        //$res_data = App::model()->encryptData($aid, $result);
        $res_data = Crypt3Des::encrypt(json_encode($result), "579BEFGINPQUVZehilprstxy");
        $postData = ['res_data' => $res_data, 'res_code' => $pic_code];
        // 单独发送
        $oCurl = new Curl();
        $res = $oCurl->post($callbackurl, $postData);
        Logger::dayLog('soup/commands', '通知返回', substr($res,0,30));
        if (strtolower($res) == "success"){
            return true;
        }
        return false;
    }
}