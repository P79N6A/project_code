<?php
/**
 * 默认控制器
 * 登录与退出
 */
namespace app\modules\api\controllers;

use app\common\BaseController;
use app\common\Logger;
use app\common\UploadImage;
use app\models\ImgFileSave;
use Yii;
use app\modules\api\common\ApiController;

/**
 * 上传首页
 */
class UploadController extends BaseController {
    private $imgPath;
    public $enableCsrfValidation = false;
    /**
     * 图片上传
     */
    public function actionIndex() {
        // 1 只能是post
        if (!$this->isPost()) {
            return $this->callback(1, "不支持此操作");
        }
        // 获取提交的数据信息
        $imgfiles = $this->getImages($this->post());
        if (!$imgfiles) {
            return $this->callback(1, $this->errinfo ? $this->errinfo : '数据不能为空');
        }

        // 保存的图片
        $success = [];
        foreach ($imgfiles as $group => $img) {
            //1 图片实例 判断保存的图片链接是否正确
            $oImg = new UploadImage;
            $path = $oImg->getPath($img['url'], $img['imgPath'], $img['ext']);
            if (!$path) {
                return $this->callback(1, $oImg->errinfo);
            }

            //3 创建图片
            $ok = false;
            if ($img['base64']) {
                //base64 方式处理
                $ok = $oImg->createByBase64($img['base64'], $path);
                /*if(!$ok){
            return $this->callback(1, $oImg->errinfo);
            }*/

            } else {
                //$_FILES方式保存
                $filename = $img['filename'];
                $ok = $oImg->createImage($filename);
                /*if(!$ok){
            return $this->callback(1, $oImg->errinfo);
            }*/
            }

            //4 保存图片
            if ($ok) {
                $result = $oImg->saveAsWidth($path, $img['width']);
                if (!$result) {
                    return $this->callback(1, $oImg->errinfo);
                }

                $success[$group] = $path;
            }
        }

        if (empty($success)) {
            return $this->callback(1, "至少上传一张图片");
        }
        return $this->callback(0, $success);
    }
    /**
     * 返回iframe结果
     */
    private function callback($res_code, $res_data, $type = null) {
        //1 输出结果
        // 自动判断返回类型
        if (empty($type)) {
            $type = $this->getParam("req_type");
        }
        $type = strtoupper($type);

        $data = [
            'res_code' => intval($res_code),
            'res_data' => $res_data,
        ];
        $jsonData = json_encode($data);

        if ($data['res_code']) {
            \app\common\Logger::dayLog("log", $this->post('encrypt'), $this->post('req_type'), $data);
        }

        // 返回结果: 统一json格式或消息提示代码
        switch ($type) {
        case 'JSON':
            return $jsonData;
            break;

        case 'HTML':
            return $data;
            break;

        default:
            //2 放在window.name里面
            $this->layout = false;
            return $this->render('iframeback', [
                'jsonData' => $jsonData,
            ]);
            break;
        }
    }
    /**
     * 获取提交的图片列表
     * @param $postData
     * @return null [filename,url,width]
     */
    private function getImages($postData) {
        //1 提交数据检测
        if (!is_array($postData) || empty($postData)) {
            return $this->returnError(null, '提交数据不能为空');
        }

        //2 验证密钥
        $oModel = new ImgFileSave();
        $encrypt = null;
        if ($postData['encrypt']) {
            $encrypt = $postData['encrypt'];
            unset($postData['encrypt']);
        }
        $result = $oModel->decryptKey($encrypt);
        if (!$result) {
            return $this->returnError(null, $oModel->errinfo);
        }
        $imgPath = $oModel->getImgPath();
        if (!$imgPath) {
            return $this->returnError(null, '项目目录不能为空,请检查密钥');
        }

        //3 循环解析判断每一组元素
        $imgfiles = [];
        foreach ($postData as $group => $data) {
            //1 数据校验
            if (!is_array($data) || empty($data)) {
                continue;
            }
//            if( !isset($data['url']) ){
            //                return $this->returnError(null, '链接地址未设置');
            //            }

            $width = 1024;
            if (isset($data['width'])) {
                if (!is_numeric($data['width'])) {
                    return $this->returnError(null, '宽度不合法');
                }
                $width = intval($data['width']);
                if ($width < 10) {
                    return $this->returnError(null, '宽度太小');
                }
                if ($width > 5000) {
                    return $this->returnError(null, '宽度不能超过5000');
                }
            }

            // 判断扩展名是否存在
            $ext = isset($data['ext']) ? $data['ext'] : 'jpg';
            if(!in_array($ext, ['jpg','png','gif','jpeg'])){
                $ext = 'jpg';
            }

            //3 加入结果
            $imgfiles[$group] = [
                'filename' => $group . '[file]',
                'base64' => isset($data['base64']) ? $data['base64'] : '',
                'url' => isset($data['url']) ? $data['url'] : '',
                'ext' => $ext,
                'width' => $width,
                'imgPath' => $imgPath,
            ];
        }
        return $imgfiles;
    }
}
