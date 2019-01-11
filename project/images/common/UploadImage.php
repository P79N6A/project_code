<?php
namespace app\common;
use app\common\Curl;
use app\common\Logger;
use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class UploadImage extends \yii\imagine\Image {
    //private $oFile; //上传的文件
    private $oImg; // 对就的图片对象
    public $errinfo;

    public $maxSize;
    public $extensions;

    public $rootPath;
    public function __construct() {
        $this->maxSize = 10242880; // 10M 最大限制
        $this->extensions = ['jpg', 'png', 'jpeg']; // 允许扩展名
        $this->rootPath = dirname(Yii::$app->basePath) . '/images';
    }
    /**
     * $_FILES方式上传文件处理
     */
    public function createImage($filename) {
        // 1 获取上传的文件
        $file = UploadedFile::getInstanceByName($filename);
        //2 检查文件是否合法
        $result = $this->chkFile($file);
        if (!$result) {
            return null;
        }

        //3 设置图片
        try {
            $img = static::getImagine()->open($file->tempName);
            //$this->oFile = $file;
            return $this->oImg = $img;
        } catch (\Exception $e) {
            Yii::warning($e->getMessage() . $file->name, __METHOD__);
            return $this->returnError(null, "无法创建文件");
        }
    }

    /**
     * base64方式上传文件处理
     */
    public function createByBase64($base64, $path) {
        //1 获取上传的文件
        if (!$base64) {
            return null;
        }
        if (!$path) {
            return null;
        }
        $path = $this->rootPath . $path;

        //2 判断图片是否正确 // 若是含data,则将其去除
        if (preg_match('/data:\s*image\/(\w+);base64,/iu', $base64, $tmp)) {
            // 这个主要用于浏览器base64图片上传产生的
            if (!in_array($tmp[1], $this->extensions, true)) {
                return $this->returnError(null, '图片格式不正确，只支持jpg,jpeg,png!');
            }
            $base64 = str_replace(' ', '+', $base64);
            $base64 = str_replace($tmp[0], '', $base64);
        }

        //3 保存图片
        $img = base64_decode($base64); // 转换成二进制的形式
        $this->makedir(dirname($path));
        if (!file_put_contents($path, $img)) {
            return $this->returnError(null, '图片保存失败!');
        }

        //4 创建图片
        try {
            $img = static::getImagine()->open($path);
            return $this->oImg = $img;
        } catch (\Exception $e) {
            Yii::warning($e->getMessage() . __METHOD__);
            return $this->returnError(null, "无法创建文件");
        }
    }
    /**
     * 保存二进制图片
     * 只要是用于微信直接下载的
     */
    public function createByHex($img, $path) {
        //1 获取上传的文件
        if (!$img) {
            return null;
        }
        if (!$path) {
            return null;
        }
        $path = $this->rootPath . $path;

        $this->makedir(dirname($path));
        if (!file_put_contents($path, $img)) {
            return $this->returnError(null, '图片保存失败!');
        }

        //4 创建图片
        try {
            $img = static::getImagine()->open($path);
            return $this->oImg = $img;
        } catch (\Exception $e) {
            Yii::warning($e->getMessage() . __METHOD__);
            return $this->returnError(null, "无法创建文件");
        }
    }

    public function reduceSaveImg($img, $path) {
        //1 获取上传的文件
        if (!$img) {
            return null;
        }
        if (!$path) {
            return null;
        }
        $path = $this->rootPath . $path;

        $this->makedir(dirname($path));
        if (!file_put_contents($path, $img)) {
            return $this->returnError(null, '图片保存失败!');
        }
        //4 创建图片
        try {
            $img = static::getImagine()->open($path);
            return $this->oImg = $img;
        } catch (\Exception $e) {
            Yii::warning($e->getMessage() . __METHOD__);
            return $this->returnError(null, "无法创建文件");
        }
    }

    /**
     * $_FILES检查file是否合法
     * @param $upFile
     * @return bool
     */
    private function chkFile($file) {
        if (empty($file)) {
            return $this->returnError(false, "文件不能为空");
        }

        switch ($file->error) {
        case UPLOAD_ERR_OK:
            if ($this->maxSize !== null && $file->size > $this->maxSize) {
                return $this->returnError(false, $file->name . '文件大小超过限制');
            } elseif (!empty($this->extensions) && !$this->validateExtension($file)) {
                return $this->returnError(false, '文件扩展名仅支持' . implode(', ', $this->extensions));
            } else {
                return true;
            }
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return $this->returnError(false, '文件大小超过限制');
        case UPLOAD_ERR_PARTIAL:
            Yii::warning('File was only partially uploaded: ' . $file->name, __METHOD__);
            break;
        case UPLOAD_ERR_NO_TMP_DIR:
            Yii::warning('Missing the temporary folder to store the uploaded file: ' . $file->name, __METHOD__);
            break;
        case UPLOAD_ERR_CANT_WRITE:
            Yii::warning('Failed to write the uploaded file to disk: ' . $file->name, __METHOD__);
            break;
        case UPLOAD_ERR_EXTENSION:
            Yii::warning('File upload was stopped by some PHP extension: ' . $file->name, __METHOD__);
            break;
        default:
            break;
        }

        return false;
    }

    /**
     * $_FILES检测文件的扩展名
     * @param UploadedFile $file
     * @return boolean
     */
    private function validateExtension($file) {
        $extension = mb_strtolower($file->extension, 'utf-8');
        $mimeType = FileHelper::getMimeType($file->tempName, null, false);
        if ($mimeType === null) {
            return false;
        }

        $extensionsByMimeType = FileHelper::getExtensionsByMimeType($mimeType);

        if (!in_array($extension, $extensionsByMimeType, true)) {
            return false;
        }

        if (!in_array($extension, $this->extensions, true)) {
            return false;
        }

        return true;
    }

    /**
     * 返回结果，同时纪录错误原因
     */
    private function returnError($result, $errinfo) {
        $this->errinfo = $errinfo;
        return $result;
    }

    /**
     * 剪切到指定的宽度然后保存
     * @param path $savePath
     * @param int $newWidth
     * bool
     */
    public function saveAsWidth($savePath, $newWidth = 102400) {
        //1 获取尺寸
        if (empty($savePath)) {
            return false;
        }
        $savePath = $this->rootPath . $savePath;

        //2 判断是否需要等比例压缩
        $size = $this->oImg->getSize();
        $oldWidth = $size->getWidth();
        if ($oldWidth > $newWidth) {
            $imageSize = $size->scale($newWidth / $oldWidth);
            $this->oImg->resize($imageSize);
        }

        //3 保存图片
        $this->makedir(dirname($savePath));
        return $this->oImg->save($savePath);
    }
    //建立文件夹
    private function makedir($param) {
        if (!file_exists($param)) {
            $this->makedir(dirname($param));
            mkdir($param);
        }
    }
    /**
     * 获取保存的路径名称
     * 默认处理的是jpg的格式
     * @param $path
     * @param ext 扩展名
     * @return '' | path 为空时表示失败
     */
    public function getPath($path, $imgPath, $ext = 'jpg') {
        //1 若为空,随机生成一个
        if (!$imgPath) {
            return $this->returnError('', "项目目录不正确");
        }
        if ($imgPath[0] != '/') {
            return $this->returnError('', "项目目录不合法");
        }
        if (empty($path)) {
            $path = $this->randPath($imgPath, $ext);
            return $path;
        }

        //2 若不为空检测是否合法
        $pos = strpos($path, '.');
        if ($pos === false) {
            return $this->returnError('', "没有扩展名");
        }

        //3 判断目录数
        $num = substr_count($path, "/");
        if ($num > 8) {
            return $this->returnError('', "目录数过多");
        }
        if ($num < 4) {
// @todo
            return $this->returnError('', "目录数过少");
        }

        //4 返回结果
        if (strpos($path, $imgPath) !== 0) {
            return $this->returnError('', "路径名称与目录无法匹配");
        }
        //$path = $path[0] == '/' ? $path : '/' . $path;
        return substr($path, 0, $pos) . '.' . $ext; // 转成jpg形式
    }
    /**
     * 生成随机链接
     * @param $ext 扩展名
     * @return '';
     */
    private function randPath($imgPath, $ext) {
        do {
            $path = $imgPath . '/' . date('Y/m/d/His') . rand(1000, 9999) . '.' . $ext;
        } while (file_exists($this->rootPath . $path));
        return $path;
    }

    /**
     *  下载并保存图片 返回图片地址
     * @param [] $val
     * @return void
     */
    public function downAndSaveImg($val) {
        // 下载图片
        //$content = Http::getCurl($val['imgUrl']);
        $curl = new Curl();
        $curl->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->setOption(CURLOPT_TIMEOUT, 30);
        $content = '';
        Logger::dayLog("transfer", 'imgUrl', $val['imgUrl']);
        $content = $curl->get($val['imgUrl']);
        $status = $curl->getStatus();
        Logger::dayLog("transfer", 'status', $status);
        if ($status != 200) {
            return $this->returnError(null, "获取图片失败");
        }
        // $isImg = $this->check_img_by_source($content);
        // if(!$isImg){
        //     return $this->returnError(null,"获取图片失败");
        // }

        $res = $this->reduceSaveImg($content, $val['imgPath']);
        if (!$res) {
            return $this->returnError(null, "无法创建文件");
        }
        //压缩
        $saveRes = $this->saveAsWidth($val['imgPath']);
        if (!$saveRes) {
            return $this->returnError(null, "图片保存失败");
        }
        Logger::dayLog("transfer", $val['imgUrl'], $val['imgPath']);
        return $val['imgPath'];
    }

    private function check_img_by_source($source) {
        switch (bin2hex(substr($source, 0, 2))) {
        case 'ffd8':
            return 'ffd9' === bin2hex(substr($source, -2));
        case '8950':
            return '6082' === bin2hex(substr($source, -2));
        case '4749':
            return '003b' === bin2hex(substr($source, -2));
        default:
            return false;
        }
    }
    /**
     * Undocumented function
     * 原比例保存图片
     * @param [type] $savePath
     * @return void
     */
    public function saveImage($savePath, $filename) {
        //1 获取尺寸
        if (empty($savePath)) {
            return false;
        }
        $path = $this->rootPath . $savePath;
        $this->makedir(dirname($path));
        Logger::dayLog("imageUp", 'path', $path);
        try {
            $oUpload = UploadedFile::getInstanceByName($filename);
            $success = $oUpload->saveAs($path);
        } catch (\Exception $e) {
            $success = false;
        }
        return $success;
    }
}
