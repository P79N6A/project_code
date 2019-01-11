<?php

/**
 * 商汤接口H5OCR
 * 内部错误码范围9000-9999
 * @author daiweiqun
 */

namespace app\modules\api\controllers;

use app\common\Logger;
use app\models\Idcard;
use app\models\IdcardLog;
use app\models\Identity;
use app\modules\api\common\ApiController;
use app\modules\api\common\linkface\LinkfaceApi;

/**
 * 身份检验接口
 */
class IdentityController extends ApiController {

    /**
     * 服务id号
     */
    protected $server_id = 18;

    /**
     * 商汤接口文档
     */
    private $linkface;

    /**
     * ocr请求记录表
     */
    private $identity;

    /**
     * 初始化
     */
    public function init() {
        parent::init();
        $env = YII_ENV_DEV ? 'dev' : 'prod';
        //$env = 'prod';
        $this->linkface = new LinkfaceApi($env);
        $this->identity = new Identity();
    }

    public function actionIndex() {
        Logger::dayLog('identity_ocr', $this->reqData);
        //1 参数设置
        if (!isset($this->reqData['img_url']) || empty($this->reqData['img_url'])) {
            return $this->resp('18001', "图片地址不能为空");
        }

        $post_data = $this->reqData;
//        $post_data = $this->post();
        //上传图片给商汤，获取img_id
        $img_content = $this->linkface->ocr_update_pic($post_data['img_url']);
        $content = json_decode($img_content, TRUE);
        if (isset($content['code']) && $content['code'] != '1000') {
            return $this->resp('18002', $content['message']);
        }
        $side = isset($this->reqData['side']) ? $this->reqData['side'] : 'front';
        if (!in_array($side, ['idcard', 'front', 'back', 'auto'])) {
            return $this->resp('18004', '参数错误');
        }
        //通过img_id获取身份证信息
        $identity = $this->linkface->ocr_pic_content($content['id'], $side);
        $id_content = json_decode($identity, TRUE);
        if (isset($id_content['code']) && $id_content['code'] != '1000') {
            return $this->resp('18003', $id_content['message']);
        }
        $info = $id_content['info'];
        //3 保存到本地数据idcardlog中
        $identityData = [
            'aid' => $this->appData['id'],
            'name' => $info['name'],
            'idcard' => $info['number'],
            'data' => $info,
            'image' => $post_data['img_url'],
            'type' => 1,
        ];
//        print_r($identityData);
        $result = $this->identity->addRecord($identityData);
        if (!$result) {
            Logger::dayLog(
                    'identity', 'log保存失败', $this->identity->errors, $this->identity->errinfo, 'data', $identityData
            );
        }

        return $this->resp(0, [
                    'name' => $info['name'], // 处理中
                    'nation' => $info['nation'],
                    'address' => $info['address'],
                    'identity' => $info['number'],
        ]);
    }

}
