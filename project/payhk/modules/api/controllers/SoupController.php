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
use app\common\Logger;
use app\modules\api\common\ApiController;
use app\modules\api\common\soup\Autograph;
use app\modules\api\common\soup\Distinguish;
use app\modules\api\common\soup\ImageFile;
use app\modules\api\common\soup\Stateless;
use app\modules\api\common\soup\VideoFile;
use yii\helpers\ArrayHelper;

class SoupController extends ApiController
{
    /**
     * 服务id号
     */
    protected $server_id = 1;
    /**
     * 初始化
     */
    public function init() {
        //$this->test();exit;
        /*
        //数据加密
        $params = [
            'pic_file_path'     => 'http://img.xianhuahua.com//yiyiyuan/transfer/2017/11/30/1508024494.jpg',
            'pic_type'          => 1
        ];
        $params = [
            'video_file'    => 'D:\1.mp4',
            //'video_file'    => '/upload/preview/pre_7d97667a3e056acab9aaf653807b4a031526616015.mp4',
        ];
        //
        $oApiCrypt = new ApiCrypt();
        $params['_sign'] = $oApiCrypt->sign($params, '48BEDAdLePQCmVAXeaYntvYXL');
        $a = Crypt3Des::encrypt(json_encode($params), '48BEDAdLePQCmVAXeaYntvYXL');
        var_dump($a);exit;
        */
        parent::init();
    }


    /**
     * 图片识别   soup/idcard
     * 需要参数
     *   pic_file_path  图片地址
     *   pic_type 类型1正面，2背面
     */
    public function actionIdcard()
    {
        //1.图片上传到云端
        $post_data = $this->reqData;
        Logger::dayLog("soup/image_file", "请求参数：", json_encode($post_data));
        $oImageFile = new ImageFile();
        $result = $oImageFile->fileUp($post_data);
        $code = ArrayHelper::getValue($result, 'code', 0);
        if ($code != 0000){
            return $this->resp($code, ArrayHelper::getValue($result, 'msg'));
        }
        $success_msg = ArrayHelper::getValue($result, 'msg');
        //2.图片识别
        $oDistinguish = new Distinguish();
        $image_data = [
            'image_id'          => ArrayHelper::getValue($success_msg, 'pic_id'),
        ];
        $pic_result = $oDistinguish -> inspectImage($image_data);
        $pic_code = ArrayHelper::getValue($pic_result, 'code');
        //var_dump(ArrayHelper::getValue($pic_result, 'msg'));
        return $this->resp($pic_code, ArrayHelper::getValue($pic_result, 'msg'));
    }

}