<?php
/**
 * 商汤控制器文件
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/16
 * Time: 10:55
 */

namespace app\modules\api\controllers;

use app\common\ApiCrypt;
use app\common\Crypt3Des;
use app\common\Curl;
use app\common\Logger;
use app\models\App;
use app\modules\api\common\ApiController;
use app\modules\api\common\soup\Autograph;
use app\modules\api\common\soup\Distinguish;
use app\modules\api\common\soup\ImageFile;
use app\modules\api\common\soup\Stateless;
use app\modules\api\common\soup\VideoFile;
use yii\helpers\ArrayHelper;

class SoupfileController extends ApiController
{
    /**
     * 服务id号
     */
    protected $server_id = 1;
    /**
     * 初始化
     */
    public function init() {
        //parent::init();
    }

    /**
     * 上传视频
     */
    public function actionFilevideo()
    {
        //$post_data = $this->reqData;
        $post_data = $this->post();
        //$post_data = json_decode('{"orderinfo":"OuRtQNICGaYXz5SNDhRvh1JJDedeqaRp6KkzpNUoUMukMg6SQf3wMAgLrZYUzZE7ajFuQQ702pdQjAqdy+OOz1Rm3RcgSe9WGhfEMbrcnRNqMW5BDvTal9akhqi5lGbC3vRf73GL5R\/iJktNESBFjXjwj\/MFYRpe4UPt\/Tz6TjwXz5SNDhRvh0S8HRDjNzPwDgA5Ojo+mqZVT4hEtBJxwfU+Ye16Fr6FpXHSIrMYrOhmq4M8t93bdTNrbEOUapnxxjeZjPjxBDAe0rxfDitvEzIIOauoDqjoZKwMHlyo1KId4\/wn7P1pW0lNjBIsfrCEzVEVZtUmNHUDx1z\/qOPToQHNHUkfDii\/ZvwTlWHw5vgqWw\/z87KwdRyPUKgII5Tvks5UZQnuxWwIC62WFM2RO2oxbkEO9NqXc0ZM6tdJUFzDt4NNhe6VVOIHwLP2xTqeHI9QqAgjlO9GgZVQSzxe4QgLrZYUzZE7ajFuQQ702peXRtml3CsXKc3cr7F9+gI3U9aEhm\/padIxJxFb8P6xrmlzmrBxewQkyMy5ByIR7\/frRVZv77mtzAME3LHCOKt4oaf7kQd2IWEj7IwcR05X9tSrK2Ezl76GZO1gAyGbx4fGN5mM+PEEMB7SvF8OK28TDwdSYNd0v0w=","aid":"10","callbackurl":"http:\/\/yyytest.xianhuahua.com\/new\/notifyvideo","redirect":"http:\/\/yyytest.xianhuahua.com\/new\/userauth\/videowaiting","_csrf":"R3R0Z29EZ3kVAwAWBAgfDQ8wQhEZCyNODwEHKxlpAgFqOxZVWgtVKw==","requestid":"EbhT4fa5hC3+IGigD5SSneNZ5SfXTjZX"}' ,true);
        Logger::dayLog("soup/video", "请求参数post：", json_encode($post_data));
        $oVideoFile = new VideoFile();
        $file_info = $oVideoFile->file($post_data);
        $callbackurl = ArrayHelper::getValue($post_data, 'callbackurl');
        $redirect = ArrayHelper::getValue($post_data, 'redirect', 'http://weixin.xianhuahua.com/new/userauth/videowaiting');
        $aid = ArrayHelper::getValue($post_data, 'aid' , 0);
        $requestid = ArrayHelper::getValue($post_data, 'requestid');
        //$msg = ArrayHelper::getValue($file_info, 'msg', $callbackurl);
        if (!empty($requestid)) {
            $requestid = Crypt3Des::decrypt($requestid, "579BEFGINPQUVZehilprstxy");
        }
        $code = ArrayHelper::getValue($file_info, 'code', 0);
        $message = ArrayHelper::getValue($file_info, "msg", true);
        /*
        $return_result = [
            'code'        => ArrayHelper::getValue($file_info, 'code', 0),
            'msg'         => [
                'message'   => ArrayHelper::getValue($file_info, "msg", true),
                'requestid' => $requestid,
            ],
        ];
        */
        $header_url = $callbackurl. "?code=".$code."&message=".$message."&requestid=".$requestid;
        //$notif = $this->notify($aid, $callbackurl, $return_result);
        return $this->redirect($header_url);


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
        $pic_code = ArrayHelper::getValue($result, 'code', 0);
        $result = ArrayHelper::getValue($result, 'msg', "true");
        //1 加密
        //$res_data = App::model()->encryptData($aid, $result);
        //$res_data = Crypt3Des::encrypt($result, "579BEFGINPQUVZehilprstxy");
        $postData = ['res_data' => json_encode($result), 'res_code' => $pic_code];
        //var_dump($postData);
        //var_dump($postData);
        // 单独发送
        $oCurl = new Curl();
        Logger::dayLog('soup/soup_file_yiyiyuan', '通知数据', json_encode($postData));
        Logger::dayLog('soup/soup_file_yiyiyuan', '通知url', json_encode($callbackurl));
        $res = $oCurl->get($callbackurl, $postData);
        Logger::dayLog('soup/soup_file_yiyiyuan', '通知返回', json_encode($res));
        if ($res == "success"){
            return true;
        }
        return false;
    }
}